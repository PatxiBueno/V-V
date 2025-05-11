<?php

require_once __DIR__ . '/../../twirch/twitchToken.php';
header("Content-type: application/json; charset=utf-8");
//Caso 3: GET /analytics/streams/enriched?limit=3

//Coger el limite de streams

function enriched($limit)
{
    if (!isset($limit) || $limit < 1 || $limit > 100) {
        $respuesta = ["error" => "Invalid limit parameter"];
        return ['data' => $respuesta, 'http_code' => 400];
    }
    //Paso 1: coger los streams
    //Configurar llamada a la API
    $urlStreams = "https://api.twitch.tv/helix/streams?first=" . $limit;
    $headersStreams = [
        "Authorization: Bearer " . gen_token(),
        "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
    ];

    //Configurar opciones de cURL
    $chStreams = curl_init();
    curl_setopt($chStreams, CURLOPT_URL, $urlStreams);
    curl_setopt($chStreams, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chStreams, CURLOPT_HTTPHEADER, $headersStreams);
    curl_setopt(
        $chStreams,
        CURLOPT_USERAGENT,
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0"
    );
    //Ejecutar cURL
    $responseStreams = curl_exec($chStreams);

    //Obtener el código de estado
    $httpCodeStreams = curl_getinfo($chStreams, CURLINFO_HTTP_CODE);

    //Preparar array para crear el json
    $infoStreamsEnriquecidos = [];

    //Verificar el código de estado
    if ($httpCodeStreams == 200) {
        //Procesamos el JSON
        $dataStreams = json_decode($responseStreams, true);

        //Recorrer el array para guardar lo que nos interesa
        foreach ($dataStreams["data"] as $stream) {
            //Limitamos el numero de streams
            if ($limit > 0) {
                //Paso 2: coger info de streamer para cada stream
                $idStreamer = $stream["user_id"];

                //Configurar llamada a la API
                $urlUser = "https://api.twitch.tv/helix/users?id=" . $idStreamer;
                $headersUser = [
                    "Authorization: Bearer " . gen_token(),
                    "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
                ];

                //Configurar opciones de cURL
                $chUser = curl_init();
                curl_setopt($chUser, CURLOPT_URL, $urlUser);
                curl_setopt($chUser, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chUser, CURLOPT_HTTPHEADER, $headersUser);

                //Ejecutar cURL
                $responseUser = curl_exec($chUser);

                //Obtener el código de estado
                $httpCodeUser = curl_getinfo($chUser, CURLINFO_HTTP_CODE);

                // Verificar el código de estado
                if ($httpCodeUser == 200) {
                    //Procesamos el JSON
                    $data = json_decode($responseUser, true);
                    foreach ($data["data"] as $streamer) {
                        $user_name = $streamer["login"];
                        $display_name = $streamer["display_name"];
                        $profile_image_url = $streamer["profile_image_url"];
                    }
                } else {
                    return ['data' => ["error" => "Internal server error."], 'http_code' => $httpCodeUser];
                }

                $nuevoStreamEnriquecido = [
                    "stream_id" => $stream["id"],
                    "user_id" => $idStreamer,
                    "user_name" => $user_name,
                    "viewer_count" => $stream["viewer_count"],
                    "title" => $stream["title"],
                    "user_display_name" => $display_name,
                    "profile_image_url" => $profile_image_url
                ];

                // Cerrar la conexión cURL
                curl_close($chUser);

                //Añadir stream
                $infoStreamsEnriquecidos[] = $nuevoStreamEnriquecido;
                $limit--;
            }
        }
        return ['data' => $infoStreamsEnriquecidos, 'http_code' => $httpCodeStreams];
    } elseif ($httpCodeStreams == 401) {
        curl_close($chStreams);
        $respuesta = ["error" => "Unauthorized. Twitch access token is invalid or has expired"];
        return ['data' => $respuesta, 'http_code' => $httpCodeStreams];
    } elseif ($httpCodeStreams == 500) {
        curl_close($chStreams);
        $respuesta = ["error" => "Internal server error"];
        return ['data' => $respuesta, 'http_code' => $httpCodeStreams];
    } else {
        curl_close($chStreams);
        return ['data' => ["error" => "Internal server error"], 'http_code' => $httpCodeStreams];
    }
}
