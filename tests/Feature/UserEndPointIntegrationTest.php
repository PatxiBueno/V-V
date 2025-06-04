<?php

namespace TwitchAnalytics\Tests\Feature;


use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use TwitchAnalytics\ResponseTwitchData;

class UserEndPointIntegrationTest extends TestCase
{

    /**
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../../bootstrap/app.php';
    }

    /**
     * @test
     *
     */
    public function invalidToken(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeInvalidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', 259201),
            ]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));

        $response = $this->call('GET', '/analytics/user', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeInvalidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(401, $response->status());
        $this->assertEquals([
            'error' => "Unauthorized. Token is invalid or expired.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function missingParameterIdErrorCode400(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));

        $response = $this->call('GET', '/analytics/user', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            'error' => "Invalid or missing 'id' parameter.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function invalidParameterIdErrorCode400(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));

        $response = $this->call('GET', '/analytics/user?id=-1', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            'error' => "Invalid or missing 'id' parameter.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function userNotFoundErrorCode404(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);
        $twitchAPIManagerMock = \Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(0)
            ->andReturn(new ResponseTwitchData(404, ""));

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));
        $this->app->instance(TwitchAPIManager::class, $twitchAPIManagerMock);

        $response = $this->call('GET', '/analytics/user?id=0', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(404, $response->status());
        $this->assertEquals([
            'error' => "User not found.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function validIdOneReturnsStreamerInfo200(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);
        $twitchAPIManagerMock = \Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(1)
            ->andReturn(new ResponseTwitchData(200, json_encode(["data" => [[
                "id" => "1",
                "login" => "elsmurfoz",
                "display_name" => "elsmurfoz",
                "type" => "",
                "broadcaster_type" => "",
                "description" => "",
                "profile_image_url" =>
                    "https://static-cdn.jtvnw.net/user-default-pictures-uv/215b7342-def9-11e9-9a66-784f43822e80-profile_image-300x300.png",
                "offline_image_url" => "",
                "view_count" => 0,
                "created_at" => "2007-05-22T10:37:47Z"
            ]]])));

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));
        $this->app->instance(TwitchAPIManager::class, $twitchAPIManagerMock);

        $response = $this->call('GET', '/analytics/user?id=1', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
