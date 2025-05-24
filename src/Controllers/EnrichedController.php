<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\Enriched;
use Illuminate\Http\Request;
use TwitchAnalytics\Validators\EnrichedValidator;

class EnrichedController
{
    public function getEnriched(Request $request)
    {
        $limit = $request->get('limit');
        $enrichedValidator = new EnrichedValidator();

        if (!$enrichedValidator->isValidLimit($limit)) {
            return response()->json(["error" => "Invalid limit parameter"], 400);
        }

        $enriched = new Enriched(new TwitchAPIManager());
        $response = $enriched->getEnriched($limit);
        return response()->json($response['data'], $response['http_code']);
    }
}
