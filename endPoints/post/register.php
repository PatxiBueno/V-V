<?php

require_once __DIR__ . '/../../bbdd/conexion.php';
header("Content-Type: application/json");

function register($data)
{
    // Verificar que el campo email exista
    if (!isset($data["email"])) {
        return response()->json(["error" => "The email is mandatory"], 400);
    }

    $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);

    // Validar el email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return response()->json(["error" => "The email must be a valid email address"], 400);
    }

    // Generar API Key y hashearla
    $newApiKey = bin2hex(random_bytes(8));
    $newApiKeyHashed = hash("sha256", $newApiKey);

    // Conectar a la base de datos
    $con = conexion();

    // Verificar si ya existe ese email
    $stmt = $con->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        // Si ya existe, eliminarlo
        $stmt = $con->prepare("DELETE FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            return response()->json(["error" => "Internal server error deleting user."], 500);
        }
    }

    // Insertar nuevo usuario
    $stmt = $con->prepare("INSERT INTO usuarios (email, api_key) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $newApiKeyHashed);

    if ($stmt->execute()) {
        return response()->json(["api_key" => $newApiKey], 200);
    } else {
        return response()->json(["error" => "Internal server error inserting user."], 500);
    }
}
