<?php

    require_once __DIR__ . '/../../twirch/twitchToken.php';
header("Content-type: application/json; charset=utf-8");
function streams()
{

    //Configurar llamada a la API
    $url = "https://api.twitch.tv/helix/streams?first=40";
    $headers = [
        "Authorization: Bearer " . gen_token() ,
        "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
    ];
//Configurar opciones de cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//Ejecutar cURL
    $response = curl_exec($ch);
//Obtener el código de estado
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
//Preparar array para crear el json
    $infoStreams = [];
//Verificar el código de estado
    http_response_code($httpCode);
    if ($httpCode == 200) {
    //Procesamos el JSON
        $data = json_decode($response, true);
    //Recorrer el array para guardar lo que nos interesa
        foreach ($data["data"] as $stream) {
            $nuevoStream = [
                "title" => $stream["title"],
                "user_name" => $stream["user_name"]
            ];
            $infoStreams[] = $nuevoStream;
        }
        return ['data' => $infoStreams, 'http_code' => $httpCode];
    } elseif ($httpCode == 401) {
        $respuesta = ["error" => "Unauthorized. Twitch access token is invalid or has expired."];
        return ['data' => $respuesta, 'http_code' => $httpCode];
    } elseif ($httpCode == 500) {
        $respuesta = ["error" => "Internal server error."];
        return ['data' => $respuesta, 'http_code' => $httpCode];
    } else {
        return ['data' => ["error" => "Internal server error."], 'http_code' => $httpCode];
    }
}