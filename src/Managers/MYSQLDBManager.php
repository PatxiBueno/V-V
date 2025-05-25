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
        $statement = $this->connection->prepare("SELECT * FROM usuarios WHERE email = ?");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("s", $email);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function insertUserWithHashedApiKey(string $email, string $hashedApiKey): bool
    {
        $statement = $this->connection->prepare("INSERT INTO usuarios (email, api_key) VALUES (?, ?)");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("ss", $email, $hashedApiKey);

        if (!$statement->execute()) {
            throw new RuntimeException("Error al ejecutar la consulta: " . $statement->error);
        }

        return true;
    }

    public function updateUserHashedKey(string $hashedApiKey, string $email): bool
    {
        $statement = $this->connection->prepare("UPDATE usuarios SET api_key = ? WHERE email = ?");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("ss", $hashedApiKey, $email);

        if (!$statement->execute()) {
            throw new RuntimeException("Error al ejecutar la consulta: " . $statement->error);
        }

        return $statement->affected_rows > 0;
    }

    public function getUserApiKey(string $email): ?array
    {
        $statement = $this->connection->prepare("SELECT id, api_key FROM usuarios WHERE email = ?");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("s", $email);
        $statement->execute();

        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function getTokenByUserId($userId): ?array
    {
        $statement = $this->connection->prepare("SELECT * FROM token WHERE id_usuario = ?");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("i", $userId);
        $statement->execute();

        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
        return $result->fetch_assoc();
    }

    public function insertToken($userId, string $token): bool
    {
        $statement = $this->connection->prepare("INSERT INTO token (id_usuario, token) VALUES (?, ?)");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("is", $userId, $token);
        return $statement->execute();
    }

    public function updateToken($userId, string $token): bool
    {
        $statement = $this->connection->prepare("UPDATE token SET token = ?, fecha_token = CURRENT_TIMESTAMP WHERE id_usuario = ?");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("si", $token, $userId);
        return $statement->execute();
    }

    public function getExpirationDateOfOurToken(string $userToken): ?array
    {
        $statement = $this->connection->prepare("SELECT fecha_token FROM token WHERE token = ?");
        if (!$statement) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $statement->bind_param("s", $userToken);
        $statement->execute();

        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }
}
