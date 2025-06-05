<?php

namespace TwitchAnalytics\Tests\Unit\Service;

use Mockery;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\StreamsService;

class StreamsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function getStreamsReturnsExpectedResponse(): void
    {
        $expectedData = [
            'data' => [
                ['title' => 'Stream Title 1', 'user_name' => 'Streamer1'],
                ['title' => 'Stream Title 2', 'user_name' => 'Streamer2'],
            ]
        ];

        $mockResponse = new ResponseTwitchData(200, json_encode($expectedData));
        $twitchAPIManagerMock = mock(TwitchAPIManager::class);

        $this->twitchAPIManagerMock
            ->shouldReceive('curlToTwitchApiForStreamsEndPoint')
            ->once()
            ->andReturn($mockResponse);

        $service = new StreamsService($twitchAPIManagerMock);
        $response = $service->getStreams();
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
