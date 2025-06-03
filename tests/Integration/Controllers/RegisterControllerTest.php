<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\RegisterService;
use TwitchAnalytics\Controllers\RegisterController;
use TwitchAnalytics\Validators\EmailValidator;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class RegisterControllerTest extends TestCase
{
    private EmailValidator $emailValidator;
    private RegisterController $registerController;
    private MYSQLDBManager $mysqlManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailValidator = new EmailValidator();
        $this->mysqlManager = mock(MYSQLDBManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     *
     */
    public function invalidMailIdErrorCode400(): void
    {
        $json = json_encode(['email' => 'motto#gmial.com']);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/register',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $json);

        $registerService = new RegisterService($this->mysqlManager);
        $this->registerController = new RegisterController($this->emailValidator, $registerService);

        $response = $this->registerController->registerUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
                'error' => "The email must be a valid email address"],
            $responseData
        );
    }
    /**
     * @test
     *
     */
    public function missingMailErrorCode400(): void
    {
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/register',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, []);

        $registerService = new RegisterService($this->mysqlManager);
        $this->registerController = new RegisterController($this->emailValidator, $registerService);

        $response = $this->registerController->registerUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
                'error' => "The email is mandatory"],
            $responseData
        );
    }
    /**
     * @test
     *
     */
    public function rightCredentialGiveApiKey(): void
    {
        $json = json_encode(['email' => 'motto@gmial.com']);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/register',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $json);

        $this->mysqlManager->shouldReceive('getUserByEmail')->once()->with('motto@gmial.com')->andReturn(null);
        $this->mysqlManager->shouldReceive('insertUserWithHashedApiKey')->once()->andReturn(true);
        $this->mysqlManager->shouldReceive('updateUserHashedKey')->once()->andReturn(true);

        $registerService = new RegisterService($this->mysqlManager);
        $this->registerController = new RegisterController($this->emailValidator, $registerService);

        $response = $this->registerController->registerUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('api_key', $responseData);
        $this->assertIsString($responseData['api_key']);
    }
}
