<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use PHPUnit\Util\Json;

require_once __DIR__ . '/../../twirch/twitchToken.php';


class User
{
    private Request $request;
    private int $httpResponseCode;

    private string $httpResponseUserData;
    public function __construct($request)
    {
        $this->request = $request;
    }
    public function getUser(): \Illuminate\Http\JsonResponse
    {
        $idUser = $this->request->get('id');//no hace falta validarlo, ya que twitch no da los cogigos de error que no interesa
        $response = $this->getUserFromApi($idUser);
        return response()->json($response['data'], $response['http_code']);
    }

    function getUserFromApi($idUser): array
    {
        $this->curlToTwitchApiForUserEndPoint($idUser);

        http_response_code($this->httpResponseCode);

        if ($this->httpResponseCode == 400) {
            return ['data' => ["error" => "Invalid or missing 'id' parameter."], 'http_code' => $this->httpResponseCode];
        }
        if ($this->httpResponseCode == 401) {
            return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired."], 'http_code' => $this->httpResponseCode];
        }
        if ($this->httpResponseCode == 404) {
            return ['data' => ["error" => "User not found."], 'http_code' => $this->httpResponseCode];
        }
        if ($this->httpResponseCode != 200) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => $this->httpResponseCode];//seria meter 500
        }

        $responseData = json_decode($this->httpResponseUserData, true);
        if ($responseData === null || empty($responseData["data"])) {
            return ['data' => ["error" => "User not found."], 'http_code' => 404];
        }
        $twitchUserData = $this->parseTwitchDataToOurFormat($responseData["data"]);
        return ['data' => $twitchUserData, 'http_code' => $this->httpResponseCode];
    }


    public function curlToTwitchApiForUserEndPoint($id): void
    {
        $url = "https://api.twitch.tv/helix/users?id=" . $id;
        $headers = [
            "Authorization: Bearer " . gen_token(),
            "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
        ];
        $curlTwitchUser = curl_init();
        curl_setopt($curlTwitchUser, CURLOPT_URL, $url);
        curl_setopt($curlTwitchUser, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlTwitchUser, CURLOPT_HTTPHEADER, $headers);


        $this->httpResponseUserData = curl_exec($curlTwitchUser);
        $this->httpResponseCode = curl_getinfo($curlTwitchUser, CURLINFO_HTTP_CODE);


        curl_close($curlTwitchUser);
    }


    public function parseTwitchDataToOurFormat($data): array
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
