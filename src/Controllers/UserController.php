<?php

namespace TwitchAnalytics\Controllers;

use TwitchAnalytics\Service\User;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../autenticacion.php';
class UserController
{
    public function getUser(Request $request)
    {
        $autenticacion = $this->noAutentificado($request);
        if ($autenticacion) {
            return $autenticacion;
        }

        $user = new User($request);
        return $user->getUser();
    }

    public function noAutentificado(Request $request)
    {
        $headers = $request->headers->all();
        if (!validarToken($headers)) {
            return response()->json(['error' => 'Unauthorized. Token is invalid or expired.'], 401);
        }
    }
}
