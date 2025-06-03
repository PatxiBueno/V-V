<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Managers\TwitchAPIManager;

class EnrichedService
{
    private TwitchAPIManager $apiManager;
    private ResponseTwitchData $responseTwitchData;

    public function __construct($apiManager)
    {
        $this->apiManager = $apiManager;
    }
    public function getEnriched($limit): array
    {

        return $this->enriched($limit);
    }

    private function enriched($limit)
    {

        $this->responseTwitchData = $this->apiManager->curlToTwitchApiForEnrichedEndPoint($limit);
        $httpCodeStreams = $this->responseTwitchData->getHttpResponseCode();

        if ($httpCodeStreams == 401) {
            return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired"], 'http_code' => 401];
        }

        if ($httpCodeStreams == 500) {
            return ['data' => ["error" => "Internal server error"], 'http_code' => 500];
        }

        if ($httpCodeStreams == 200) {
            $enrichedInfo = [];
            $dataStreams = json_decode($this->responseTwitchData->getHttpResponseData(), true);

            foreach ($dataStreams["data"] as $stream) {
                if ($limit > 0) {
                    $userForEnriched = $this->apiManager->curlToTwitchApiForUserEndPoint($stream["user_id"]);
                    $httpCodeUser = $userForEnriched->getHttpResponseCode();

                    if ($httpCodeUser != 200) {
                        return ['data' => ["error" => "Internal server error."], 'http_code' => $httpCodeUser];
                    }

                    if ($httpCodeUser == 200) {
                        $userDataForEnriched = json_decode($userForEnriched->getHttpResponseData(), true);

                        $enrichedStreamer = $this->parseTwitchDataToOurFormat($userDataForEnriched, $stream);
                        $enrichedInfo[] = $enrichedStreamer;
                    }
                    $limit--;
                }
            }
            return ['data' => $enrichedInfo, 'http_code' => $httpCodeStreams];
        }
    }

    private function parseTwitchDataToOurFormat($data, $stream): array
    {
        foreach ($data["data"] as $streamer) {
            $user_name = $streamer["login"];
            $display_name = $streamer["display_name"];
            $profile_image_url = $streamer["profile_image_url"];
        }

        $enrichedStreamer = [
            "stream_id" => $stream["id"],
            "user_id" => $stream["user_id"],
            "user_name" => $user_name,
            "viewer_count" => $stream["viewer_count"],
            "title" => $stream["title"],
            "user_display_name" => $display_name,
            "profile_image_url" => $profile_image_url
        ];
        return $enrichedStreamer;
    }
}
