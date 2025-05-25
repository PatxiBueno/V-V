<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\TopsOfTheTops;
use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;

class TopsOfTheTopsController
{
    private TwitchAPIManager $twitchAPIManager;
    private TopsOfTheTopsValidator $topsValidator;

    public function __construct($twitchAPIManager,$topsValidator)
    {
        $this->twitchAPIManager = $twitchAPIManager;
        $this->topsValidator = $topsValidator;
    }

    public function getTopsOfTheTops(Request $request)
    {
        $since = $request->get("since", 600);

        if (!$this->topsValidator->validateSince($since)) {

            return response()->json(["error" => "Bad request. Invalid or missing parameters."], 400);
        }

        $topsOfTheTops = new TopsOfTheTops($this->twitchAPIManager);
        $response = $topsOfTheTops->getTops($since);
        return response()->json($response['data'], $response['http_code']);
    }
}
