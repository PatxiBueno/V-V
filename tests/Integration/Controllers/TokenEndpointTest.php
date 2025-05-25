<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\Token;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class TokenEndPointTest extends TestCase
{
    /**
     * @test
     *
     */
    public function missingMailIdErrorCode400(): void
    {
        $request = new Request();
        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager
            ->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->andReturn(new ResponseTwitchData(400, ""));
        $token = new Token($request, $mysqlManager);

        $response = $token->genToken();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
            'error' => "The email is mandatory"],
            $responseData
        );
    }
}
