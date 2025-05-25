<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\UserValidator;

class UserController
{
    private TwitchAPIManager $twitchAPIManager;
    private UserValidator $userValidator;
    public function __construct($twitchAPIManager,$userValidator)
    {
        $this->twitchAPIManager = $twitchAPIManager;
        $this->userValidator = $userValidator;
    }

    public function getUser(Request $request)
    {
        $userId = $request->get('id');

        if (!$this->userValidator->isValidId($userId)) {
            return response()->json(["error" => "Invalid or missing 'id' parameter."], 400);
        }

        $user = new User($this->twitchAPIManager);
        $response = $user->getUser($userId);
        return response()->json($response['data'], $response['http_code']);
    }
}
