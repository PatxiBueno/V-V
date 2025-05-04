<?php
namespace TwitchAnalytics\Http\Controllers;

use Illuminate\Http\Request;

require_once __DIR__ . '/../../../twirch/twitchToken.php'; 
require_once __DIR__ . '/../../../endPoints/get/streams.php';
require_once __DIR__ . '/../../../endPoints/get/enriched.php'; 
require_once __DIR__ . '/../../../endPoints/get/user.php'; 
require_once __DIR__ . '/../../../endPoints/get/topsofthetops.php';
require_once __DIR__ . '/../../../endPoints/post/token.php'; 

class ApiController
{
    public function getUser(Request $request)
    {
        $id = $request->get('id');

        return getUserFromApi($id);
    }

    public function getStreams()
    {
        return streams();
    }

    public function getEnriched(Request $request)
    {
        $limit = $request->get('limit');

        return enriched($limit);
    }

    public function getTopsOfTheTops(Request $request)
    {
        $since = $request->get('since',600);

        return getTopOfTheTops($since);
    }

    public function getToken(Request $request)
    {
        $email = $request->post('email');

        return generarToken($email);
    }

}
