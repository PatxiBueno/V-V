<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;

class Streams
{
    private Request $request;
    private TwitchAPIManager $twitchAPIManager;
    public function __construct($request, $twitchAPIManager)
    {
        $this->request = $request;
        $this->twitchAPIManager = $twitchAPIManager;
    }
    public function getStreams(): \Illuminate\Http\JsonResponse
    {
        $response = $this->getStreamsFromApi();
        return response()->json($response['data'], $response['http_code']);
    }

    public function getStreamsFromApi(): array
    {
        $responseTwitchData = $this->twitchAPIManager->curlToTwitchApiForStreamsEndPoint();

        $obtainedHttpCode = $responseTwitchData->getHttpResponseCode();

        if ($obtainedHttpCode == 401) {
            return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired."], 'http_code' => 401];
        }
        if ($obtainedHttpCode != 200) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        $responseData = json_decode($responseTwitchData->getHttpResponseUserData(), true);
        $twitchUserData = $this->parseTwitchDataToStreamsFormat($responseData["data"]);
        return ['data' => $twitchUserData, 'http_code' => 200];
    }

    private function parseTwitchDataToStreamsFormat(mixed $data): array
    {
        $twitchStreamsData = [];
        foreach ($data as $stream) {
            $newStreamData = [
                "title" => $stream["title"],
                "user_name" => $stream["user_name"]
            ];
            $twitchStreamsData[] = $newStreamData;
        }
        return $twitchStreamsData;
    }
}
