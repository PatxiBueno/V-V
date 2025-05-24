<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Feature;

use DateTime;
use Laravel\Lumen\Testing\TestCase;
use Mockery;
use TwitchAnalytics\Domain\Time\TimeProvider;

class GetUserPlatformAgeTest extends TestCase
{
    /**
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../../bootstrap/app.php';
    }

    /**
     * @test
     */
    public function gets400WhenNameParameterIsMissing(): void
    {
        $response = $this->get('/api/users/platform-age');

        $response->assertResponseStatus(400);
        $response->seeJson(
            [
                'error' => 'INVALID_REQUEST',
                'message' => 'Name parameter is required',
                'status' => 400
            ]
        );
    }

    /**
     * @test
     */
    public function gets400ForInvalidUsername(): void
    {
        $response = $this->get('/api/users/platform-age?name=ab');

        $response->assertResponseStatus(400);
        $response->seeJson(
            [
                'error' => 'INVALID_REQUEST',
                'message' => 'Name must be at least 3 characters long',
                'status' => 400
            ]
        );
    }

    /**
     * @test
     */
    public function gets404ErrorForNonExistingtUser(): void
    {
        $response = $this->get('/api/users/platform-age?name=NonExistentUser');

        $response->assertResponseStatus(404);
        $response->seeJson(
            [
                'error' => 'USER_NOT_FOUND',
                'message' => 'No user found with given name: NonExistentUser',
                'status' => 404
            ]
        );
    }

    /**
     * @test
     */
    public function getsUserAgeForExistingUser(): void
    {
        $timeProvider = Mockery::mock(TimeProvider::class);
        $timeProvider->allows('now')
            ->andReturns(new DateTime('2025-01-01T00:00:00Z'));
        $this->app->instance(TimeProvider::class, $timeProvider);

        $response = $this->get('/api/users/platform-age?name=Ninja');

        $response->assertResponseStatus(200);
        $response->seeJson(
            [
                'name' => 'Ninja',
                'created_at' => '2011-11-20T00:00:00Z',
                'days_since_creation' => 4791
            ]
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
