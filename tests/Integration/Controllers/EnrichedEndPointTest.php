<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Controllers\EnrichedController;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\Enriched;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EnrichedValidator;
use PHPUnit\Framework\TestCase;

class EnrichedEndPointTest extends TestCase
{
    private EnrichedController $enrichedController;
    private EnrichedValidator $enrichedValidator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->enrichedValidator = new EnrichedValidator();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
    /**
     * @test
     *
     */
    public function invalidParameterLimitErrorCode400(): void
    {

        $argumentsForCalls = ['limit' => 0];
        $request = new Request($argumentsForCalls);
        $twitchAPIManagerMock = mock(TwitchAPIManager::class);
        $enrichedService = new Enriched($twitchAPIManagerMock);
        $this->enrichedController = new EnrichedController($this->enrichedValidator, $enrichedService);

        $response = $this->enrichedController->getEnriched($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "Invalid limit parameter",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function missingParameterLimitErrorCode400(): void
    {

        $argumentsForCalls = ['limit'];
        $request = new Request($argumentsForCalls);
        $twitchAPIManagerMock = mock(TwitchAPIManager::class);
        $enrichedService = new Enriched($twitchAPIManagerMock);
        $this->enrichedController = new EnrichedController($this->enrichedValidator, $enrichedService);

        $response = $this->enrichedController->getEnriched($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "Invalid limit parameter",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function validLimitOneReturnsListStreamerInfo200(): void
    {
        $argumentsForCalls = ['limit' => 1];
        $request = new Request($argumentsForCalls);

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


        $enrichedService = new Enriched($twitchAPIManagerMock);
        $this->enrichedController = new EnrichedController($this->enrichedValidator, $enrichedService);

        $response = $this->enrichedController->getEnriched($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $responseData);
    }
}
