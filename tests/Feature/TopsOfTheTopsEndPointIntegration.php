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
    public function happyPahtForCache204(): void
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
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
    }
}
