<?php
	require_once('/endPoints/get/*');
	require_once('/endPoints/post/*');
	
	$method = $_SERVER['REQUEST_METHOD'];
	
	
	if($method === "GET"){
		
		if(!usuarioValido($_SERVER['HTTP_AUTORIZATION'])){ ///revisar //hacer metodo
				//devolver lo que toca
		}
		
		$ruta = $_SERVER['REQUEST_URI'];
		
		if ($ruta == "analytics/user") {//revisar
			if(isset($_GET['id'])){
				analytics($_GET['id']); //revisar que devuelve get
				exit; //revisar
			}else{
				analytics("");
				exit; //revisar
			}
		}
		if ($ruta == "analytics/streams") {
			streams();
			exit;
		}
		if ($ruta == "analytics/stream/enriched") {
			if(isset($_GET['limit'])){
				enriched($_GET['limit']);
				exit;
			}else{
				enriched(0);
				exit;
			}
		}
	}
	
	if($method === "POST"){
		$ruta = $_SERVER['REQUEST_URI'];
		
		if ($ruta == "register") {//llamar funcion dentro de enponits post register
			return register();//los headers no se de donde salen
		}
	}


?>
