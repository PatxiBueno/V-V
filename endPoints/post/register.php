<?php

//Cabecera
require_once('../../conexion.php');
header("Content-Type: application/json");

//Recogida de información
$input = file_get_contents("php://input");
$data = json_decode($input, true);

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
            $consultaInsert = "DELETE FROM usuarios WHERE email = '$email'";// , "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";

            if($con->query($consultaInsert)){
                //Codigo = 200, todo correcto
                /*http_response_code(200);
                $json_final = json_encode(["api_key" => $newApiKey]);
                echo $json_final;*/
                $consultaInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";
            } else {
                //Codigo 500, error interno
                //echo "Error en la consulta SELECT: " . $con->error . "<br>";
                http_response_code(500);
                $json_final = json_encode(["error" => "Internal server error."]);
                echo $json_final;
            }

        } else {
            //Caso 2, no existia el email
            $consultaInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email','$newApiKeyHasheada')";
        }

        if($con->query($consultaInsert)){
            //Codigo = 200, todo correcto
            http_response_code(200);
            $json_final = json_encode(["api_key" => $newApiKey]);
            echo $json_final;
        } else {
            //Codigo 500, error interno
            //echo "Error en la consulta SELECT: " . $con->error . "<br>";
            http_response_code(500);
            $json_final = json_encode(["error" => "Internal server error."]);
            echo $json_final;
        }

    } else {
        //Codigo = 400, email invalido
        http_response_code(400);
        $json_final = json_encode(["error" => "The email must be a valid email address"]);
        echo $json_final;
    }
} else {
    //Codigo = 400, email inexistente
    http_response_code(400);
    $json_final = json_encode(["error" => "The email is mandatory"]);
    echo $json_final;
}

?>
