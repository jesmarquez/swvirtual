<?php
    include("conduit.php");
    // Permite la conexion desde cualquier origen
    header("Access-Control-Allow-Origin: *");
    // Permite la ejecucion de los metodos
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");  

    $array = explode("/", $_SERVER['REQUEST_URI']);
    // Obtener el cuerpo de la solicitud HTTP
    $bodyRequest = file_get_contents("php://input");
    //en caso de solicitar una URL con final / eliminamos esa posicion
    /*
    foreach ($array as $key => $value) {
        if(empty($value)) {
        unset($array[$key]);
        }
    }*/

    // creamos parametros

    if (count($array) == 5) {
        $id = $array[count($array) - 1];
        $recurso = $array[count($array) - 2];
    } else {
        $recurso = $array[count($array) - 1];
    }

     // Analiza el metodo usado actualmente de los cuatro disponibles: GET, POST, PUT, DELETE
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
			// Si la variable id existe se solicita info del usuario
			if(isset($id)) {
				$data = getUser($id);
				switch($data['status']) {
					case "success":
						print_json(200, "OK", $data);
					break;
					case "failed":
						print_json(404, "Not Found", $data);
					break;
				}
			} else {
				// si el id no está presente se hizo un mal request
				print_json(400, "Bad Request", null);
				break;
			}
			break;

        case 'POST':
			/*  URL por POST solo puede ser de estilo http://localhost/api/usuario 
			no habria por que existir un Id nuevo elemento */

            if(!isset($id)) {
                // Decodifica el cuerpo de la solicitud y lo guarda en un array de PHP
                $data = json_decode($bodyRequest, true);
				// Llamamos crear usuario
				$response = createUser($data);
				switch($response['status']) {
					case "success":
						print_json(201, "Created", $response);
					break;
					case "failed":
						print_json(404, "Not Found", $response);
					break;
				}
            } else {
                print_json(400, "Bad Request", null);
            }
            break;
    
        default:
            // Acciones cuando el metodo no se permite
            // En caso de que el Metodo Solicitado no sea ninguno de los cuatro disponible, envia la siguiente respuesta
            print_json(405, "Method Not Allowed", null);
            break;
   }

   
    // Esta funcion imprime las respuesta en estilo JSON y establece los estatus de la cebeceras HTTP
    function print_json($status, $mensaje, $data) {
        header("HTTP/1.1 $status $mensaje");
        header("Content-Type: application/json; charset=UTF-8");
    
        $response['statusCode'] = $status;
        $response['statusMessage'] = $mensaje;
        $response['data'] = $data;
    
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
?>