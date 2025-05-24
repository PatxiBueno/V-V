<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\TopsOfTheTops;
use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;

class TopsOfTheTopsController
{
    public function getTopsOfTheTops(Request $request)
    {
        $topsOfTheTops = new TopsOfTheTops($request, new TwitchAPIManager());
        return $topsOfTheTops->getTops();
    }
}
