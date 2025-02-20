<?php
require_once("token.php");

function user($id){
    //Caso 1: GET /analytics/user?id=1234
    //Configurar llamada a la API
    $url = "https://api.twitch.tv/helix/users?id=" . $id_usuario;
    $headers = [
        "Authorization: Bearer " . gen_token() ,
        "Client-Id: 3kvc11lm0hiyfqxs32i127986wbep6"
    ];

    //Configurar opciones de cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //Ejecutar cURL
    $response = curl_exec($ch);

    //Obtener el código de estado
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Verificar el código de estado
    http_response_code($httpCode);
    if ($httpCode == 200) {
        //Procesamos el JSON
        $data = json_decode($response, true);
        if($data === null || empty($data["data"])){
            http_response_code(404);
            $respuesta = ["error" => "User not found."];
            echo json_encode($respuesta);

            curl_close($ch);
            exit;
        }
        foreach ($data["data"] as $streamer) {
            $infoStreamer = [
                "id" => $streamer["id"],
                "login" => $streamer["login"],
                "display_name" => $streamer["display_name"],
                "type" => $streamer["type"],
                "broadcaster_type" => $streamer["broadcaster_type"],
                "description" => $streamer["description"],
                "profile_image_url" => $streamer["profile_image_url"],
                "offline_image_url" => $streamer["offline_image_url"],
                "view_count" => $streamer["view_count"],
                "created_at" => $streamer["created_at"]
            ];

        }
        //Generamos JSON de envio
        $jsonFinal = json_encode($infoStreamer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        echo $jsonFinal;
    } elseif ($httpCode == 400){
        $respuesta = ["error" => "Invalid or missing 'id' parameter."];
        echo json_encode($respuesta);
    } elseif ($httpCode == 401){
        $respuesta = ["error" => "Unauthorized. Twitch access token is invalid or has expired."];
        echo json_encode($respuesta);
    } elseif ($httpCode == 404){
        $respuesta = ["error" => "User not found."];
        echo json_encode($respuesta);
    } elseif ($httpCode == 500){
        $respuesta = ["error" => "Internal server error."];
        echo json_encode($respuesta);
    } else {
        echo "⚠️ Código de error: $httpCode\n";
    }

        curl_close($ch);
}

?>
