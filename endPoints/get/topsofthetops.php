<?php

//Cabeceras
require_once("/var/www/html/conexion.php");
require_once("/var/www/html/twirch/twitchToken.php");
require_once("/var/www/html/autenticacion.php");
header("Content-type: application/json; charset=utf-8");

function getTopOfTheTops($since)
{
    // El parametro since es opcional, pero debe ser positivo
    if($since < 0){
        http_response_code(400);
        echo json_encode(["error" => "Bad request. Invalid or missing parameters."]);
        exit;
    }elseif ($since > 600){
        $since = 600;
    }
//Conexion a bbdd
    $con = conexion();

//Ver si han pasado menos de 10 mins
    $consultaFecha = "SELECT fecha_insercion FROM ttt_fecha";
    $resultadoFecha = $con->query($consultaFecha);
    if ($resultadoFecha) {
        $fila = $resultadoFecha->fetch_assoc();
        $ultimaActualizacion = time() - strtotime($fila['fecha_insercion']);
       
    } else {
        //No existe fecha guardada, hay que actualizar
        $ultimaActualizacion = 601;
        $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";
        if (!$con->query($consultaInsert)) {
            // Codigo = 500, mal ahi
            http_response_code(500);
            $json_final = json_encode(["error" => "Internal server error."]);
            echo $json_final;
        } 
    }

    if ($ultimaActualizacion > $since) {
        //echo "EEEE" . $ultimaActualizacion . "Sicne" . $since;
        //Llamada a la API 1 (top games)
        $url = "https://api.twitch.tv/helix/games/top?first=3";
        $headers = [
            "Authorization: Bearer " . gen_token(),
            "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
        ];

        //Configurar opciones de cURL
        $chGames = curl_init();
        curl_setopt($chGames, CURLOPT_URL, $url);
        curl_setopt($chGames, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chGames, CURLOPT_HTTPHEADER, $headers);

        //Ejecutar cURL
        $response = curl_exec($chGames);

        //Obtener el código de estado
        $httpCode = curl_getinfo($chGames, CURLINFO_HTTP_CODE);

        // Verificar el código de estado
        http_response_code($httpCode);
        if ($httpCode == 200) {
            //Procesamos el JSON
            $dataGames = json_decode($response, true);

            foreach ($dataGames["data"] as $game) {
                $gameId = $game["id"];
                $gameName = $game["name"];

                //Llamada a la API 2 (top videos foreach game)
                $url = "https://api.twitch.tv/helix/videos?game_id=" . $gameId . "&sort=views&first=40";
                $headers = [
                    "Authorization: Bearer " . gen_token(),
                    "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
                ];

                //Curl
                //Configurar opciones de cURL
                $chVideos = curl_init();
                curl_setopt($chVideos, CURLOPT_URL, $url);
                curl_setopt($chVideos, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chVideos, CURLOPT_HTTPHEADER, $headers);

                //Ejecutar cURL
                $response = curl_exec($chVideos);

                //Obtener el código de estado
                $httpCode = curl_getinfo($chVideos, CURLINFO_HTTP_CODE);

                if ($httpCode == 200) {
                    //Procesar el JSON
                    $dataVideos = json_decode($response, true);
                    $listaUsers = [];

                    foreach ($dataVideos["data"] as $video) {
                        $userId = $video["user_id"];
                        if (!isset($listaUsers[$userId])) {
                            //Nuevo usuario en la lista
                            //Formato del array: userName, totalVideos, totalViews, mostTitle, mostViews, mostDuration, mostFecha
                            $listaUsers[$video["user_id"]] = ["userName" => $video["user_name"], "totalVideos" => 1, "totalViews" => $video["view_count"],
                                "mostTitle" => $video["title"], "mostViews" => $video["view_count"], "mostDuration" => $video["duration"], "mostFecha" => $video["created_at"]];
                        } else {
                            //El usuario ya ha salido
                            $listaUsers[$video["user_id"]]["totalVideos"]++;
                            $listaUsers[$video["user_id"]]["totalViews"] += $video["view_count"];
                        }
                    }
                    $consultaBorrar = "DELETE FROM ttt";
                    if (!$con->query($consultaBorrar)) {
                        //Codigo 500, error interno
                        echo "fsdafdf 122";
                        $json_final = json_encode(["error" => "Internal server error."]);
                        echo $json_final;
                        exit;
                    }
                    foreach ($listaUsers as $userId => $usuario) {
                        $newUser = [
                            "game_id" => $gameId,
                            "game_name" => $gameName,
                            "user_name" => $usuario["userName"],
                            "total_videos" => $usuario["totalVideos"],
                            "total_views" => $usuario["totalViews"],
                            "most_viewed_title" => $usuario["mostTitle"],
                            "most_viewed_views" => $usuario["mostViews"],
                            "most_viewed_duration" => $usuario["mostDuration"],
                            "most_viewed_created_at" => $usuario["mostFecha"]
                        ];

                        $infoUsers[] = $newUser;

                        //Borrar datos de la bbdd
                        

                        //Insertar datos en la bbdd
                        $consultaInsert = "INSERT INTO ttt (game_id, game_name, user_name, total_videos, total_views,  
                    most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at)
                    VALUES ('$gameId', '$gameName', '{$usuario["userName"]}', '{$usuario["totalVideos"]}', '{$usuario["totalViews"]}', 
                    '{$usuario["mostTitle"]}', '{$usuario["mostViews"]}', '{$usuario["mostDuration"]}', '{$usuario["mostFecha"]}')";

                        // HACE FALTA ACTUALIZAR LA FECHA DE INSERCIÓN

                        if (!$con->query($consultaInsert)) {
                            //Codigo 500, error interno
                            echo " 136 Error MySQL: " . $con->error;
                            $json_final = json_encode(["error" => "Internal server error."]);
                            echo $json_final;
                            exit;
                        }
                        $consultaUpdate = "delete from ttt_fecha";
                        if (!$con->query($consultaUpdate)) {
                            http_response_code(500);
                            $json_final = json_encode(["error" => "Internal server error."]);
                            echo $json_final;
                        } 
                        $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";
                        if (!$con->query($consultaInsert)) {
                            // Codigo = 500, mal ahi
                            http_response_code(500);
                            $json_final = json_encode(["error" => "Internal server error."]);
                            echo $json_final;
                        } 
                    }
                }
            }

            //Generamos JSON de envio
            $jsonFinal = json_encode($infoUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo $jsonFinal;
            
            
        } else {
            $respuesta = ["error" => "Internal server error."];
            echo json_encode($respuesta);
        }
    } else {
        //Leer informacion de la bbdd

        $consultaRAM = "SELECT * FROM ttt";
        $resultado = $con->query($consultaRAM);

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                echo json_encode($fila, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo "\n"; // Salto de línea para mejor legibilidad
               
            }
        }
        
    }
}
?>