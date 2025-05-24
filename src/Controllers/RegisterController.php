<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\Register;
use Illuminate\Http\Request;

class RegisterController
{
    public function registerUser(Request $request)
    {
        $register = new Register($request);
        return $register->registerUser();
    }
}
