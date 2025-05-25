<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Managers\TwitchAPIManager;

require_once __DIR__ . '/../../bbdd/conexion.php';


class TopsOfTheTops
{
    private ResponseTwitchData $responseTwitchData;

    private TwitchAPIManager $twitchAPIManager;
    private ?\mysqli $conexion;
    public function __construct($twitchAPIManager)
    {
        $this->twitchAPIManager = $twitchAPIManager;
        $this->conexion = conexion();
    }
    public function getTops($since)
    {
        $resultDate = $this->queryDate();

        if ($resultDate && $resultDate->num_rows > 0) {
            $fila = $resultDate->fetch_assoc();
            $ultimaActualizacion = time() - strtotime($fila['fecha_insercion']);

            if ($ultimaActualizacion <= $since) {
                return $this->getTopOfTheTopsCache();
            }
        }
        return $this->getTopOfTheTops();
    }
    private function getTopOfTheTops()
    {
        if (!$this->queryDeleteCache()) {
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
                $listOfUsers = $this->parseVideoData($responseOfGame);

                foreach ($listOfUsers as $usuario) {
                    $infoUsers[] = $this->parseStreamerData($game, $usuario);

                    if (!$this->insertDataIntoCache($game, $usuario) || !$this->deleteDate() || !$this->queryInsertDate()) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                    }
                }
            }
        }
        return ['data' => $infoUsers, 'http_code' => 200];
    }


    private function queryDate(): bool|\mysqli_result
    {
        $consultaFecha = "SELECT fecha_insercion FROM ttt_fecha";
        return $this->conexion->query($consultaFecha);
    }


    private function queryInsertDate(): bool
    {
        $consultaInsert = "INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)";
        return $this->conexion->query($consultaInsert);
    }


    private function getTopsOfTheTopsData(): bool|\mysqli_result
    {
        $queryCache = "SELECT * FROM ttt";
        return  $this->conexion->query($queryCache);
    }


    private function queryDeleteCache(): string
    {
        $consultaBorrar = "DELETE FROM ttt";
        return $this->conexion->query($consultaBorrar);
    }

    private function deleteDate(): string
    {
        $consultaUpdate = "delete from ttt_fecha";
        return $this->conexion->query($consultaUpdate);
    }
    private function insertDataIntoCache($game, $usuario): bool
    {
        $stmt = $this->conexion->prepare("INSERT INTO ttt 
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


    public function parseDataFromCache(\mysqli_result|bool $resultado): array
    {
        $result = [];
        while ($fila = $resultado->fetch_assoc()) {
            $result[] = $fila;
        }
        return $result;
    }


    public function parseVideoData(ResponseTwitchData $responseOfGame): array
    {
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
            }
            if (isset($listOfUsers[$userId])) {
                $listOfUsers[$video["user_id"]]["totalVideos"]++;
                $listOfUsers[$video["user_id"]]["totalViews"] += $video["view_count"];
            }
        }
        return $listOfUsers;
    }

    public function parseStreamerData(mixed $game, mixed $usuario): array
    {
        $newStreamer = [
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
        return $newStreamer;
    }

    private function getTopOfTheTopsCache()
    {
        $topsOfTheTopsData = $this->getTopsOfTheTopsData();

        if ($topsOfTheTopsData->num_rows > 0) {
            $result = $this->parseDataFromCache($topsOfTheTopsData);
            return ['data' => $result, 'http_code' => 200];
        }
    }
}
