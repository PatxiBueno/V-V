<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\ResponseTwitchData;

require_once __DIR__ . '/../../endPoints/get/streams.php';
class Streams
{
    private Request $request;
    private ResponseTwitchData $responseTwitchData;
    private TwitchAPIManager $twitchAPIManager;
    public function __construct($request, $twitchAPIManager)
    {
        $this->request = $request;
        $this->twitchAPIManager = $twitchAPIManager;
    }
    public function getStreams()
    {
        $response = getStreamsFromApi();
        return response()->json($response['data'], $response['http_code']);
    }

    public function getStreamsFromApi()
    {
        $this->responseTwitchData = $this->twitchAPIManager->curlToTwitchApiForStreamsEndPoint();

        $obtainedHttpCode = $this->responseTwitchData->getHttpResponseCode();

        if ($obtainedHttpCode == 401) {
            return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired."], 'http_code' => 401];
        }
        if ($obtainedHttpCode != 200) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        $responseData = json_decode($this->responseTwitchData->getHttpResponseUserData(), true);
        $twitchUserData = $this->parseTwitchDataToStreamsFormat($responseData["data"]);
        return ['data' => $twitchUserData, 'http_code' => 200];
    }

    private function parseTwitchDataToStreamsFormat(mixed $data): array
    {
        $twitchStreamsData = [];
        foreach ($data["data"] as $stream) {
            $twitchStreamsData = [
                "title" => $stream["title"],
                "user_name" => $stream["user_name"]
            ];
        }
        return $twitchStreamsData;
    }
}
