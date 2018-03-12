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

     // Analiza el metodo usado actualmente de los cuatro disponibles: GET, POST
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
			// Si la variable id existe se solicita info del usuario
			switch($recurso) {
				case "usuario":
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
					}
					break;
				
				default:
					print_json(404, "Not found", null);
					break;
			}
			break;

        case 'POST':
			/*  URL por POST solo puede ser de estilo http://localhost/api/usuario 
			no habria por que existir un Id nuevo elemento */
            switch($recurso) {
                case "usuario":
                    if(!isset($id)) {
                        // Decodifica el cuerpo de la solicitud y lo guarda en un array de PHP
                        $usuario = json_decode($bodyRequest, true);
						// Validamos la presencia de todos los parametros para crear usuarios
						if (array_key_exists('username', $usuario) && array_key_exists('password', $usuario) && array_key_exists('nombre', $usuario) && array_key_exists('apellido', $usuario) && array_key_exists('email', $usuario) && array_key_exists('auth', $usuario)) {
							// verificamos existencia del usuario
							$response = getUser($usuario['username']);
							if ($response['status'] == 'success') {
								$data = array("status" => "failed", "message" => "Usuario ya existe");
								print_json(404, "Not found", $data);
							} else {
								// crear usuario
								$response = createUser($usuario);
								switch($response['status']) {
									case "success":
										print_json(201, "Created", $response);
									break;
									case "failed":
										print_json(404, "Not Found", $response);
									break;
								}
							}
						}
						else {
							$data = array("status" => "failed", "message" => "Falta parámetros");
							print_json(404, "Not found", $data);
						}
                    } else {
                        print_json(400, "Bad Request", null);
                    }
                break;

                case "matricula":
					if(!isset($id)) {
                        // Decodifica el cuerpo de la solicitud y lo guarda en un array de PHP
                        $matricula = json_decode($bodyRequest, true);
						// Validamos la presencia de todos los parametros para crear usuarios
						if (array_key_exists('shortname', $matricula) && array_key_exists('username', $matricula) && array_key_exists('role', $matricula) && array_key_exists('timestart', $matricula) && array_key_exists('timeend', $matricula)) {
							// verifica si el curso existe
							$response_course = getCourse($matricula['shortname']);
							if ($response_course['status'] == 'success') {
								//verifica si el estudiante existe!
								$response_user = getUser($matricula['username']);
								if ($response_user['status'] == 'success') {
									// si curso y usuario existen ... crear la matrícula
									$response_enroll = createEnroll($matricula);
									switch($response_enroll['status']) {
										case "success":
											print_json(201, "Created", $response);
										break;
										case "failed":
											print_json(404, "Not Found", $response);
										break;
									}
								} else {
									$data = array("status" => "failed", "message" => "Usuario no existe!");
									print_json(404, "Not found", $data);
								}
							} else {
								//si curso no existe...no hay matricula
								$data = array("status" => "failed", "message" => "Curso no existe!");
								print_json(404, "Not found", $data);
							}
						}
						else {
							$data = array("status" => "failed", "message" => "Falta parámetros");
							print_json(404, "Not found", $data);
						}
					} else {
						print_json(400, "Bad Request", null);
					}
					
                break;

                default:
                    print_json(404, "Not found", null);
                break;
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