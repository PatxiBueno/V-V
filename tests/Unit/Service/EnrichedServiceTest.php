<?php

namespace TwitchAnalytics\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\EnrichedService;

use function PHPUnit\Framework\assertEquals;

class EnrichedServiceTest extends TestCase
{
    /**
     * @test
     *
     */
    public function enrichedHappyPath(): void
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

        $enrichedService = new EnrichedService($twitchAPIManagerMock);

        $response = $enrichedService->getEnriched(1);

        assertEquals(200, $response["http_code"]);
        $this->assertEquals($expectedResponse, $response["data"]);
    }
}
