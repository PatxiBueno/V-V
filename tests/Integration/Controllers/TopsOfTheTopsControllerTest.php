<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\TopsOfTheTopsService;
use DateTime;
use Mockery;
use TwitchAnalytics\Controllers\TopsOfTheTopsController;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class TopsOfTheTopsControllerTest extends TestCase
{
    private TopsOfTheTopsController $topsController;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     *
     */
    public function invalidParameterSinceErrorCode400(): void
    {
        $postData = ['since' => '-1'];
        $json = json_encode($postData);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];

        $request = new Request($postData, [], [], [], [], $server, $json);
        $mysqlManager = mock(MYSQLDBManager::class);
        $apiManager = mock(TwitchAPIManager::class);
        $topsOfTheTopsService = new TopsOfTheTopsService($apiManager, $mysqlManager);
        $this->topsController = new TopsOfTheTopsController(new TopsOfTheTopsValidator(), $topsOfTheTopsService);

        $response = $this->topsController->getTopsOfTheTops($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
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
        $postData = ['since' => '600'];
        $json = json_encode($postData);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];

        $request = new Request($postData, [], [], [], [], $server, $json);

        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager
        ->shouldReceive('getCacheInsertTime')
        ->andReturn([
            'fecha_insercion' => '2055-06-03 14:23:45',
            ]);

        $mysqlManager
        ->shouldReceive('getTopsCacheData')
        ->andReturn([]);
        $apiManager = mock(TwitchAPIManager::class);
        $topsOfTheTopsService = new TopsOfTheTopsService($apiManager, $mysqlManager);
        $this->topsController = new TopsOfTheTopsController(new TopsOfTheTopsValidator(), $topsOfTheTopsService);

        $response = $this->topsController->getTopsOfTheTops($request);

        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @test
     *
     */
    public function happyPahtForApi200(): void
    {
        $postData = ['since' => '600'];
        $json = json_encode($postData);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];

        $request = new Request($postData, [], [], [], [], $server, $json);

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
        $this->topsController = new TopsOfTheTopsController(new TopsOfTheTopsValidator(), $topsOfTheTopsService);

        $response = $this->topsController->getTopsOfTheTops($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
