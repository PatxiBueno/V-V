<?php
// Incluir el archivo de conexión
include('conexion.php');
	$con = conexion();

	$consulta = "describe usuarios";
	if ($resultado = $con->query($consulta)) {
		print_r( $resultado );
	} else {
		return -1;
	}
?>
