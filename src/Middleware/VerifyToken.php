<?php

namespace TwitchAnalytics\Middleware;

use Closure;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../autenticacion.php';

class VerifyToken
{
    public function handle(Request $request, Closure $next)
    {
        $headers = $request->headers->all();
        if (!validarToken($headers)) {
            return response()->json(['error' => 'Unauthorized. Token is invalid or expired.'], 401);
        }

        return $next($request);
    }
}
