<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../endPoints/post/token.php';
class Token
{
    private Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function genToken()
    {
        $data = $this->request->json()->all();
        $response = generarToken($data);
        return response()->json($response['data'], $response['http_code']);
    }
}
