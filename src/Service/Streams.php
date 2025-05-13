<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../endPoints/get/streams.php';
class Streams
{
    public function getStreams()
    {
        $response = getStreamsFromApi();
        return response()->json($response['data'], $response['http_code']);
    }
}
