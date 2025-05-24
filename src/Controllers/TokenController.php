<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\Token;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\apiKeyValidator;

class TokenController
{
    public function getToken(Request $request)
    {
        $data = $request->json()->all();

        $apikeyValidator = new ApiKeyValidator();

        if (!$apikeyValidator->existsApiKey($data)) {
            return response()->json(["error" => "The api_key is mandatory"], 400);
        }

        $token = new Token();
        $response = $token->genToken($data);
        return response()->json($response['data'], $response['http_code']);
    }
}
