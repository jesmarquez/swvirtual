<?php
    // include("conduit.php");
	include("wsmoodle.php");
	include("log.php");
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
    }
	*/
	
    // creamos parametros
	$process = false;
	switch (count($array)) {
		case 6:
			// uri tipo: http://domain/sw/api/recurso/id1/id2
			$id2 = $array[count($array) - 1];
			$id = $array[count($array) - 2];
			$recurso = $array[count($array) - 3];
			$process = true;
			break;
		case 5:
			// uri tipo: http://domain/sw/api/recurso/id1
			$id = $array[count($array) - 1];
			$recurso = $array[count($array) - 2];
			$process = true;
			break;
		case 4:
			// uri tipo: http://domain/sw/api/recurso
			$recurso = $array[count($array) - 1];
			$process = true;
			break;
		default:
			print_json(400, "Bad Request", null);
			break;
	}
	
	if ($process) {
		$tiempo = date('d-m-Y H:i:s');
		$method_request = $_SERVER['REQUEST_METHOD']; 
		$ip_remote = $_SERVER['REMOTE_ADDR'];
		$record_log = $tiempo.' '.$ip_remote.' '.$method_request.' '.$recurso.' bodyrequest: '.$bodyRequest.PHP_EOL;
		save_record_to_log($record_log);
		
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
					case "matricula":
						if (isset($id) && isset($id2)) {
							if (strlen($id) > 0 && strlen($id2) > 0) {
								$response_user = getUser($id);
								if ($response_user['status'] == "failed") {
									print_json(404, "Usuario no existe", $response_user);
									break;
								}
								
								$response_course = getCourse($id2);
								if ($response_course['status'] == 'failed') {
									print_json(404, "Curso no existe", $response_course);
									break;
								} 
								
								// print_json(200, "Ok", $data);
								$response_matriculado = getUserEnrolled($response_user['id'], $id2);
								if ($response_matriculado['status'] == "failed") {
									print_json(404, "Usuario no esta matriculado", $response_matriculado);
								} else {
									print_json(200, "Matriculado!", $response_matriculado);
								}
								
							} else {
								print_json(400, "Bad Request", null);
							}
						} else {
							print_json(400, "Bad Request", null);
						}
						break;
                    case "curso":
						if(isset($id)) {
							$data = getCourse($id);
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
									$data = array("status" => "failed", "service" => "createuser", "message" => "Usuario ya existe");
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
								$data = array("status" => "failed", "service" => "createuser" ,"message" => "Falta parámetros");
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
                                    $matricula['courseid'] = $response_course['id'];
									//verifica si el estudiante existe!
									$response_user = getUser($matricula['username']);
									if ($response_user['status'] == 'success') {
										// si curso y usuario existen ...
										// determinar si el usuario ya se encuentra matriculado
										// print_json(200, "Ok", $data);
                                        $matricula['userid'] = $response_user['id'];
										$response_matriculado = getUserEnrolled($response_user['id'], $matricula['shortname']);

										if ($response_matriculado['status'] == "not-enrolled") {
											$response_enroll = createEnroll($matricula);
											switch($response_enroll['status']) {
												case "success":
													print_json(201, "Created", $response_enroll);
												break;
												case "failed":
													print_json(404, "Not Found", $response_enroll);
												break;
											}
										} else {
                                            if ($response_matriculado['status'] == "enrolled")
                                                print_json(404, "Ya esta matriculado", $response_matriculado);
                                            else
                                                print_json(500, "Failed!", $response_matriculado);
										}
									} else {
										$data = array("status" => "failed", "service" => "createenroll", "message" => "Usuario no existe!");
										print_json(404, "Not found", $data);
									}
								} else {
									//si curso no existe...no hay matricula
									$data = array("status" => "failed", "service" => "createenroll" , "message" => "Curso no existe!");
									print_json(404, "Not found", $data);
								}
							}
							else {
								$data = array("status" => "failed", "service" => "createenroll" ,"message" => "Falta parámetros");
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
		
			case 'DELETE':
				switch($recurso) {
					case "matricula":
						// Decodifica el cuerpo de la solicitud y lo guarda en un array de PHP
						$matricula = json_decode($bodyRequest, true);
						if (array_key_exists('shortname', $matricula) && array_key_exists('username', $matricula)) {
							// validamos si usuario existe
							$response_user = getUser($matricula['username']);
							if ($response_user['status'] == 'success') {
								// agregamos el id user
                                $matricula['userid'] = $response_user['id'];
                                //validamos si el curso existe
								$response_course = getCourse($matricula['shortname']);
								if ($response_course['status'] == 'success') {
                                    // agregamos el id del curso
                                    $matricula['courseid'] = $response_course['id'];
                                    //verificamos si esta matriculado 
                                    $response_matriculado = getUserEnrolled($response_user['id'], $matricula['shortname']);
                                    if ($response_matriculado['status'] == "enrolled") {
                                        // borramos matricula
                                        $response_delete = deleteEnroll($matricula);
                                        switch($response_delete['status']) {
                                            case "success":
                                                print_json(201, "Deleted", $response_delete);
                                            break;
                                            case "failed":
                                                print_json(404, "Not Found", $response_delete);
                                            break;
                                        }
                                    } else {
                                        if ($response_matriculado['status'] == "not-enrolled")
                                            print_json(404, "No esta matriculado", $response_matriculado);
                                        else
                                            print_json(500, "Failed!", $response_matriculado);
                                    }
								} else {
                                    //si curso no existe...no hay matricula
                                    $data = array("status" => "failed", "service" => "deleteenroll", "message" => "Curso no existe!");
                                    print_json(404, "Not found", $data);								
								}
							} else {
								$data = array("status" => "failed", "service" => "deleteenroll", "message" => "Usuario no existe!");
								print_json(404, "Not found", $data);
							}
						}
						else {
							$data = array("status" => "failed", "message" => "Falta parámetros");
							print_json(404, "Not found", $data);					
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
