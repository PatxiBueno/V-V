<?php
function gen_token(){
    $url = "https://id.twitch.tv/oauth2/token";
    $headers = [
        "Content-Type: application/x-www-form-urlencoded"
    ];

    //Configurar opciones de cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=3kvc11lm0hiyfqxs32i127986wbep6&client_secret=uk8rqpk69km2l83dj722t6wowsm7od&grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //Ejecutar cURL
    $response = curl_exec($ch);

    //Obtener el c  digo de estado
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['access_token'];

}
?>