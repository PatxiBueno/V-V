<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\Register;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EmailValidator;

class RegisterController
{
    private EmailValidator $emailValidator;
    private Register $registerService;
    public function __construct(EmailValidator $emailValidator, Register $registerService)
    {
        $this->emailValidator = $emailValidator;
        $this->registerService = $registerService;
    }

    public function registerUser(Request $request)
    {
        $data = $request->json()->all();
        if (!$this->emailValidator->existsEmail($data)) {
            return response()->json(["error" => "The email is mandatory"], 400);
        }
        if (!$this->emailValidator->emailIsValid($data["email"])) {
            return response()->json(["error" => "The email must be a valid email address"], 400);
        }
        $sanitizedEmail = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
        $response = $this->registerService->registerUser($sanitizedEmail);
        return response()->json($response['data'], $response['http_code']);
    }
}
