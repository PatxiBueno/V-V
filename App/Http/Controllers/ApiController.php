<?php

namespace TwitchAnalytics\Http\Controllers;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../../twirch/twitchToken.php';
require_once __DIR__ . '/../../../endPoints/get/streams.php';
require_once __DIR__ . '/../../../endPoints/get/enriched.php';
require_once __DIR__ . '/../../../endPoints/get/user.php';
require_once __DIR__ . '/../../../endPoints/get/topsofthetops.php';
require_once __DIR__ . '/../../../endPoints/post/token.php';
require_once __DIR__ . '/../../../endPoints/post/register.php';
require_once __DIR__ . '/../../../autenticacion.php';
class ApiController
{
    public function getUser(Request $request)
    {
        $autenticacion = $this->noAutentificado($request);
        if ($autenticacion) {
            return $autenticacion;
        }
        $id = $request->get('id');
        $response = getUserFromApi($id);
        return response()->json($response['data'], $response['http_code']);
    }

    public function getStreams(Request $request)
    {
        $autenticacion = $this->noAutentificado($request);
        if ($autenticacion) {
            return $autenticacion;
        }
        $response = streams();
        return response()->json($response['data'], $response['http_code']);
    }

    public function getEnriched(Request $request)
    {
        $autenticacion = $this->noAutentificado($request);
        if ($autenticacion) {
            return $autenticacion;
        }
        $limit = $request->get('limit');
        $response = enriched($limit);
        return response()->json($response['data'], $response['http_code']);
    }

    public function getTopsOfTheTops(Request $request)
    {
        $autenticacion = $this->noAutentificado($request);
        if ($autenticacion) {
            return $autenticacion;
        }
        $since = $request->get('since', 600);
        $response = getTopOfTheTops($since);
        return response()->json($response['data'], $response['http_code']);
    }

    public function getToken(Request $request)
    {
        $data = $request->json()->all();
        $response = generarToken($data);
        return response()->json($response['data'], $response['http_code']);
    }

    public function register(Request $request)
    {
        $data = $request->json()->all();
        $response = register($data);
        return response()->json($response['data'], $response['http_code']);
    }

    public function noAutentificado(Request $request)
    {
        $headers = $request->headers->all();
        if (!validarToken($headers)) {
            return response()->json(['error' => 'Unauthorized. Token is invalid or expired.'], 401);
        }
    }
}
