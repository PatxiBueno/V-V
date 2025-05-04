<?php

//Cabecera
require_once __DIR__ . '/../../bbdd/conexion.php';
header("Content-Type: application/json; charset=utf-8");

//Recogida de información
function register($data)
{
    if (isset($data["email"])) {
        $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $newApiKey = bin2hex(random_bytes(8));
            $newApiKeyHasheada = hash("sha256", $newApiKey);
            $con = conexion();

            $consultaSelect = "SELECT * FROM usuarios where email like '$email'";
            $resultado = $con->query($consultaSelect);

            if ($resultado && $resultado->num_rows > 0) {
                $consultaInsert = "DELETE FROM usuarios WHERE email like '$email'";

                if ($con->query($consultaInsert)) {
                    $consultaInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Internal server error."]);
                    return;
                }
            } else {
                $consultaInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";
            }

            if ($con->query($consultaInsert)) {
                http_response_code(200);
                echo json_encode(["api_key" => $newApiKey]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Internal server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "The email must be a valid email address"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
    }
}
