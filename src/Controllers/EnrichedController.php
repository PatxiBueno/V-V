<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\Enriched;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EnrichedValidator;

class EnrichedController
{
    private EnrichedValidator $enrichedValidator;
    private Enriched $enrichedService;
    public function __construct(EnrichedValidator $enrichedValidator, Enriched $enrichedService)
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
