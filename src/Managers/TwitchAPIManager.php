<?php

namespace TwitchAnalytics\Managers;

use TwitchAnalytics\ResponseTwitchData;

class TwitchAPIManager
{
    public function curlToTwitchApiForStreamsEndPoint(): ResponseTwitchData
    {
        return $this->curlToTwitch('streams');
    }
    public function curlToTwitchApiForUserEndPoint($userId): ResponseTwitchData
    {
        $urlForUser = "users?id=" . $userId;
        return $this->curlToTwitch($urlForUser);
    }


    public function curlToTwitchApiForEnrichedEndPoint($limit): ResponseTwitchData
    {
        $urlForEnriched = "streams?first=" . $limit;
        return $this->curlToTwitch($urlForEnriched);
    }

    private function curlToTwitch($endPointUrl): ResponseTwitchData
    {
        $url = "https://api.twitch.tv/helix/" . $endPointUrl;
        $headers = [
            "Authorization: Bearer " . $this->generateTwitchToken(),
            "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
        ];
        $curlVariable = curl_init();
        curl_setopt($curlVariable, CURLOPT_URL, $url);
        curl_setopt($curlVariable, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlVariable, CURLOPT_HTTPHEADER, $headers);

        $curlResponse = curl_exec($curlVariable);
        $responseTwitchData = new ResponseTwitchData(curl_getinfo($curlVariable, CURLINFO_HTTP_CODE), $curlResponse);

        curl_close($curlVariable);

        return $responseTwitchData;
    }

    private function generateTwitchToken()
    {
        $clientID = 'client_id=3kvc11lm0hiyfqxs32i127986wbep6&client_secret=uk8rqpk69km2l83dj722t6wowsm7od&grant_type=client_credentials';
        $url = "https://id.twitch.tv/oauth2/token";
        $headers = [
            "Content-Type: application/x-www-form-urlencoded"
        ];

        $curlVariable = curl_init();
        curl_setopt($curlVariable, CURLOPT_URL, $url);
        curl_setopt($curlVariable, CURLOPT_POST, true);
        curl_setopt($curlVariable, CURLOPT_POSTFIELDS, $clientID);
        curl_setopt($curlVariable, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlVariable, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curlVariable);

        curl_close($curlVariable);
        $data = json_decode($response, true);
        return $data['access_token'];
    }
}
