<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\TopsOfTheTops;
use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;

class TopsOfTheTopsController
{
    private TopsOfTheTopsValidator $topsValidator;
    private TopsOfTheTops $topsOfTheTopsService;

    public function __construct(TopsOfTheTopsValidator $topsValidator, TopsOfTheTops $topsOfTheTopsService)
    {
        $this->topsValidator = $topsValidator;
        $this->topsOfTheTopsService = $topsOfTheTopsService;
    }

    public function getTopsOfTheTops(Request $request)
    {
        $since = $request->get("since", 600);
        if (!$this->topsValidator->validateSince($since)) {
            return response()->json(["error" => "Bad request. Invalid or missing parameters."], 400);
        }

        $response = $this->topsOfTheTopsService->getTops($since);
        return response()->json($response['data'], $response['http_code']);
    }
}
