<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\Streams;
use Illuminate\Http\Request;

class StreamController
{
    public function getStreams(Request $request): \Illuminate\Http\JsonResponse
    {
        $streams = new Streams($request, new TwitchAPIManager());
        return $streams->getStreams();
    }
}
