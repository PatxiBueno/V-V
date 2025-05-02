<?php

namespace TwitchAnalytics\Http\Controllers;
require_once __DIR__ . '/../../../twirch/twitchToken.php'; 
class ApiController
{
    public function getUser($id)
    {
        return getUserById($id);  // Aquí llamas a la lógica que necesites
    }

    public function genToken()
    {
        return gen_token();  // Aquí llamas a la función para generar el token
    }
}
