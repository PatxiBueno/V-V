<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\Token;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\apiKeyValidator;
use TwitchAnalytics\Validators\EmailValidator;

class TokenController
{
    private MYSQLDBManager $dbManager;
    private EmailValidator $emailValidator;
    public function __construct($dbManager,$emailValidator)
    {
        $this->dbManager = $dbManager;
        $this->emailValidator = $emailValidator;
    }

    public function getToken(Request $request)
    {
        $data = $request->json()->all();

        $apikeyValidator = new ApiKeyValidator();

        if (!$apikeyValidator->existsApiKey($data)) {
            return response()->json(["error" => "The api_key is mandatory"], 400);
        }
        if (!$this->emailValidator->existsEmail($data)) {
            return response()->json(["error" => "The email is mandatory"], 400);
        }
        if (!$this->emailValidator->emailIsValid($data["email"])) {
            return response()->json(["error" => "The email must be a valid email address"], 400);
        }
        $token = new Token($this->dbManager);
        $sanitizedEmail = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
        $apiKey = $request->get("api_key");

        $response = $token->genToken($sanitizedEmail, $apiKey);
        return response()->json($response['data'], $response['http_code']);
    }
}