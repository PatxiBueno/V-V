<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Controllers\GetUserPlatformAge\GetUserPlatformAgeController;
use TwitchAnalytics\Application\Services\UserAccountService;
use TwitchAnalytics\Infrastructure\Repositories\ApiUserRepository;
use TwitchAnalytics\Infrastructure\ApiClient\FakeTwitchApiClient;
use TwitchAnalytics\Controllers\GetUserPlatformAge\UserNameValidator;
use TwitchAnalytics\Domain\Time\TimeProvider;
use Illuminate\Http\Request;

class GetUserPlatformAgeControllerTest extends TestCase
{
    private GetUserPlatformAgeController $controller;
    private TimeProvider $timeProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $apiClient = new FakeTwitchApiClient();
        $repository = new ApiUserRepository($apiClient);
        $this->timeProvider = Mockery::mock(TimeProvider::class);
        $this->timeProvider->shouldReceive('now')->andReturn(new DateTime('2025-01-01T00:00:00Z'));
        $service = new UserAccountService($repository, $this->timeProvider);
        $validator = new UserNameValidator();

        $this->controller = new GetUserPlatformAgeController($service, $validator);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function gets400WhenNameParameterIsMissing(): void
    {
        $request = new Request();

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'INVALID_REQUEST',
            'message' => 'Name parameter is required',
            'status' => 400
        ], $responseData);
    }

    /**
     * @test
     */
    public function gets400ForInvalidUsername(): void
    {
        $request = new Request();
        $request->query->set('name', 'ab');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'INVALID_REQUEST',
            'message' => 'Name must be at least 3 characters long',
            'status' => 400
        ], $responseData);
    }

    /**
     * @test
     */
    public function gets404ErrorForNonExistingtUser(): void
    {
        $request = new Request();
        $request->query->set('name', 'NonExistentUser');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'USER_NOT_FOUND',
            'message' => 'No user found with given name: NonExistentUser',
            'status' => 404
        ], $responseData);
    }

    /**
     * @test
     */
    public function getsUserAgeForExistingUser(): void
    {
        $request = new Request();
        $request->query->set('name', 'Ninja');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'name' => 'Ninja',
            'created_at' => '2011-11-20T00:00:00Z',
            'days_since_creation' => 4791
        ], $responseData);
    }
}
