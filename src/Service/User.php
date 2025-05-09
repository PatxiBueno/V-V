<?php
namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../endPoints/get/user.php';
class User 
{
    private Request $request;

    public function __construct($request) {
        $this->request = $request;
    }
    public function getUser()
    {
        $id = $this->request->get('id');
        $response = getUserFromApi($id);
        return response()->json($response['data'], $response['http_code']);
    }
}
