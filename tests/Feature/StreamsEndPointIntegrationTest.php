<?php

namespace TwitchAnalytics\Tests\Feature;

use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use TwitchAnalytics\ResponseTwitchData;

class StreamsEndPointIntegrationTest extends TestCase
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

        $response = $this->call('GET', '/analytics/streams', [], [], [], [
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
    public function validURLReturnsStreamsInfo200(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);
        $twitchAPIManagerMock = \Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForStreamsEndPoint')
            ->andReturn(new ResponseTwitchData(200, json_encode([
                'data' => [
                    [
                        'title' => 'Stream Title 1',
                        'user_name' => 'Streamer1',
                    ],
                    [
                        'title' => 'Stream Title 2',
                        'user_name' => 'Streamer2',
                    ],
                ]
            ])));

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));
        $this->app->instance(TwitchAPIManager::class, $twitchAPIManagerMock);

        $response = $this->call('GET', '/analytics/streams', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
