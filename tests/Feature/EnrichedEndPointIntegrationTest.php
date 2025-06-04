<?php

namespace TwitchAnalytics\Tests\Feature;

use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Controllers\EnrichedController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use TwitchAnalytics\ResponseTwitchData;

class EnrichedEndPointIntegrationTest extends TestCase
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
    public function invalidLimitParameterErrorCode400(): void
    {
        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));

        $response = $this->call('GET', 'analytics/streams/enriched?limit=0', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            'error' => "Invalid limit parameter",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function correctLimitParameterHappyPath200(): void
    {
        $streamData = [
            [
                "id" => "321112823545",
                "user_id" => "92038375",
                "user_name" => "caedrel",
                "viewer_count" => 67128,
                "title" => "🔴LEC COSTREAM FNC VS KC - LOSER IS OUT TILL SUMMER AYOOOOOOO  🔴-  !dpmlol !discord !displate"
            ]
        ];

        $userData = [
            [
                "id" => "92038375",
                "login" => "caedrel",
                "display_name" => "Caedrel",
                "type" => "",
                "broadcaster_type" => "",
                "description" => "",
                "profile_image_url" =>
                    "https://static-cdn.jtvnw.net/jtv_user_pictures/483a37ac-58fd-4e2f-8dc3-2c68a0164112-profile_image-300x300.png",
                "offline_image_url" => "",
                "view_count" => 0,
                "created_at" => "2020-01-01T00:00:00Z"
            ]
        ];

        $expectedResponse = [
            [
                "stream_id" => "321112823545",
                "user_id" => "92038375",
                "user_name" => "caedrel",
                "viewer_count" => 67128,
                "title" => "🔴LEC COSTREAM FNC VS KC - LOSER IS OUT TILL SUMMER AYOOOOOOO  🔴-  !dpmlol !discord !displate",
                "user_display_name" => "Caedrel",
                "profile_image_url" =>
                    "https://static-cdn.jtvnw.net/jtv_user_pictures/483a37ac-58fd-4e2f-8dc3-2c68a0164112-profile_image-300x300.png"
            ]
        ];

        $twitchAPIManagerMock = mock(TwitchAPIManager::class);

        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForEnrichedEndPoint')
            ->once()
            ->with(1)
            ->andReturn(new ResponseTwitchData(200, json_encode(['data' => $streamData])));

        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->once()
            ->with("92038375")
            ->andReturn(new ResponseTwitchData(200, json_encode(['data' => $userData])));

        $mockDbManager = \Mockery::mock(MYSQLDBManager::class);
        $mockDbManager->shouldReceive('getExpirationDayOfToken')
            ->with('fakeValidToken')
            ->andReturn([
                'fecha_token' => date('Y-m-d H:i:s', time() - 3600),
            ]);

        $this->app->instance(TokenVerifyer::class, new TokenVerifyer($mockDbManager));
        $this->app->instance(TwitchAPIManager::class, $twitchAPIManagerMock);

        $response = $this->call('GET', 'analytics/streams/enriched?limit=1', [], [], [], [
            'HTTP_Authorization' => 'Bearer fakeValidToken',
        ]);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
        $this->assertEquals($expectedResponse, $responseData);
    }
}
