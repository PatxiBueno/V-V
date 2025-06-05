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
    public function happyPathForApiUsage()
    {
        $mockDBManager = mock(MYSQLDBManager::class);
        $mockDBManager
            ->shouldReceive('getCacheInsertTime')
            ->andReturn([
                'fecha_insercion' => '2005-06-03 14:23:45',
            ]);
        $mockDBManager
            ->shouldReceive('cleanTopOfTheTopsCache')
            ->andReturn(true);
        $mockDBManager
            ->shouldReceive('insertTopsCache')
            ->andReturn(true);
        $mockTwitchApiManager = mock(TwitchApiManager::class);
        $mockTwitchApiManager
            ->shouldReceive('curlToTwitchApiForTopThreeGames')
            ->andReturn(new ResponseTwitchData(200, json_encode(['data' => [[
                "id" => "509658",
                "game_name" => "Just Chatting"
            ]]])));
        $mockTwitchApiManager
            ->shouldReceive('curlToTwitchApiForGameById')
            ->andReturn(new ResponseTwitchData(200, json_encode([[
                'user_name' => 'user'
            ]])));
        $topsOfTheTopsService = new TopsOfTheTopsService($mockTwitchApiManager, $mockDBManager);

        $response = $topsOfTheTopsService->getTops(600);
        $this->assertEquals(200, $response['http_code']);
    }
}
