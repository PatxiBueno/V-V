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
    public function __construct($request, $twitchAPIManager)
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
    private function getTopOfTheTops($since)
    {
        if ($since < 0) {
            return ['data' => ["error" => "Bad request. Invalid or missing parameters."], 'http_code' => 400];
        } elseif ($since > 600) {
            $since = 600;
        }

        $con = conexion();


        $resultadoFecha = $this->queryDate($con);

        if ($resultadoFecha && $resultadoFecha->num_rows > 0) {
            $fila = $resultadoFecha->fetch_assoc();
            $ultimaActualizacion = time() - strtotime($fila['fecha_insercion']);
        } else {
            $ultimaActualizacion = 601;

            if (!$this->queryInsertDate($con)) {
                return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
            }
        }

        if ($ultimaActualizacion <= $since) {
            $resultado = $this->getTopsOfTheTopsData($con);

            if ($resultado->num_rows > 0) {
                $result = [];
                while ($fila = $resultado->fetch_assoc()) {
                    $result[] = $fila;
                }
                return ['data' => $result, 'http_code' => 200];
            }
        }
        if (!$this->queryDeleteCache($con)) {
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

                    if (!$this->insertDataIntoCache($con, $game, $usuario)) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                    }


                    if (!$this->deleteDate($con)) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                    }

                    if (!$this->queryInsertDate($con)) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                    }
                }
            }
        }
        return ['data' => $infoUsers, 'http_code' => 200];
    }


    private function queryDate(?\mysqli $con): bool|\mysqli_result
    {
        $consultaFecha = "SELECT fecha_insercion FROM ttt_fecha";
        return $con->query($consultaFecha);
    }


    private function queryInsertDate(?\mysqli $con): bool
    {
        $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";
        return $con->query($consultaInsert);
    }


    private function getTopsOfTheTopsData(?\mysqli $con): bool|\mysqli_result
    {
        $queryCache = "SELECT * FROM ttt";
        return  $con->query($queryCache);
    }


    private function queryDeleteCache(?\mysqli $con): string
    {
        $consultaBorrar = "DELETE FROM ttt";
        return $con->query($consultaBorrar);
    }

    private function deleteDate(?\mysqli $con): string
    {
        $consultaUpdate = "delete from ttt_fecha";
        return $con->query($consultaUpdate);
    }
    private function insertDataIntoCache(?\mysqli $con, $game, $usuario): bool
    {
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
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
