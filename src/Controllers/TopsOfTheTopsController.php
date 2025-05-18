<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\TopsOfTheTops;
use Illuminate\Http\Request;

class TopsOfTheTopsController
{
    public function getTopsOfTheTops(Request $request)
    {
        $topsOfTheTops = new TopsOfTheTops($request);
        return $topsOfTheTops->getTops();
    }
}