<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\TopsOfTheTops;
use DateTime;
use Mockery;
use TwitchAnalytics\Controllers\TopsOfTheTopsController;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class TopsOfTheTopsEndPointTest extends TestCase
{
    private TopsOfTheTopsController $topsOfTheTopdsController;

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
        $topsOfTheTopsService = new TopsOfTheTops($apiManager, $mysqlManager);
        $this->topsOfTheTopdsController = new TopsOfTheTopsController(new TopsOfTheTopsValidator(), $topsOfTheTopsService);

        $response = $this->topsOfTheTopdsController->getTopsOfTheTops($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "Bad request. Invalid or missing parameters.",
            ], $responseData);
    }
}
