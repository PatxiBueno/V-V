<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Managers\TwitchAPIManager;

require_once __DIR__ . '/../../bbdd/conexion.php';


class TopsOfTheTops
{
    private Request $request;
    private ResponseTwitchData $responseTwitchData;

    private TwitchAPIManager $twitchAPIManager;
    public function __construct($request,$twitchAPIManager)
    {
        $this->request = $request;
        $this->twitchAPIManager = $twitchAPIManager;
    }
    public function getTops()
    {
        $since = $this->request->get('since');
        $response = $this->getTopOfTheTops($since);
        return response()->json($response['data'], $response['http_code']);
    }
    function getTopOfTheTops($since)
    {
        if ($since < 0) {
            return ['data' => ["error" => "Bad request. Invalid or missing parameters."], 'http_code' => 400];
        } elseif ($since > 600) {
            $since = 600;
        }

        $con = conexion();


        $consultaFecha = "SELECT fecha_insercion FROM ttt_fecha";
        $resultadoFecha = $con->query($consultaFecha);

        if ($resultadoFecha && $resultadoFecha->num_rows > 0) {
            $fila = $resultadoFecha->fetch_assoc();
            $ultimaActualizacion = time() - strtotime($fila['fecha_insercion']);
        } else {
            $ultimaActualizacion = 601;
            $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";

            if (!$con->query($consultaInsert)) {
                return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
            }
        }

        if ($ultimaActualizacion <= $since) {
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
        $consultaBorrar = "DELETE FROM ttt";
        if (!$con->query($consultaBorrar)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        $this->responseTwitchData = $this->twitchAPIManager->curlToTwitchApiForTopThreeGames();


        if ($this->responseTwitchData->getHttpResponseCode() != 200) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        $dataGames = json_decode($this->responseTwitchData->getHttpResponseData(), true);
        $infoUsers = [];

        foreach ($dataGames["data"] as $game) {

            $responseOfGame = $this->twitchAPIManager->curlToTwitchApiForGameById($game["id"]);

            if ($responseOfGame->getHttpResponseCode() == 200) {
                $dataVideos = json_decode($responseOfGame->getHttpResponseData(), true);
                $listOfUsers = [];
                foreach ($dataVideos["data"] as $video) {
                    $userId = $video["user_id"];
                    if (!isset($listOfUsers[$userId])) {

                        $listOfUsers[$video["user_id"]] = [
                            "userName" => $video["user_name"],
                            "totalVideos" => 1,
                            "totalViews" => $video["view_count"],
                            "mostTitle" => $video["title"],
                            "mostViews" => $video["view_count"],
                            "mostDuration" => $video["duration"],
                            "mostDate" => date('Y-m-d H:i:s', strtotime($video["created_at"]))
                        ];
                    } else {
                        $listOfUsers[$video["user_id"]]["totalVideos"]++;
                        $listOfUsers[$video["user_id"]]["totalViews"] += $video["view_count"];
                    }
                }

                foreach ($listOfUsers as $userId => $usuario) {
                    $newUser = [
                        "game_id" => $game["id"],
                        "game_name" => $game["name"],
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
                        $game["id"],
                        $game["name"],
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
        return ['data' => $infoUsers, 'http_code' => 200];
    }
}
