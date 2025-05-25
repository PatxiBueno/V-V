<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\TopsOfTheTops;
use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;

class TopsOfTheTopsController
{
    private TwitchAPIManager $twitchAPIManager;

    public function __construct($twitchAPIManager)
    {
        $this->twitchAPIManager = $twitchAPIManager;
    }

    public function getTopsOfTheTops(Request $request)
    {
        $topsValidator = new TopsOfTheTopsValidator();
        $since = $request->get("since", 600);

        if (!$topsValidator->validateSince($since)) {

            return response()->json(["error" => "Bad request. Invalid or missing parameters."], 400);
        }

        $topsOfTheTops = new TopsOfTheTops($this->twitchAPIManager);
        $response = $topsOfTheTops->getTops($since);
        return response()->json($response['data'], $response['http_code']);
    }
}
