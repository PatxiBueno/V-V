<?php

namespace TwitchAnalytics\Service;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Managers\TwitchAPIManager;
use function PHPUnit\Framework\isEmpty;

class TopsOfTheTops
{
    private ResponseTwitchData $responseTwitchData;
    private MYSQLDBManager $dbManager;
    private TwitchAPIManager $twitchAPIManager;
    public function __construct($twitchAPIManager, $dbManager)
    {
        $this->twitchAPIManager = $twitchAPIManager;
        $this->dbManager = $dbManager;
    }
    public function getTops($since)
    {
        $insertTime = $this->dbManager->getCacheInsertTime();

        if ($insertTime) {
            $dbSince = time() - strtotime($insertTime['fecha_insercion']);
            if ($dbSince <= $since) {
                return $this->getTopOfTheTopsCache();
            }
        }
        return $this->getTopOfTheTops();
    }
    private function getTopOfTheTops()
    {
        if (!$this->dbManager->cleanTopOfTheTopsCache()) {
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

                    if (
                        !$this->dbManager->insertTopsCache($game, $usuario) ||
                        !$this->dbManager->deleteTopsDate() ||
                        !$this->dbManager->insertTopsDate()
                    ) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                    }
                }
            }
        }
        return ['data' => $infoUsers, 'http_code' => 200];
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
            "game_id" => (string)$game["id"],
            "game_name" => (string)$game["name"],
            "user_name" => (string)$usuario["userName"],
            "total_videos" => (string)$usuario["totalVideos"],
            "total_views" => (string)$usuario["totalViews"],
            "most_viewed_title" => (string)$usuario["mostTitle"],
            "most_viewed_views" => (string)$usuario["mostViews"],
            "most_viewed_duration" => (string)$usuario["mostDuration"],
            "most_viewed_created_at" => (string)$usuario["mostDate"]
        ];
        return $newStreamer;
    }

    private function getTopOfTheTopsCache(): array
    {
        $cacheData = $this->dbManager->getTopsCacheData();
        if (!empty($cacheData)) {
            $normalizedData = $this->normalizeTopOfTheTopsTypes($cacheData);
            return ['data' => $normalizedData, 'http_code' => 200];
        }
        return ['data' => [], 'http_code' => 204];
    }

    private function normalizeTopOfTheTopsTypes(array $items): array
    {
        return array_map(function ($item) {
            return array_map('strval', $item);
        }, $items);
    }
}
