<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\EnrichedService;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EnrichedValidator;

class EnrichedController
{
    private EnrichedValidator $enrichedValidator;
    private EnrichedService $enrichedService;
    public function __construct(EnrichedValidator $enrichedValidator, EnrichedService $enrichedService)
    {
        $this->enrichedValidator = $enrichedValidator;
        $this->enrichedService = $enrichedService;
    }

    public function getEnriched(Request $request)
    {
        $limit = $request->get('limit');

        if (!$this->enrichedValidator->isValidLimit($limit)) {
            return response()->json(["error" => "Invalid limit parameter"], 400);
        }

        $response = $this->enrichedService->getEnriched($limit);
        return response()->json($response['data'], $response['http_code']);
    }
}
