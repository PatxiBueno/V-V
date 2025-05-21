<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\User;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class UserEndPointTest extends TestCase
{
    /**
     * @test
     *
     */
    public function missingParameterIdErrorCode400():void
    {
        $request = new Request();
        $twitchAPIManagerMock = Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock
            ->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->andReturn(new ResponseTwitchData(400,""));
        $user = new User($request,$twitchAPIManagerMock);

        $response = $user->getUser();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error'=>"Invalid or missing 'id' parameter."],
            $responseData);
    }

    /**
     * @test
     *
     */
    public function invalidParameterIdErrorCode400():void
    {
        $argumentsForCalls = ['id' => -1];
        $request = new Request($argumentsForCalls);
        $twitchAPIManagerMock = Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(-1)
            ->andReturn(new ResponseTwitchData(400,""));
        $user = new User($request,$twitchAPIManagerMock);

        $response = $user->getUser();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error'=>"Invalid or missing 'id' parameter."],
            $responseData);
    }

    /**
     * @test
     *
     */
    public function userNotFoundErrorCode404():void
    {
        $argumentsForCalls = ['id' => 0];
        $request = new Request($argumentsForCalls);
        $twitchAPIManagerMock = Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(0)
            ->andReturn(new ResponseTwitchData(404,""));
        $user = new User($request,$twitchAPIManagerMock);

        $response = $user->getUser();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            'error'=>"User not found."],
            $responseData);
    }

    /**
     * @test
     *
     */
    public function validIdOneReturnsStreamerInfo200():void
    {
        $argumentsForCalls = ['id' => 1];
        $request = new Request($argumentsForCalls);
        $twitchAPIManagerMock = Mockery::mock(TwitchAPIManager::class);
        $twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(1)
            ->andReturn(new ResponseTwitchData(200,json_encode(["data"=>[[
                "id" => "1",
                "login" => "elsmurfoz",
                "display_name" => "elsmurfoz",
                "type" => "",
                "broadcaster_type" => "",
                "description" => "",
                "profile_image_url" => "https://static-cdn.jtvnw.net/user-default-pictures-uv/215b7342-def9-11e9-9a66-784f43822e80-profile_image-300x300.png",
                "offline_image_url" => "",
                "view_count" => 0,
                "created_at" => "2007-05-22T10:37:47Z"
            ]]])
            ));

        $user = new User($request,$twitchAPIManagerMock);

        $response = $user->getUser();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(["id" => "1",
                "login" => "elsmurfoz",
                "display_name" => "elsmurfoz",
                "type" => "",
                "broadcaster_type" => "",
                "description" => "",
                "profile_image_url" => "https://static-cdn.jtvnw.net/user-default-pictures-uv/215b7342-def9-11e9-9a66-784f43822e80-profile_image-300x300.png",
                "offline_image_url" => "",
                "view_count" => 0,
                "created_at" => "2007-05-22T10:37:47Z"
        ], $responseData);
    }

}
