<?php

function conexion()
{
    $host = getenv('DB_HOST');
    $bd = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');
    $port = getenv('DB_PORT');

    $con = mysqli_connect($host, $user, $password, $bd, $port);

    if (!$con) {
        echo "Error de conexión de base de datos <br>";
        echo "Error número: " . mysqli_connect_errno();
        echo "Texto error: " . mysqli_connect_error();
        exit;
    }
    return $con;
}
