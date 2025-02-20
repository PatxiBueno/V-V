<?php

//Cabecera
require_once('conexion.php');

function validarToken($headers){
   
    //Comprobar que la cabecera Authorization existe
    if (isset($headers['Authorization'])){
        //Sustrae de la cabecera el "Bearer" y el token
        list($bearer, $tokenUsuario) = explode(" ", $headers['Authorization'], 2);
        //Comprueba que la cabecera es correcta y que viene con "bearer" en ella 
        if (strcasecmp($bearer, "Bearer") == 0) {
            //Si la cabecera es correcta (Authorization: Bearer ...) comprueba el token
            $con = conexion();
            $consultaTokens = "SELECT fecha_token FROM token WHERE token LIKE  '$tokenUsuario'";
            $resultado = $con->query($consultaTokens);
            
            if ($resultado && $resultado->num_rows > 0) {
                
                //El token es correcto
                //Comprobar si no ha caducado
                $fila = $resultado->fetch_assoc();
                $timestampToken = $fila['fecha_token'];
                //Calculamos que no hayan pasado 3 dias (259200 seundos)
                //echo time() ." fdsafs ". time$timestampToken; $timestamp = strtotime($fecha);
                $tiempoDelToken = time() - strtotime($timestampToken);
                //echo "Toekn time".$tiempoDelToken;
                if ($tiempoDelToken <= 259200) {
                    //El token es valido y no ha expirado
                    return true;
                } else {
                    //El token ha expirado
                    return false;
                }
            } else {
                //El token es incorrecto
                return false;
            }
        } else {
            //La cabecera no tiene el formato correcto (falta bearer)
            return false;
        }
    } else {
        //No existe la cabecera Authorization
        return false;
    }
}

?>
