<?php

//Cabecera
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

        //Insertar nueva api_key en la BBDD
        /*
            Aqui codigo para insertar nueva key
        */

        //Empaquetar y enviar json
        $json_final = json_encode(["api_key" => $newApiKey]);
        echo $json_final;

    } else {
        //Codigo = 400, email invalido
        $json_final = json_encode(["error" => "The email must be a valid email address"]);
        echo $json_final;
    }
} else {
    //Codigo = 400, email inexistente
    $json_final = json_encode(["error" => "The email is mandatory"]);
    echo $json_final;
}

//El codigo 500 no se como hacerlo la verdad

?>