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
        $receivedData = $request->json()->all();
        if (!$emailValidator->existsEmail($receivedData)) {
            return response()->json(["error" => "The email is mandatory"], 400);
        }
        if (!$emailValidator->emailIsValid($receivedData['email'])) {
            return response()->json(["error" => "The email must be a valid email address"], 400);
        }
        $register = new Register(new MYSQLDBManager());
        $sanitizedEmail = filter_var($receivedData['email'], FILTER_SANITIZE_EMAIL);
        $response = $register->registerUserData($sanitizedEmail);
        return response()->json($response['data'], $response['http_code']);
    }
}
