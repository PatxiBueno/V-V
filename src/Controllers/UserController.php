<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\UserValidator;

class UserController
{
    private User $userService;
    private UserValidator $userValidator;
    public function __construct(UserValidator $userValidator,User $userService)
    {
        $this->userValidator = $userValidator;
        $this->userService = $userService;
    }

    public function getUser(Request $request)
    {
        $userId = $request->get('id');

        if (!$this->userValidator->isValidId($userId)) {
            return response()->json(["error" => "Invalid or missing 'id' parameter."], 400);
        }

        $response = $this->userService->getUser($userId);
        return response()->json($response['data'], $response['http_code']);
    }
}
