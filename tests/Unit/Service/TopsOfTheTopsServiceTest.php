<?php

namespace TwitchAnalytics\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\TopsOfTheTopsService;

class TopsOfTheTopsServiceTest extends TestCase
{
    /**
     * @test
     **/
    public function happyPathForCacheUsage()
    {
        $mockDBManager = mock(MYSQLDBManager::class);
        $mockDBManager
            ->shouldReceive('getTopsCacheData')
            ->andReturn([[
                "game_id" => '2',
                "game_name" => 'Minecraft',
                "user_name" => 'user',
                "total_videos" => '4',
            ]]);
        $mockDBManager
            ->shouldReceive('getCacheInsertTime')
            ->andReturn([
                'fecha_insercion' => '2055-06-05 14:23:45',
            ]);
        $mockTwitchApiManager = mock(TwitchApiManager::class);
        $topsOfTheTopsService = new TopsOfTheTopsService($mockTwitchApiManager, $mockDBManager);

        $response = $topsOfTheTopsService->getTops(1);
        $expectedResponse = [["game_id" => '2',
            "game_name" => 'Minecraft',
            "user_name" => 'user',
            "total_videos" => '4',
        ]];

        $this->assertEquals($expectedResponse, $response['data']);
        $this->assertEquals(200, $response['http_code']);
    }

    /**
     * @test
     **/
    public function happyPathForApiUsage(): void
    {
        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager
        ->shouldReceive('getCacheInsertTime')
        ->andReturn([
            'fecha_insercion' => '2005-06-03 14:23:45',
        ]);

        $mysqlManager
        ->shouldReceive('cleanTopOfTheTopsCache')
        ->andReturn(true);

        $mysqlManager
        ->shouldReceive('insertTopsCache')
        ->andReturn(true);

        $mysqlManager
        ->shouldReceive('deleteTopsDate')
        ->andReturn(true);

        $mysqlManager
        ->shouldReceive('insertTopsDate')
        ->andReturn(true);

        $apiManager = mock(TwitchAPIManager::class);
        $apiManager
        ->shouldReceive('curlToTwitchApiForTopThreeGames')
        ->andReturn(new ResponseTwitchData(200, json_encode(["data" => [[
            "id" => "509658",
            "name" => "Just Chatting"
        ]]])));

        $apiManager
            ->shouldReceive('curlToTwitchApiForGameById')
            ->andReturn(new ResponseTwitchData(200, json_encode(["data" => [[
                "user_id" => "123456",
                "game_name" => "Just Chatting",
                "user_name" => "KaiCenat",
                "view_count" => 45000,
                "title" => "Funny Moments",
                "duration" => "1h 10m",
                "created_at" => "2023-10-01T12:00:00Z"
            ]]])));

        $topsOfTheTopsService = new TopsOfTheTopsService($apiManager, $mysqlManager);

        $response = $topsOfTheTopsService->getTops(600);

        $this->assertEquals(200, $response["http_code"]);
    }
}
