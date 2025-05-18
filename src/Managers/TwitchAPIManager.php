<?php

namespace TwitchAnalytics\Managers;

use TwitchAnalytics\ResponseTwitchData;

require_once __DIR__ . '/../../twirch/twitchToken.php';
class TwitchAPIManager
{

    public function curlToTwitchApiForUserEndPoint($id): ResponseTwitchData
    {
        $urlForUser = "users?id=" . $id;
        return $this->curlToTwitch($urlForUser);

    }
    private function curlToTwitch($endPointUrl): ResponseTwitchData
    {
        $url = "https://api.twitch.tv/helix/" . $endPointUrl;
        $headers = [
            "Authorization: Bearer " . gen_token(),
            "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
        ];
        $curlTwitchUser = curl_init();
        curl_setopt($curlTwitchUser, CURLOPT_URL, $url);
        curl_setopt($curlTwitchUser, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlTwitchUser, CURLOPT_HTTPHEADER, $headers);

        $curlResponse = curl_exec($curlTwitchUser);
        $responseTwitchData  = new ResponseTwitchData(curl_getinfo($curlTwitchUser, CURLINFO_HTTP_CODE),$curlResponse);

        curl_close($curlTwitchUser);

        return $responseTwitchData;
    }
}
