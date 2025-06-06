<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\TokenService;
use DateTime;
use Mockery;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Validators\ApiKeyValidator;
use TwitchAnalytics\Validators\EmailValidator;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class TokenControllerTest extends TestCase
{
    private EmailValidator $emailValidator;
    private ApiKeyValidator $apiKeyValidator;
    private TokenController $tokenController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiKeyValidator = new ApiKeyValidator();
        $this->emailValidator = new EmailValidator();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     *
     */
    public function missingAPIkeyErrorCode400(): void
    {

        $request = new Request([], [], [], ['CONTENT_TYPE' => 'application/json'], []);
        $mysqlManager = mock(MYSQLDBManager::class);
        $tokenService = new TokenService($mysqlManager);
        $this->tokenController = new TokenController($this->emailValidator, $this->apiKeyValidator, $tokenService);

        $response = $this->tokenController->getToken($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "The api_key is mandatory",
            ], $responseData);
    }

    /**
     * @test
     *
     */
    public function missingMailGivenApiKeyErrorCode400(): void
    {
        $json = json_encode(['api_key' => 'apikimokery']);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $json);

        $mysqlManager = mock(MYSQLDBManager::class);
        $tokenService = new TokenService($mysqlManager);
        $this->tokenController = new TokenController($this->emailValidator, $this->apiKeyValidator, $tokenService);

        $response = $this->tokenController->getToken($request);
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
    public function invalidMailIdErrorCode400(): void
    {
        $json = json_encode(['email' => 'motto#gmial.com','api_key' => 'apikimokery']);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $json);

        $mysqlManager = mock(MYSQLDBManager::class);
        $tokenService = new TokenService($mysqlManager);
        $this->tokenController = new TokenController($this->emailValidator, $this->apiKeyValidator, $tokenService);

        $response = $this->tokenController->getToken($request);
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
    public function incorrectApiKeyErrorCode400(): void
    {
        $json = json_encode(['email' => 'motto@gmial.com','api_key' => 'apikimokery']);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $json);

        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager
        ->shouldReceive('getUserApiKey')
        ->andReturn([
            'api_key' => 'invalid_hash_value',
            'id' => 123
            ]);
        $tokenService = new TokenService($mysqlManager);
        $this->tokenController = new TokenController($this->emailValidator, $this->apiKeyValidator, $tokenService);

        $response = $this->tokenController->getToken($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(
            [
            'error' => "Unauthorized. API access token is invalid."],
            $responseData
        );
    }

     /**
     * @test
     *
     */
    public function rightCredentialGiveToken(): void
    {
        $postData = ['email' => 'motto@gmail.com', 'api_key' => 'apikimokery'];
        $json = json_encode($postData);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];

        $request = new Request($postData, [], [], [], [], $server, $json);

        $mokeryKey = hash('sha256', 'apikimokery');

        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager
            ->shouldReceive('getUserApiKey')
            ->andReturn([
            'api_key' => $mokeryKey,
            'id' => 123
            ]);

        $mysqlManager
            ->shouldReceive('getTokenByUserId')
            ->with(123)
            ->andReturn(['token' => 'existing_token']);

        $mysqlManager
            ->shouldReceive('updateToken')
            ->andReturn(true);

        $mysqlManager
            ->shouldReceive('insertToken')
            ->andReturn(true);

        $tokenService = new TokenService($mysqlManager);
        $this->tokenController = new TokenController($this->emailValidator, $this->apiKeyValidator, $tokenService);

        $response = $this->tokenController->getToken($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
