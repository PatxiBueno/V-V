<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\Enriched;
use Illuminate\Http\Request;

class EnrichedController
{
    public function getEnriched(Request $request)
    {
        $user = new Enriched($request);
        return $user->getEnriched();
    }
}
