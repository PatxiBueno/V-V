<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\Register;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EmailValidator;

class RegisterController
{
    public function registerUser(Request $request)
    {
        $emailValidator = new EmailValidator();
        $data = $request->json()->all();
        if (!$emailValidator->existsEmail($data)) {
            return response()->json(["error" => "The email is mandatory"], 400);
        }
        if (!$emailValidator->emailIsValid($data["email"])) {
            return response()->json(["error" => "The email must be a valid email address"], 400);
        }
        $register = new Register(new MYSQLDBManager());
        $response = $register->registerUser($data);
        return response()->json($response['data'], $response['http_code']);
    }
}
