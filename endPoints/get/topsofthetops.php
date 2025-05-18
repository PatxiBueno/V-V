<?php

//Cabeceras
require_once __DIR__ . '/../../bbdd/conexion.php';
require_once __DIR__ . '/../../twirch/twitchToken.php';
require_once __DIR__ . '/../../autenticacion.php';
header("Content-type: application/json; charset=utf-8");

function getTopOfTheTops($since)
{
    if ($since < 0) {
        return ['data' => ["error" => "Bad request. Invalid or missing parameters."], 'http_code' => 400];
    } elseif ($since > 600) {
        $since = 600;
    }

    $con = conexion();

//Ver si han pasado menos de 10 mins
    $consultaFecha = "SELECT fecha_insercion FROM ttt_fecha";
    $resultadoFecha = $con->query($consultaFecha);
    if ($resultadoFecha && $resultadoFecha->num_rows > 0) {
        $fila = $resultadoFecha->fetch_assoc();
        $ultimaActualizacion = time() - strtotime($fila['fecha_insercion']);
    } else {
        //No existe fecha guardada, hay que actualizar
        $ultimaActualizacion = 601;
        $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";
        if (!$con->query($consultaInsert)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }
    }

    if ($ultimaActualizacion > $since) {
        $consultaBorrar = "DELETE FROM ttt";
        if (!$con->query($consultaBorrar)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }
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
            $infoUsers = [];
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
                            //Formato del array: userName, totalVideos, totalViews, mostTitle, mostViews, mostDuration, mostDate
                            $listaUsers[$video["user_id"]] = [
                                "userName" => $video["user_name"],
                                "totalVideos" => 1,
                                "totalViews" => $video["view_count"],
                                "mostTitle" => $video["title"],
                                "mostViews" => $video["view_count"],
                                "mostDuration" => $video["duration"],
                                "mostDate" => date('Y-m-d H:i:s', strtotime($video["created_at"]))
                            ];
                        } else {
                            $listaUsers[$video["user_id"]]["totalVideos"]++;
                            $listaUsers[$video["user_id"]]["totalViews"] += $video["view_count"];
                        }
                    }

                    foreach ($listaUsers as $userId => $usuario) {
                        $newUser = [
                            "game_id" => $gameId,
                            "game_name" => $gameName,
                            "user_name" => $usuario["userName"],
                            "total_videos" => $usuario["totalVideos"] . '',
                            "total_views" => $usuario["totalViews"] . '',
                            "most_viewed_title" => $usuario["mostTitle"],
                            "most_viewed_views" => $usuario["mostViews"] . '',
                            "most_viewed_duration" => $usuario["mostDuration"],
                            "most_viewed_created_at" => $usuario["mostDate"]
                        ];

                        $infoUsers[] = $newUser;

                        $stmt = $con->prepare("INSERT INTO ttt 
                        (game_id, game_name, user_name, total_videos, total_views,  
                         most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                        $stmt->bind_param(
                            "sssiissss",
                            $gameId,
                            $gameName,
                            $usuario["userName"],
                            $usuario["totalVideos"],
                            $usuario["totalViews"],
                            $usuario["mostTitle"],
                            $usuario["mostViews"],
                            $usuario["mostDuration"],
                            $usuario["mostDate"]
                        );

                        if (!$stmt->execute()) {
                            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                        }
                        $stmt->close();
                        $consultaUpdate = "delete from ttt_fecha";
                        if (!$con->query($consultaUpdate)) {
                            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                        }
                        $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";
                        if (!$con->query($consultaInsert)) {
                            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                        }
                    }
                }
            }
            return ['data' => $infoUsers, 'http_code' => $httpCode];
        } else {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }
    } else {
        $consultaRAM = "SELECT * FROM ttt";
        $resultado = $con->query($consultaRAM);

        if ($resultado->num_rows > 0) {
            $result = [];
            while ($fila = $resultado->fetch_assoc()) {
                $result[] = $fila;
            }
            return ['data' => $result, 'http_code' => 200];
        }
    }
}
