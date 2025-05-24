<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;

class Register
{
    private Request $request;
    private MYSQLDBManager $dbManager;
    public function __construct($request, $dbManager)
    {
        $this->request = $request;
        $this->dbManager = $dbManager;
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

        $user = $this->dbManager->getUserByEmail($email);

        if (!$user) {
            if (!$this->dbManager->insertUserWithHashedApiKey($email, $hashedNewApiKey)) {
                return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
            }
        }
        if (!$this->dbManager->updateUserHashedKey($hashedNewApiKey, $email)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        return ['data' => ["api_key" => $newApiKey], 'http_code' => 200];
    }
}
