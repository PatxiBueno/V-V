<?php
	header("Content-type: application/json; charset=utf-8");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once('endPoints/get/user.php');
	require_once('endPoints/get/streams.php');
	require_once('endPoints/get/topsofthetops.php');
	require_once('endPoints/get/enriched.php');
	require_once('endPoints/post/register.php');
	require_once('endPoints/post/token.php');
	require_once('autenticacion.php');

	$method = $_SERVER['REQUEST_METHOD'];

	if($method === "GET"){
		$headers = getallheaders();
		if (!validarToken($headers)) {
			http_response_code(401);
			$respuesta = ["error" => "Unauthorized. Token is invalid or expired."];
			echo json_encode($respuesta);
			exit;
		}
		$urlCompleta = $_SERVER['REQUEST_URI'];
		$ruta = parse_url($urlCompleta, PHP_URL_PATH);
		if ($ruta == "/analytics/user") {
			if(isset($_GET['id'])){
				$id_usuario = $_GET['id'];
			}else{
				$id_usuario = '';
			}
			getUserFromApi($id_usuario);
			exit;
		}
		if ($ruta == "/analytics/streams") {
			streams();
			exit;
		}
		if ($ruta == "/analytics/streams/enriched") {
			if(isset($_GET['limit'])) {
				$limit = $_GET['limit'];
			}else {
				$limit = '';
			}
			enriched($limit);
			exit;
		}
		if ($ruta == "/analytics/topsofthetops") {
			if(isset($_GET['since'])){
				$since = intval($_GET['since']);
			}else{
				$since = 600;
			}
			getTopOfTheTops($since);
			exit;
		}
		http_response_code(404);
		echo json_encode(["error" => "Not Found."]);
		exit;
	}

	if($method === "POST"){
		$ruta = $_SERVER['REQUEST_URI'];
		if ($ruta == "/register") {
			$input = file_get_contents("php://input");
			$data = json_decode($input, true);	
			register($data);
			exit;
		}
		if ($ruta == "/token") {
			$input = file_get_contents("php://input");
			$data = json_decode($input, true);	
			generarToken($data);
			exit;
		}
	}
?>
