<?php

require_once __DIR__ . '/../../twirch/twitchToken.php';
header("Content-type: application/json; charset=utf-8");
function getUserFromApi($id)
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
    $curlTwitchUserResponse = curl_exec($curlTwitchUser);
    $twitchResponseHttpCode = curl_getinfo($curlTwitchUser, CURLINFO_HTTP_CODE);
    http_response_code($twitchResponseHttpCode);
    if ($twitchResponseHttpCode == 200) {
        $responseData = json_decode($curlTwitchUserResponse, true);
        if ($responseData === null || empty($responseData["data"])) {
            curl_close($curlTwitchUser);
            return ['data' => ["error" => "User not found."], 'http_code' => $twitchResponseHttpCode];
        }
        foreach ($responseData["data"] as $twitchUser) {
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
        return ['data' => $twitchUserData, 'http_code' => $twitchResponseHttpCode];
    } elseif ($twitchResponseHttpCode == 400) {
        curl_close($curlTwitchUser);
        return ['data' => ["error" => "Invalid or missing 'id' parameter."], 'http_code' => $twitchResponseHttpCode];
    } elseif ($twitchResponseHttpCode == 401) {
        curl_close($curlTwitchUser);
        return ['data' => ["error" => "Unauthorized. Twitch access token is invalid or has expired."], 'http_code' => $twitchResponseHttpCode];
    } elseif ($twitchResponseHttpCode == 404) {
        curl_close($curlTwitchUser);
        return ['data' => ["error" => "User not found."], 'http_code' => $twitchResponseHttpCode];
    } else {
        curl_close($curlTwitchUser);
        return ['data' => ["error" => "Internal server error."], 'http_code' => $twitchResponseHttpCode];
    }
}
