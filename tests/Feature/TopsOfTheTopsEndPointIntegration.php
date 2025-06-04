<?php

namespace TwitchAnalytics\Tests\Feature;


use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use TwitchAnalytics\ResponseTwitchData;

class TopsOfTheTopsEndPointIntegration extends TestCase
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
    public function invalidParameterSinceErrorCode400(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeInvalidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));

        $response = $this->call('GET', '/analytics/topsofthetops?since=-1', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeInvalidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            'error' => "Bad request. Invalid or missing parameters.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function happyPahtForCache200(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeInvalidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);
        
        $mockDbManager
        ->shouldReceive('getCacheInsertTime')
        ->andReturn([
            'fecha_insercion' => '2055-06-03 14:23:45',
            ]);

        $mockDbManager
        ->shouldReceive('getTopsCacheData')
        ->andReturn([]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));

        $response = $this->call('GET', '/analytics/topsofthetops?since=1', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeInvalidToken',
        ]);

        $this->assertEquals(200, $response->status());
    }
    /**
     * @test
     *
     */
    public function happyPahtForApi200(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeInvalidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);
        
        $mockDbManager
        ->shouldReceive('getCacheInsertTime')
        ->andReturn([
            'fecha_insercion' => '2005-06-03 14:23:45',
        ]);

        $mockDbManager
        ->shouldReceive('cleanTopOfTheTopsCache')
        ->andReturn(true);

        $mockDbManager
        ->shouldReceive('curlToTwitchApiForTopThreeGames')
        ->andReturn(new ResponseTwitchData(200, json_encode([[
            "game_id" => "509658",
            "game_name" => "Just Chatting",
            "userName" => "KaiCenat",
            "totalVideos" => 36,
            "totalViews" => 100000,
            "mostTitle" => "Funny Moments",
            "mostViews" => 45000,
            "mostDuration" => "1h 10m",
            "mostDate" => "2025-06-03 14:23:45"
        ]])));
        $mockDbManager
        ->shouldReceive('cleanTopOfTheTopsCache')
        ->andReturn(true);

        $mockDbManager
        ->shouldReceive('insertTopsCache')
        ->andReturn(true);

        $twitchAPIManagerMock = mock(TwitchAPIManager::class);
        $twitchAPIManagerMock
        ->shouldReceive('curlToTwitchApiForTopThreeGames')
        ->andReturn(new ResponseTwitchData(200, json_encode([[
            "game_id" => "509658",
            "game_name" => "Just Chatting"
        ]])));

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));
        $this->app->instance(TwitchAPIManager::class, $twitchAPIManagerMock);

        $response = $this->call('GET', '/analytics/topsofthetops?since=600', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeInvalidToken',
        ]);

        $this->assertEquals(200, $response->status());
    }
}
