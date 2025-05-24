<?php

namespace TwitchAnalytics\Service;

require_once __DIR__ . '/../../bbdd/conexion.php';
use Illuminate\Http\Request;

class Token
{
    private ?\mysqli $conexion;

    public function __construct()
    {
        $this->conexion = conexion();
    }
    public function genToken($data)
    {
        return $this->generarToken($data);
    }

    private function generarToken($data)
    {
        if (!isset($data["email"])) {
            return ['data' => ["error" => "The email is mandatory"], 'http_code' => 400];
        }

        $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['data' => ["error" => "The email must be a valid email address"], 'http_code' => 400];
        }

        $emailResult = $this->queryEmail($email);

        if (!$emailResult || $emailResult->num_rows == 0) {
            return ['data' => ["error" => "The email must be a valid email address"], 'http_code' => 400];
        }

        $resultadoKey = $this->apikeyQuery($email);
        $userInfo = $resultadoKey->fetch_assoc();
        $keyBBDD = $userInfo['api_key'];
        $idUsuario = $userInfo['id'];

        $keyUsuario = $data["api_key"];
        if (hash("sha256", $keyUsuario) != $keyBBDD) {
            return ['data' => ["error" => "Unauthorized. API access token is invalid."], 'http_code' => 401];
        }
        return $this->giveTokenToUser($idUsuario);
    }

    private function giveTokenToUser($idUsuario)
    {
        $newToken = $this->generateToken();
        $resultadoToken = $this->queryToken($idUsuario);

        if (!$resultadoToken || $resultadoToken->num_rows == 0) {
            if (!$this->insertToken($idUsuario, $newToken)) {
                return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
            }
            return ['data' => ["token" => $newToken], 'http_code' => 200];
        }

        if (!$this->queryUpdate($idUsuario, $newToken)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }
        return ['data' => ["token" => $newToken], 'http_code' => 200];
    }

    private function queryEmail($email)
    {
        $query = "SELECT * FROM usuarios WHERE email LIKE '$email' ";
        return $this->conexion->query($query);
    }

    private function insertToken($idUsuario, $newToken)
    {
        $query = "INSERT INTO token (id_usuario, token) VALUES ('$idUsuario', '$newToken')";
        return $this->conexion->query($query);
    }

    private function queryUpdate($idUsuario, $newToken)
    {
        $query = "UPDATE token SET token = '$newToken', fecha_token = CURRENT_TIMESTAMP WHERE id_usuario = '$idUsuario'";
        return $this->conexion->query($query);
    }

    private function queryToken($idUsuario)
    {
        $query = "SELECT * FROM token WHERE id_usuario = '$idUsuario'";
        return $this->conexion->query($query);
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(10));
    }

    private function apikeyQuery($email)
    {
        $query = "SELECT id, api_key FROM usuarios WHERE email LIKE  '$email' ";
         return $this->conexion->query($query);
    }
}
