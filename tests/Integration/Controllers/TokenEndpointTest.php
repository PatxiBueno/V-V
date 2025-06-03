<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\Token;
use DateTime;
use Mockery;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Validators\ApiKeyValidator;
use TwitchAnalytics\Validators\EmailValidator;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class TokenEndPointTest extends TestCase
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
        $tokenService = new Token($mysqlManager);
        $this->tokenController = new TokenController($this->emailValidator, $this->apiKeyValidator, $tokenService);

        $response = $this->tokenController->getToken($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "The api_key is mandatory",
            ], $responseData);
    }
}
