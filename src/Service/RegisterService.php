<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;

class RegisterService
{
    private MYSQLDBManager $dbManager;
    public function __construct($dbManager)
    {
        $this->dbManager = $dbManager;
    }
    public function registerUser($data): array
    {
        return $this->register($data);
    }
    private function register($email)
    {
        $newApiKey = bin2hex(random_bytes(8));
        $hashedNewApiKey = hash("sha256", $newApiKey);

        $user = $this->dbManager->getUserByEmail($email);

        if (!$user && !$this->dbManager->insertUserWithHashedApiKey($email, $hashedNewApiKey)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }
        if (!$this->dbManager->updateUserHashedKey($hashedNewApiKey, $email)) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        return ['data' => ["api_key" => $newApiKey], 'http_code' => 200];
    }
}
