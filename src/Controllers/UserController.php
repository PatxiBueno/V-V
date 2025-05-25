<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\UserValidator;

class UserController
{
    private TwitchAPIManager $twitchAPIManager;
    public function __construct($twitchAPIManager)
    {
        $this->twitchAPIManager = $twitchAPIManager;
    }

    public function getUser(Request $request)
    {
        $userValidator = new UserValidator();
        $userId = $request->get('id');

        if (!$userValidator->isValidId($userId)) {
            return response()->json(["error" => "Invalid or missing 'id' parameter."], 400);
        }

        $user = new User($this->twitchAPIManager);
        $response = $user->getUser($userId);
        return response()->json($response['data'], $response['http_code']);
    }
}
