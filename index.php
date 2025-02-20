<?php
	header("Content-type: application/json; charset=utf-8");
	

	require_once ('/var/www/html/endPoints/get/user.php');
	require_once('endPoints/get/streams.php');
	require_once('endPoints/get/topsofthetops.php');
	require_once('endPoints/get/enriched.php');
	require_once('endPoints/post/register.php');
	require_once('endPoints/post/token.php');
	require_once('autenticacion.php');

	$method = $_SERVER['REQUEST_METHOD'];
	
	if($method === "GET"){
		// Validamos el token
		$headers = getallheaders();
		if (!validarToken($headers)) {
			http_response_code(401);
			$respuesta = ["error" => "Unauthorized. Token is invalid or expired."];
			echo json_encode($respuesta);
			exit;
		}
		
		$urlCompleta = $_SERVER['REQUEST_URI'];
		$ruta = parse_url($urlCompleta, PHP_URL_PATH);
		echo $ruta;
		// ENDPOINT USER
		if ($ruta == "/analytics/user") {
			if(isset($_GET['id'])){
				$id_usuario = $_GET['id'];
			}else{
				$id_usuario = '';
			}
			user($id_usuario);
			exit;
		}
		// ENDPOINT STREAMS
		if ($ruta == "/analytics/streams") {
			streams();
			exit;
		}
		// ENDPOINT STREAMS ENRICHED
		if ($ruta == "/analytics/streams/enriched") {
			if(isset($_GET['limit'])){
				$limit = $_GET['limit'];
			}else{
				$limit = '';
			}
			enriched($limit);
			exit;
		}
		if ($ruta == "/analytics/topofthetops") {
			if(isset($_GET['since'])){
				$since = intval($_GET['since']);
			}else{
				$since = 600;
			}
			getTopOfTheTops($since);
			exit;
		}
		// Si llegamos a este punto es por que la URL era incorrecta
		http_response_code(404);
		echo json_encode(["error" => "Not Found."]);
		exit;
	}

	// FALTA ACABAR LOS POST Y PONERLOS EN FORMA DE FUNCIÓN
	if($method === "POST"){
		$ruta = $_SERVER['REQUEST_URI'];
		
		if ($ruta == "/register") {//llamar funcion dentro de enponits post register
			
			$input = file_get_contents("php://input");
			$data = json_decode($input, true);	
			register($data);//los headers no se de donde salen
			exit;
		}
		if ($ruta == "/token") {//llamar funcion dentro de enponits post register
			$input = file_get_contents("php://input");
			$data = json_decode($input, true);	
			generarToken($data);//los headers no se de donde salen
			exit;
		}
	}


?>
