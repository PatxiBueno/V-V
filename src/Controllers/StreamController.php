<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\Streams;
use Illuminate\Http\Request;

class StreamController
{
    private Streams $streamsService;

    public function __construct(Streams $streamsService)
    {
        $this->streamsService = $streamsService;
    }

    public function getStreams(): \Illuminate\Http\JsonResponse
    {
        return $this->streamsService->getStreams();
    }
}
