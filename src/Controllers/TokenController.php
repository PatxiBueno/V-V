<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\Token;
use Illuminate\Http\Request;

class TokenController
{
    public function getToken(Request $request)
    {
        $token = new Token($request);
        return $token->genToken();
    }
}
