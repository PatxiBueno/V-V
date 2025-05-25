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

    public function getCacheInsertTime(): false|array|null
    {
        $stmt = $this->connection->prepare("SELECT fecha_insercion FROM ttt_fecha ORDER BY fecha_insercion DESC LIMIT 1");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function cleanTopOfTheTopsCache(): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM ttt");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }
        $result = $stmt->execute();
        if (!$result) {
            throw new RuntimeException("Error executing the query: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }

    public function insertTopsCache(array $game, array $usuario): bool
    {
        $stmt = $this->connection->prepare("INSERT INTO ttt 
        (game_id, game_name, user_name, total_videos, total_views,  
         most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }
        $stmt->bind_param(
            "sssiissss",
            $game["id"],
            $game["name"],
            $usuario["userName"],
            $usuario["totalVideos"],
            $usuario["totalViews"],
            $usuario["mostTitle"],
            $usuario["mostViews"],
            $usuario["mostDuration"],
            $usuario["mostDate"]
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteTopsDate(): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM ttt_fecha");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function insertTopsDate(): bool
    {
        $stmt = $this->connection->prepare("INSERT INTO ttt_fecha (fecha_insercion) VALUES (CURRENT_TIMESTAMP)");
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getTopsCacheData(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM ttt");

        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->connection->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();

        return $data;
    }
}
