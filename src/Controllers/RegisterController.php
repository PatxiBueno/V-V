<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\Register;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EmailValidator;

class RegisterController
{
    private MYSQLDBManager $dbManager;

    public function __construct($dbManager)
    {
        $this->dbManager = $dbManager;
    }

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
        $register = new Register($this->dbManager);
        $sanitizedEmail = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
        $response = $register->registerUser($sanitizedEmail);
        return response()->json($response['data'], $response['http_code']);
    }
}
