<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\TopsOfTheTops;
use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;

class TopsOfTheTopsController
{
    public function getTopsOfTheTops(Request $request)
    {
        $topsValidator = new TopsOfTheTopsValidator();
        $since = $request->get("since", 600);

        if(!$topsValidator->validateSince($since)) {
            return response()->json(["error" => "Bad request. Invalid or missing parameters."], 400);
        }

        $topsOfTheTops = new TopsOfTheTops($request, new TwitchAPIManager());
        $response = $topsOfTheTops->getTops($since);
        return response()->json($response['data'], $response['http_code']);
    }
}
