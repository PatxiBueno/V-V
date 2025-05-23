<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../endPoints/get/topsofthetops.php';
class TopsOfTheTops
{
    private Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function getTops()
    {
        $since = $this->request->get('since');
        $response = getTopOfTheTops($since);
        return response()->json($response['data'], $response['http_code']);
    }
}
