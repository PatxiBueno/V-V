<?php

//Cabecera
header("Content-Type: application/json");

//Recogida de información
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Verificar email
if (isset($data["email"])) {
    $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //Ver si el email es existente
        /*
            Buscar el email en la base de datos
            Si es valida, coger tambien la api_key
            $keyBBDD = alfalfa;
        */

        $keyBBDD = "alfalfa";

        //Comprobar que nos han mandado una api_key
        if (isset($data["api_key"])) {
            $keyUsuario = $data["api_key"];

            //Comprobar si la key es valida
            if ($keyUsuario == $keyBBDD) {
                //Codigo = 200, todo bien
                $newToken = bin2hex(random_bytes(10));
                $json_final = json_encode(["token" => $newToken]);
                echo $json_final;
            } else {
                //Codigo = 400, api_key invalida
                $json_final = json_encode(["error" => "Unauthorized. API access token is invalid."]);
                echo $json_final;
            }

        } else {
            //Codigo = 400, api_key inexistente
            $json_final = json_encode(["error" => "The api_key is mandatory"]);
            echo $json_final;
        }

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
echo "\n";

?>