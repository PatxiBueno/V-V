<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\Token;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\ApiKeyValidator;
use TwitchAnalytics\Validators\EmailValidator;

class TokenController
{
    private EmailValidator $emailValidator;
    private ApiKeyValidator $apiKeyValidator;
    private Token $tokenService;


    public function __construct(EmailValidator $emailValidator, ApiKeyValidator $apiKeyValidator, Token $tokenService)
    {
        $this->emailValidator = $emailValidator;
        $this->apiKeyValidator = $apiKeyValidator;
        $this->tokenService = $tokenService;
    }

    public function getToken(Request $request)
    {
        $data = $request->json()->all();

        if (!$this->apiKeyValidator->existsApiKey($data)) {
            return response()->json(["error" => "The api_key is mandatory"], 400);
        }
        if (!$this->emailValidator->existsEmail($data)) {
            return response()->json(["error" => "The email is mandatory"], 400);
        }
        if (!$this->emailValidator->emailIsValid($data["email"])) {
            return response()->json(["error" => "The email must be a valid email address"], 400);
        }
        $sanitizedEmail = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
        $apiKey = $request->get("api_key");

        $response = $this->tokenService->genToken($sanitizedEmail, $apiKey);
        return response()->json($response['data'], $response['http_code']);
    }
}