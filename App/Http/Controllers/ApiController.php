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
        $this->noAutentificado( $request);
        $id = $request->get('id');

        return getUserFromApi($id);
    }

    public function getStreams(Request $request)
    {
        $this->noAutentificado( $request);
        return streams();
    }

    public function getEnriched(Request $request)
    {
        $this->noAutentificado( $request);

        $limit = $request->get('limit');

        return enriched($limit);
    }

    public function getTopsOfTheTops(Request $request)
    {
        $this->noAutentificado( $request);

        $since = $request->get('since',600);

        return getTopOfTheTops($since);
    }

    public function getToken(Request $request)
    {
        $data = $request->json()->all();

        return generarToken($data);
    }

    public function register(Request $request)
    {
        $data = $request->json()->all();

        return register($data);
    }

    /**
     * @return void
     */
    public function noAutentificado(Request $request): void
    {
        $headers = $request->headers->all();
        app("log")->error("esta son las cabeceras",$headers);
        if (!validarToken($headers)) {
            http_response_code(401);
            $respuesta = ["error" => "Unauthorized. Token is invalid or expired."];
            echo json_encode($respuesta);
            exit;
        }
    }

}
