<?php

namespace TwitchAnalytics\Tests\Feature;

use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Managers\MYSQLDBManager;

class TokenEndPointIntegration extends TestCase
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
     **/
    public function noApiKeyProvidedErrorCode400()
    {
        $json = json_encode(['email' => 'motto@gmial.com']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            "error" => "The api_key is mandatory",
        ], $responseData);
    }

    /**
     * @test
     **/
    public function noEmailProvidedErrorCode400()
    {
        $json = json_encode(['api_key' => 'apikiprovided']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            "error" => "The email is mandatory",
        ], $responseData);
    }

    /**
     * @test
     **/
    public function invalidEmailProvidedErrorCode400()
    {
        $json = json_encode(['email' => 'motto#gmial.com', 'api_key' => 'apikiprovided']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            "error" => "The email must be a valid email address",
        ], $responseData);
    }

    /**
     * @test
     **/
    public function unauthorizedApiKeyProvidedErrorCode401()
    {
        $mysqlManagerMock = mock(MYSQLDBManager::class);
        $mysqlManagerMock
            ->shouldReceive('getUserApiKey')
            ->andReturn([
                'api_key' => 'invalid_hash_value',
                'id' => 123
            ]);
        $this->app->instance(MYSQLDBManager::class, $mysqlManagerMock);
        $json = json_encode(['email' => 'motto@gmial.com', 'api_key' => 'apikiprovided']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(401, $response->status());
        $this->assertEquals([
            "error" => "Unauthorized. API access token is invalid.",
        ], $responseData);
    }

    /**
     * @test
     **/
    public function rightCredentialsProvidedCode200()
    {
        $hashedKey = hash("sha256", 'apikimokery');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];

        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager
            ->shouldReceive('getUserApiKey')
            ->andReturn([
                'api_key' => $hashedKey,
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

        $this->app->instance(MYSQLDBManager::class, $mysqlManager);
        $json = json_encode(['email' => 'motto@gmial.com', 'api_key' => 'apikimokery']);
        $response = $this->call('POST', '/token', [], [], [], $server, $json);

        $this->assertEquals(200, $response->status());
    }
}
