<?php

namespace TwitchAnalytics\Managers;

use mysqli;
use RuntimeException;

class MYSQLDBManager
{
    protected mysqli $connection;

    public function __construct()
    {
        $host = getenv('DB_HOST');
        $database = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $pass = getenv('DB_PASSWORD');
        $port = getenv('DB_PORT');

        $this->connection = new mysqli($host, $user, $pass, $database, (int) $port);

        if ($this->connection->connect_error) {
            throw new RuntimeException("Error de conexión: " . $this->connection->connect_error);
        }
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM usuarios WHERE email = ?");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function insertUserWithHashedApiKey(string $email, string $hashedApiKey): bool
    {
        $stmt = $this->connection->prepare("INSERT INTO usuarios (email, api_key) VALUES (?, ?)");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("ss", $email, $hashedApiKey);

        if (!$stmt->execute()) {
            throw new RuntimeException("Error al ejecutar la consulta: " . $stmt->error);
        }

        return true;
    }

    public function updateUserHashedKey(string $hashedApiKey, string $email): bool
    {
        $stmt = $this->connection->prepare("UPDATE usuarios SET api_key = ? WHERE email = ?");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("ss", $hashedApiKey, $email);

        if (!$stmt->execute()) {
            throw new RuntimeException("Error al ejecutar la consulta: " . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    public function getUserApiKey(string $email): ?array
    {
        $stmt = $this->connection->prepare("SELECT id, api_key FROM usuarios WHERE email = ?");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function getTokenByUserId($userId): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM token WHERE id_usuario = ?");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
        return $result->fetch_assoc();
    }

    public function insertToken($userId, string $token): bool
    {
        $stmt = $this->connection->prepare("INSERT INTO token (id_usuario, token) VALUES (?, ?)");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("is", $userId, $token);
        return $stmt->execute();
    }

    public function updateToken($userId, string $token): bool
    {
        $stmt = $this->connection->prepare("UPDATE token SET token = ?, fecha_token = CURRENT_TIMESTAMP WHERE id_usuario = ?");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("si", $token, $userId);
        return $stmt->execute();
    }

    public function getExpirationDayOfToken(string $userToken): ?array
    {
        $stmt = $this->connection->prepare("SELECT fecha_token FROM token WHERE token = ?");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("s", $userToken);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }
}
