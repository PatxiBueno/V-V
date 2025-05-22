<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Managers\TwitchAPIManager;

class User
{
    private Request $request;
    private ResponseTwitchData $responseTwitchData;
    private TwitchAPIManager $twitchAPIManager;
    public function __construct($request, $twitchAPIManager)
    {
        $this->request = $request;
        $this->twitchAPIManager = $twitchAPIManager;
    }
    public function getUser(): \Illuminate\Http\JsonResponse
    {
        $idUser = $this->request->get('id');//no hace falta validarlo, ya que twitch no da los cogigos de error que no interesa
        $response = $this->getUserFromApi($idUser);
        return response()->json($response['data'], $response['http_code']);
    }

    private function getUserFromApi($idUser): array
    {
        $this->responseTwitchData = $this->twitchAPIManager->curlToTwitchApiForUserEndPoint($idUser);

        $httpResponseCode = $this->responseTwitchData->getHttpResponseCode();
        if ($httpResponseCode == 400) {
            return ['data' => ["error" => "Invalid or missing 'id' parameter."], 'http_code' => 400];
        }
        if ($httpResponseCode == 401) {
            return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired."], 'http_code' => 401];
        }
        if ($httpResponseCode == 404) {
            return ['data' => ["error" => "User not found."], 'http_code' => 404];
        }
        if ($httpResponseCode != 200) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        $responseData = json_decode($this->responseTwitchData->getHttpResponseData(), true);
        if ($responseData === null || empty($responseData["data"])) {
            return ['data' => ["error" => "User not found."], 'http_code' => 404];
        }
        $twitchUserData = $this->parseTwitchDataToUsersFormat($responseData["data"]);
        return ['data' => $twitchUserData, 'http_code' => 200];
    }


    private function parseTwitchDataToUsersFormat($data): array
    {
        foreach ($data as $twitchUser) {
            $twitchUserData = [
                "id" => $twitchUser["id"],
                "login" => $twitchUser["login"],
                "display_name" => $twitchUser["display_name"],
                "type" => $twitchUser["type"],
                "broadcaster_type" => $twitchUser["broadcaster_type"],
                "description" => $twitchUser["description"],
                "profile_image_url" => $twitchUser["profile_image_url"],
                "offline_image_url" => $twitchUser["offline_image_url"],
                "view_count" => $twitchUser["view_count"],
                "created_at" => $twitchUser["created_at"]
            ];
        }
        return $twitchUserData;
    }
}
