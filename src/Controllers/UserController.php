<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;

class UserController
{
    public function getUser(Request $request)
    {
        $user = new User($request,new TwitchAPIManager());
        return $user->getUser();
    }
}
