<?php

namespace TwitchAnalytics\Middleware;

use Closure;
use Illuminate\Http\Request;
use TwitchAnalytics\Managers\MYSQLDBManager;

class VerifyToken
{
    private MYSQLDBManager $dbManager;
    public function __construct($dbManager)
    {
        $this->dbManager = $dbManager;
    }

    public function handle(Request $request, Closure $next)
    {
        $headers = $request->headers->all();
        if (!$this->validateToken($headers)) {
            return response()->json(['error' => 'Unauthorized. Token is invalid or expired.'], 401);
        }

        return $next($request);
    }

    public function validateToken($headers)
    {
        if (!isset($headers['authorization'])) {
            return false;
        }

        list($bearer, $userToken) = explode(" ", $headers['authorization'][0], 2);

        if (strcasecmp($bearer, "Bearer") != 0) {
            return false;
        }

        $expirationDate = $this->dbManager->getExpirationDayOfToken($userToken);

        if (!$expirationDate) {
            return false;
        }

        $timestampToken = $expirationDate['fecha_token'];
        $tiempoDelToken = time() - strtotime($timestampToken);

        if ($tiempoDelToken > 259200) {
            return false;
        }
        return true;
    }
}
