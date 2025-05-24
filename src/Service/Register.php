<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\ResponseTwitchData;

require_once __DIR__ . '/../../bbdd/conexion.php';

class Register
{
    private Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function registerUser(): \Illuminate\Http\JsonResponse
    {
        $data = $this->request->json()->all();
        $response = $this->register($data);
        return response()->json($response['data'], $response['http_code']);
    }
    private function register($data)
    {
        if (!isset($data["email"])) {
            return ['data' => ["error" => "The email is mandatory"], 'http_code' => 400];
        }
        $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['data' => ["error" => "The email must be a valid email address"], 'http_code' => 400];
        }

        $newApiKey = bin2hex(random_bytes(8));
        $hashedNewApiKey = hash("sha256", $newApiKey);

        $con = conexion();

        $resultado = $this->getUserByEmail($email, $con);

        if (!$resultado || $resultado->num_rows === 0) {
            if (!$this->setUserWithKey($email, $hashedNewApiKey, $con)) {
                return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
            }
        }

        if (!$this->updateUserKey($hashedNewApiKey, $email, $con)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }
        return ['data' => ["api_key" => $newApiKey], 'http_code' => 200];
    }

    public function getUserByEmail(mixed $email, ?\mysqli $con): bool|\mysqli_result
    {
        $querySelect = "SELECT * FROM usuarios where email like '$email' ";
        return $con->query($querySelect);
    }

    public function setUserWithKey(mixed $email, string $newApiKeyHasheada, ?\mysqli $con): bool
    {
        $queryInsert = "INSERT INTO usuarios (email, api_key) VALUES ('$email', '$newApiKeyHasheada')";
        return $con->query($queryInsert);
    }

    public function updateUserKey(string $newApiKeyHasheada, mixed $email, ?\mysqli $con): bool
    {
        $queryUpdate = "UPDATE usuarios SET api_key = '$newApiKeyHasheada' WHERE email = '$email'";
        return $con->query($queryUpdate);
    }
}
