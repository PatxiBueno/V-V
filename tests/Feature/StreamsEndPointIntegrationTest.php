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
}
