<?php

//Cabecera
header("Content-type: application/json; charset=utf-8");
require_once __DIR__ . '/../../bbdd/conexion.php';


//Recogida de información
function generarToken($data)
{
    // Verificar email
    if (isset($data["email"])) {
        $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //Realizar conexion
            $con = conexion();
            //Ver si el email es existente
            $consultaEmail = "SELECT * FROM usuarios WHERE email LIKE '$email' ";
            $resultadoEmail = $con->query($consultaEmail);
            if ($resultadoEmail && $resultadoEmail->num_rows > 0) {
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
                        if (hash("sha256", $keyUsuario) == $keyBBDD) {
                            http_response_code(200);
                            // Generar el nuevo token
                            $newToken = bin2hex(random_bytes(10));
                            // Verificar si ya existe un token para este usuario
                            $consultaToken = "SELECT * FROM token WHERE id_usuario = '$idUsuario'";
                            $resultadoToken = $con->query($consultaToken);
                            if ($resultadoToken && $resultadoToken->num_rows > 0) {
                                // Si ya existe un token, lo actualizamos
                                $consultaUpdate = "UPDATE token SET token = '$newToken', fecha_token = CURRENT_TIMESTAMP WHERE id_usuario = '$idUsuario'";
                                if ($con->query($consultaUpdate)) {
                                    return ['data' => ["token" => $newToken], 'http_code' => 200];
                                } else {
                                    return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                                }
                            } else {
                                $consultaInsert = "INSERT INTO token (id_usuario, token) VALUES ('$idUsuario', '$newToken')";
                                if ($con->query($consultaInsert)) {
                                    return ['data' => ["token" => $newToken], 'http_code' => 200];
                                } else {
                                    return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
                                }
                            }
                        }
                    } else {
                        //Codigo = 401, api_key invalida
                        return ['data' => ["error" => "Unauthorized. API access token is invalid."], 'http_code' => 401];
                    }
                } else {
                    //Codigo = 400, api_key inexistente
                    return ['data' => ["error" => "The api_key is mandatory"], 'http_code' => 400];
                }
            } else {
                //Codigo = 400, email no registrado
                return ['data' => ["error" => "The email must be a valid email address"], 'http_code' => 400];
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
