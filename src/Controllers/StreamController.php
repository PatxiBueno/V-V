<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\Streams;
use Illuminate\Http\Request;

class StreamController
{
    private TwitchAPIManager $twitchAPIManager;

    public function __construct($twitchAPIManager)
    {
        $this->twitchAPIManager = $twitchAPIManager;
    }

    public function getStreams(): \Illuminate\Http\JsonResponse
    {
        $streams = new Streams($this->twitchAPIManager);
        return $streams->getStreams();
    }
}
