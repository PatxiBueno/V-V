<?php

//Cabecera
require_once __DIR__ . '/../../bbdd/conexion.php';
header("Content-type: application/json; charset=utf-8");

//Recogida de información
function register($data)
{
// Verificar email
    if (isset($data["email"])) {
        $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);

        // Validar que el email sea correcto
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //Codigo = 200, todo bien
            //Generar nueva key
            $newApiKey = bin2hex(random_bytes(8));

            //Realizar conexion
            $con = conexion();
            //Insertar nueva api_key en la BBDD
            $consultaSelect = "SELECT * FROM usuarios where email like '$email' ";
            $resultado = $con->query($consultaSelect);


            $newApiKeyHasheada = hash("sha256", $newApiKey);

            if ($resultado && $resultado->num_rows > 0) {
                //Caso 1, existia el email
                //$consultaInsert = "INSERT INTO usuarios (api_key) VALUES ('$newApiKeyHasheada')";
                //$consultainsert = "UPDATE usuarios SET api_key = '$newApiKeyHasheada' WHERE email like '$email'";
                $consultaInsert = "DELETE FROM usuarios WHERE email like '$email'";// , "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";

                if ($con->query($consultaInsert)) {
                    $consultaInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";
                } else {
                    return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                }
            } else {
                //Caso 2, no existia el email
                $consultaInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";
            }

            if ($con->query($consultaInsert)) {
                return ['data' => ["api_key" => $newApiKey], 'http_code' => 200];
            } else {
                return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
            }
        } else {
            //Codigo = 400, email invalido
            return ['data' => ["error" => "The email must be a valid email address"], 'http_code' => 400];
        }
    } else {
        //Codigo = 400, email inexistente
        return ['data' => ["error" => "The email is mandatory"], 'http_code' => 400];
    }
}
