<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\UserValidator;

class UserController
{
    public function getUser(Request $request)
    {
        $userValidator = new UserValidator();
        $userId = $request->get('id');

        if (!$userValidator->isValidId($userId)) {
            return response()->json(["error" => "Invalid or missing 'id' parameter."], 400);
        }

        $user = new User(new TwitchAPIManager());
        $response = $user->getUserData($userId);
        return response()->json($response['data'], $response['http_code']);
    }
}
