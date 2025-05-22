<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../endPoints/post/register.php';
class Register
{
    private Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function registerUser(): \Illuminate\Http\JsonResponse
    {
        $data = $this->request->json()->all();
        $response = register($data);
        return response()->json($response['data'], $response['http_code']);
    }
}
