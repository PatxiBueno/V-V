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
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        //Realizar conexion
        $con = conexion();

        //Ver si el email es existente
        $consultaEmail = "SELECT * FROM usuarios WHERE email LIKE '$email' ";
        $resultadoEmail = $con->query($consultaEmail);

  
        if ($resultadoEmail && $resultadoEmail->num_rows > 0) {//si tiene datos, existe el mail, casca aqui
            //Email correcto
            //Guardar la api_key de la BBDD
            $consultaKey = "SELECT id, api_key FROM usuarios WHERE email LIKE  '$email' ";
            $resultadoKey = $con->query($consultaKey);
            $fila = $resultadoKey->fetch_assoc();
            $keyBBDD = $fila['api_key'];
            $idUsuario = $fila['id'];

            //Comprobar que nos han mandado una api_key
            if (isset($data["api_key"])) {
                $keyUsuario = $data["api_key"];

                //Comprobar si la key es valida
                if (hash("sha256", $keyUsuario) == $keyBBDD) {
                    http_response_code(200);
                    $newToken = bin2hex(random_bytes(10));
                    $fechaToken = time();
                    $consultaInsert = "INSERT INTO token (id_usuario, token) VALUES ('$idUsuario', '$newToken')";
                    if($con->query($consultaInsert)){
                        //Codigo = 200, todo correcto
                        http_response_code(200);
                        $json_final = json_encode(["token" => $newToken]);
                        echo $json_final;
                    } else {
                        //Codigo 500, error interno
                        http_response_code(500);
                        $json_final = json_encode(["error" => "Internal server error."]);
                        echo $json_final;
                    }
                   
                } else {
                    //Codigo = 401, api_key invalida
                    http_response_code(401);
                    $json_final = json_encode(["error" => "Unauthorized. API access token is invalid."]);
                    echo $json_final;
                }

            } else {
                //Codigo = 400, api_key inexistente
                http_response_code(400);
                $json_final = json_encode(["error" => "The api_key is mandatory"]);
                echo $json_final;
            }
        } else {
            //Codigo = 400, email no registrado
            http_response_code(400);
            $json_final = json_encode(["error" => "The email must be a valid email address"]);
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
