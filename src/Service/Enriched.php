<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Managers\TwitchAPIManager;

class Enriched
{
    private Request $request;
    private TwitchAPIManager $apiManager;
    private ResponseTwitchData $responseTwitchData;

    public function __construct($request, $apiManager)
    {
        $this->request = $request;
        $this->apiManager = $apiManager;
    }
    public function getEnriched(): \Illuminate\Http\JsonResponse
    {
        $limit = $this->request->get('limit');
        $response = $this->enriched($limit);
        return response()->json($response['data'], $response['http_code']);
    }

    private function enriched($limit)
    {
        if ($limit < 1 || $limit > 100) {
            return ['data' => ["error" => "Invalid limit parameter"], 'http_code' => 400];
        }

        $this->responseTwitchData = $this->apiManager->curlToTwitchApiForEnrichedEndPoint($limit);
        $httpCodeStreams = $this->responseTwitchData->getHttpResponseCode();

        if ($httpCodeStreams == 401) {
            return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired"], 'http_code' => $httpCodeStreams];
        }

        if ($httpCodeStreams == 500) {
            return ['data' => ["error" => "Internal server error"], 'http_code' => $httpCodeStreams];
        }

        if ($httpCodeStreams == 200) {
            $infoStreamsEnriched = [];
            $dataStreams = json_decode($this->responseTwitchData->getHttpResponseUserData(), true);
            foreach ($dataStreams["data"] as $stream) {
                if ($limit > 0) {
                    $responseUserDataForEnriched = $this->apiManager->curlToTwitchApiForUserEndPoint($stream["user_id"]);
                    $httpCodeUser = $responseUserDataForEnriched->getHttpResponseCode();

                    if ($httpCodeUser != 200) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => $httpCodeUser];
                    }

                    if ($httpCodeUser == 200) {
                        $userDataForEnriched = json_decode($responseUserDataForEnriched->getHttpResponseUserData(), true);

                        $newStreamEnriched = $this->parseTwitchDataToOurFormat($userDataForEnriched, $stream);
                        $infoStreamsEnriched[] = $newStreamEnriched;
                    }
                    $limit--;
                }
            }
            return ['data' => $infoStreamsEnriched, 'http_code' => $httpCodeStreams];
        }
    }

    private function parseTwitchDataToOurFormat($data, $stream): array
    {
        foreach ($data["data"] as $streamer) {
            $user_name = $streamer["login"];
            $display_name = $streamer["display_name"];
            $profile_image_url = $streamer["profile_image_url"];
        }

        $newStreamEnriched = [
            "stream_id" => $stream["id"],
            "user_id" => $stream["user_id"],
            "user_name" => $user_name,
            "viewer_count" => $stream["viewer_count"],
            "title" => $stream["title"],
            "user_display_name" => $display_name,
            "profile_image_url" => $profile_image_url
        ];
        return $newStreamEnriched;
    }
}
