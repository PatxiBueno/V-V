<?php

namespace TwitchAnalytics\Tests\Feature;


use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Middleware\TokenVerifyer;

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

        $this->app->routeMiddleware([
            'token.verify' => new TokenVerifyer($mockDbManager),
        ]);

        $response = $this->call('GET', '/analytics/user', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeInvalidToken',
        ]);

        $this->assertEquals(401, $response->status());
    }

}
