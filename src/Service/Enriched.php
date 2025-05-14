<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../endPoints/get/enriched.php';
class Enriched
{
    private Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function getEnriched(): JsonResponse
    {
        $limit = $this->request->get('limit');
        $response = enriched($limit);
        return response()->json($response['data'], $response['http_code']);
    }
}
