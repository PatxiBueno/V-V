<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\Token;
use Illuminate\Http\Request;

class TokenController
{
    public function getToken(Request $request)
    {
        $token = new Token($request, new MYSQLDBManager());
        return $token->genToken();
    }
}
