<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Controllers\UserController;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\UserValidator;
use PHPUnit\Framework\TestCase;

class UserEndPointTest extends TestCase
{
    private UserController $userController;
    protected function setUp(): void
    {
        parent::setUp();

        $twitchAPIManager = new TwitchAPIManager();//MOCK MOCK quizas, ya que solo nos da datos el 200 sino no llamamos a twitch
        $userService = new User($twitchAPIManager);
        $userValidator = new UserValidator();

        $this->userController = new UserController($userValidator, $userService);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
    /**
     * @test
     *
     */
    public function missingParameterIdErrorCode400(): void
    {

        $argumentsForCalls = ['id' => null];//falla por esto el test, en pruebas php da 500, pero en el resto da bien
        $request = new Request($argumentsForCalls);

        $response = $this->userController->getUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "Invalid or missing 'id' parameter.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function invalidParameterIdErrorCode400(): void
    {
        $argumentsForCalls = ['id' => -1];
        $request = new Request($argumentsForCalls);

        $response = $this->userController->getUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => "Invalid or missing 'id' parameter.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function userNotFoundErrorCode404(): void
    {
        $argumentsForCalls = ['id' => 0];
        $request = new Request($argumentsForCalls);

        $response = $this->userController->getUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            'error' => "User not found.",
        ], $responseData);
    }
    /**
     * @test
     *
     */
    public function validIdOneReturnsStreamerInfo200(): void
    {
        $argumentsForCalls = ['id' => 1];
        $request = new Request($argumentsForCalls);

        $response = $this->userController->getUser($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(["id" => "1",
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
        ], $responseData);
    }
}
