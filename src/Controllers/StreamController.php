<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\StreamsService;
use Illuminate\Http\Request;

class StreamController
{
    private StreamsService $streamsService;

    public function __construct(StreamsService $streamsService)
    {
        $this->streamsService = $streamsService;
    }

    public function getStreams(): \Illuminate\Http\JsonResponse
    {
        return $this->streamsService->getStreams();
    }
}
