<?php

namespace TwitchAnalytics\Tests\Unit\Service;

use Mockery;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\UserService;

use function PHPUnit\Framework\assertEquals;

class UserServiceTest extends TestCase
{
    private TwitchAPIManager $twitchAPIManagerMock;
    protected function setUp(): void
    {
        parent::setUp();
        $this->twitchAPIManagerMock = mock(TwitchAPIManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }


    /**
     * @test
     *
     */
    public function userNotFoundErrorCode404(): void
    {
        $this->twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(0)
            ->andReturn(new ResponseTwitchData(404, ""));
        $userService = new UserService($this->twitchAPIManagerMock);

        $response = $userService->getUser(0);

        assertEquals(404, $response["http_code"]);
        assertEquals(["error" => "User not found."], $response["data"]);
    }
    /**
     * @test
     *
     */
    public function userHappyPath(): void
    {
        $mockResponseData = json_encode(["data" => [[
            "id" => "1",
            "login" => "elsmurfoz",
            "display_name" => "elsmurfoz",
            "type" => "",
            "broadcaster_type" => "",
            "description" => "",
            "profile_image_url" =>
                "https://static-cdn.jtvnw.net/user-default-pictures-uv/215b7342-def9-11e9-9a66-784f43822e80-profile_image-300x300.png",
            "offline_image_url" => "",
            "view_count" => 0,
            "created_at" => "2007-05-22T10:37:47Z"
        ]]]);
        $this->twitchAPIManagerMock->shouldReceive('curlToTwitchApiForUserEndPoint')
            ->with(1)
            ->andReturn(new ResponseTwitchData(200, $mockResponseData));
        $userService = new UserService($this->twitchAPIManagerMock);

        $response = $userService->getUser(1);

        assertEquals(200, $response["http_code"]);
        assertEquals(["id" => "1",
            "login" => "elsmurfoz",
            "display_name" => "elsmurfoz",
            "type" => "",
            "broadcaster_type" => "",
            "description" => "",
            "profile_image_url" =>
                "https://static-cdn.jtvnw.net/user-default-pictures-uv/215b7342-def9-11e9-9a66-784f43822e80-profile_image-300x300.png",
            "offline_image_url" => "",
            "view_count" => 0,
            "created_at" => "2007-05-22T10:37:47Z"
        ], $response["data"]);
    }
}
