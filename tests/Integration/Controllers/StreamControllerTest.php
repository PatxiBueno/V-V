<?php

namespace TwitchAnalytics\Tests\Integration\Controllers;

use Mockery;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\ResponseTwitchData;
use \TwitchAnalytics\Service\Streams;
use TwitchAnalytics\Controllers\StreamController;
use TwitchAnalytics\Managers\TwitchAPIManager;

class StreamControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getsStreamsHappyPath(): void
    {
        $apiManager = Mockery::mock(TwitchAPIManager::class);
        $streamService = new Streams($apiManager);
        $streamController = new StreamController($streamService);
        $mockResponse = new ResponseTwitchData(200, json_encode([
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
        ]));
        $apiManager->shouldReceive('curlToTwitchApiForStreamsEndPoint')
            ->once()
            ->andReturn($mockResponse);

        $response = $streamController->getStreams();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([[
            'title' => 'Stream Title 1',
            'user_name' => 'Streamer1',
            ],
            [
                'title' => 'Stream Title 2',
                'user_name' => 'Streamer2',
            ]], $responseData);
    }
}
