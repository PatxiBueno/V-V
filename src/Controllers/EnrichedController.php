<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Service\Enriched;
use Illuminate\Http\Request;

class EnrichedController
{
    public function getEnriched(Request $request)
    {
        $user = new Enriched($request, new TwitchAPIManager());
        return $user->getEnriched();
    }
}
