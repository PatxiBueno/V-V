<?php
echo "OPCIONES:
        \n1. Get User
        \n2. Get Streams
        \n3. Get Streams-Enriched
        \n\tIntroduce el caso de uso a ejecutar: ";
$opcion_menu = trim(fgets(STDIN));
switch($opcion_menu){
    case "1":
        //Caso 1: GET /analytics/user?id=1234

        //Coger el id del streamer que se desea buscar
        echo "Introduzca el ID del Streamer:";
        $id_usuario = trim(fgets(STDIN));

        //$id_usuario = $GET_["id"];

        //Configurar llamada a la API
        $url = "https://api.twitch.tv/helix/users?id=" . $id_usuario;
        $headers = [
            "Authorization: Bearer 09pmsrc1ov1mkg0ajinfnd5ty585j0",
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

        // Verificar el código de estado
        if ($httpCode == 200) {
            //Procesamos el JSON
            $data = json_decode($response, true);
            foreach ($data["data"] as $streamer) {
                $infoStreamer = [
                    "id" => $streamer["id"],
                    "login" => $streamer["login"],
                    "display_name" => $streamer["display_name"],
                    "type" => $streamer["type"],
                    "broadcaster_type" => $streamer["broadcaster_type"],
                    "description" => $streamer["description"],
                    "profile_image_url" => $streamer["profile_image_url"],
                    "offline_image_url" => $streamer["offline_image_url"],
                    "view_count" => $streamer["view_count"],
                    "created_at" => $streamer["created_at"]
                ];
            }
            //Generamos JSON de envio
            $jsonFinal = json_encode($infoStreamer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            echo $jsonFinal;
        } else {
            echo "⚠️ Código de error: $httpCode\n";
        }
        break;

    case "2":
        //Caso 2: GET /analytics/streams

        //Configurar llamada a la API
        $url = "https://api.twitch.tv/helix/streams";
        $headers = [
            "Authorization: Bearer 09pmsrc1ov1mkg0ajinfnd5ty585j0",
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

        //Preparar array para crear el json
        $infoStreams = [];

        //Verificar el código de estado
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

            //Generamos JSON de envio
            $jsonFinal = json_encode($infoStreams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            echo $jsonFinal;

        } else {
            echo "⚠️ Código de error: $httpCode\n";
        }

        break;

    case "3":
        //Caso 3 GET /analytics/streams/enriched?limit=3

        //Coger el limite de streams
        echo "Introduzca el limite de streams: ";
        $limit = trim(fgets(STDIN));

        //$limit = $_GET["limit"];

        //Paso 1: coger los streams
        //Configurar llamada a la API
        $urlStreams = "https://api.twitch.tv/helix/streams";
        $headersStreams = [
            "Authorization: Bearer 09pmsrc1ov1mkg0ajinfnd5ty585j0",
            "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
        ];

        //Configurar opciones de cURL
        $chStreams = curl_init();
        curl_setopt($chStreams, CURLOPT_URL, $urlStreams);
        curl_setopt($chStreams, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chStreams, CURLOPT_HTTPHEADER, $headersStreams);

        //Ejecutar cURL
        $responseStreams = curl_exec($chStreams);

        //Obtener el código de estado
        $httpCodeStreams = curl_getinfo($chStreams, CURLINFO_HTTP_CODE);

        //Preparar array para crear el json
        $infoStreams = [];

        //Verificar el código de estado
        if ($httpCodeStreams == 200) {
            //Procesamos el JSON
            $dataStreams = json_decode($responseStreams, true);

            //Recorrer el array para guardar lo que nos interesa
            foreach ($dataStreams["data"] as $stream) {
                //Limitamos el numero de streams
                if($limit > 0){
                    //Paso 2: coger info de streamer para cada stream
                    $idStreamer = $stream["user_id"];

                    //Configurar llamada a la API
                    $urlUser = "https://api.twitch.tv/helix/users?id=" . $idStreamer;
                    $headersUser = [
                        "Authorization: Bearer 09pmsrc1ov1mkg0ajinfnd5ty585j0",
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
                        echo "⚠️ Código de error: $httpCodeUser\n";
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

            //Generamos JSON de envio
            $jsonFinal = json_encode($infoStreamsEnriquecidos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            echo $jsonFinal;

        } else {
            echo "⚠️ Código de error: $httpCodeStreams\n";
        }

        // Cerrar la conexión cURL
        curl_close($chStreams);

        break;

    default:
        echo "No es una opcion posible";
        break;
}

?>
