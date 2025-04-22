<?php
	function conexion () {
		$host = "uvmw5o.stackhero-network.com";
		$bd = "twitchAnalytics";
		$user = "root";
		$password = "O3wGdqU2E8oq62OVCk5tcfUeqmakK4bZ";
		$port = "7879";

		$con = mysqli_connect($host, $user, $password, $bd, $port);

		if (!$con) {
			echo "Error de conexión de base de datos <br>";
			echo "Error número: " . mysqli_connect_errno();
			echo "Texto error: " . mysqli_connect_error();
			exit;
		} else {
			echo "Conexión correcta a la base de datos <br>";
		}
		return $con;
	}
?>
