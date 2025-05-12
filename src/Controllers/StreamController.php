<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\Streams;
use Illuminate\Http\Request;

class StreamController
{
    public function getStreams(): \Illuminate\Http\JsonResponse
    {
        $streams = new Streams();
        return $streams->getStreams();
    }
}
