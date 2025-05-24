<?php

namespace TwitchAnalytics\Service;

use Illuminate\Http\Request;
use TwitchAnalytics\Managers\MYSQLDBManager;

class Token
{
    private const int TOKEN_LENGHT = 10;
    private Request $request;
    private MYSQLDBManager $dbManager;

    public function __construct($request, $dbManager)
    {
        $this->request = $request;
        $this->dbManager = $dbManager;
    }
    public function genToken($data)
    {
        return $this->generarToken($data);
    }

    private function generateToken($data)
    {
        $userData = $this->dbManager->getUserApiKey($email);
        if (!$userData) {
            return ['data' => ["error" => "The email must be a valid email address"], 'http_code' => 400];
        }

        $dbApiKey = $userData['api_key'];
        $dbUserId = $userData['id'];
        $requestApiKey = $data["api_key"];

        if (hash("sha256", $requestApiKey) !== $dbApiKey) {
            return ['data' => ["error" => "Unauthorized. API access token is invalid."], 'http_code' => 401];
        }
        return $this->giveTokenToUser($dbUserId);
    }

    private function giveTokenToUser($userId)
    {
        $newToken = $this->generateRandomToken();
        $dbToken = $this->dbManager->getTokenByUserId($userId);


        $success = $dbToken
            ? $this->dbManager->updateToken($userId, $newToken)
            : $this->dbManager->insertToken($userId, $newToken);

        if (!$success) {
            return ['data' => ["error" => "Internal server error."], 'http_code' => 500];
        }

        return ['data' => ["token" => $newToken], 'http_code' => 200];
    }
    private function generateRandomToken():string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGHT));
    }
}
