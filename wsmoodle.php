<?php
	/* -----------------------------------------------------------------
		Solicita al webservice de moodle nativo los cursos matriculados
		por medio del id del usuario
	   -----------------------------------------------------------------
	*/
	function getUserEnrolled($userid, $shortname) {
		$domain='https://campus.uaovirtual.edu.co';

		$token = getenv('TOKEN_WS');
		$function_name='core_enrol_get_users_courses';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';

		$args['userid'] = $userid;

		$url_str=http_build_query($args);
		$curl=curl_init($service_url . $restformat);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $url_str);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		curl_close($curl);
        
        $response_object = json_decode($curl_response);
        if (isset($response_object->exception)) {
            $data = array("status" => "failed", "service" => "getUserEnrolled", "message" => $response_object->message);
        } else {
            foreach($response_object as $valor) {
                if ($valor->shortname == $shortname) {
                    $data = array("status" => "enrolled", "service" => "getUserEnrolled", "message" => "Usuario ya está matriculado!");
                    return $data;
                }
            }
            $data = array("status" => "not-enrolled", "service" => "getUserEnrolled", "message" => "Usuario no está matriculado!");
        }        
		return $data;
	}
    
    function getUser($username) {
        $domain='https://campus.uaovirtual.edu.co';

		$token = getenv('TOKEN_WS');
		$function_name='core_user_get_users_by_field';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';

        $args = array('field' => 'username', 'values' => 
            array('0' => $username));

		$url_str=http_build_query($args);
		$curl=curl_init($service_url . $restformat);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $url_str);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		curl_close($curl);
        
        $response_object = json_decode($curl_response);
        if (empty($response_object)) {
            $data = array("status" => "failed", "service" => "getuser", "message" => "Usuario ".$username." no fue encontrado");
        } else {
            $data = array("status" => "success", "id" => $response_object[0]->id, "username" => $response_object[0]->username, "email" => $response_object[0]->email, "status" => "success");
        }
        
        return $data;
    }
    
    function createUser($usuario) {
        $domain='https://campus.uaovirtual.edu.co';

		$token = getenv('TOKEN_WS');
		$function_name='core_user_create_users';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';

        $user = array('username'=> $usuario["username"],
                        'password' => $usuario["password"],
                        'firstname' => $usuario["nombre"],
                        'lastname' => $usuario["apellido"],
                        'email'=> $usuario["email"],
                        'auth' => $usuario["auth"],
                        'preferences' => array('0' => array('type' => 'auth_forcepasswordchange', 'value' => 1)));
                        
        $list_users = array($user);

        $args = array('users' => $list_users);

		$url_str=http_build_query($args);
		$curl=curl_init($service_url . $restformat);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $url_str);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		curl_close($curl);        
        
        $response_object = json_decode($curl_response);
        if (isset($response_object->exception)) {
            $data = array("status" => "failed", "service" => "createuser", "message" => $response_object->message);
        } else {
            $data = array("status" => "success");
        }
        
        return $data;
    }
    
    function getCourse($shortname) {
        $domain='https://campus.uaovirtual.edu.co';

		$token = getenv('TOKEN_WS');
		$function_name='core_course_get_courses_by_field';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';

        $args['field'] = 'shortname';
        $args['value'] = $shortname;

		$url_str=http_build_query($args);
		$curl=curl_init($service_url . $restformat);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $url_str);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		curl_close($curl);         
        
        $response_object = json_decode($curl_response);

        if (count($response_object->courses) == 0) {
            $message = "Curso no fue encontrado";
            $data = array("status" => "failed", "service" => "getcourse", "message" => $message);

        } else {    
			$data = array("id" => $response_object->courses[0]->id, "shortname" => $response_object->courses[0]->shortname, "idnumber" => $response_object->courses[0]->idnumber, "status" => "success");
        }

        return $data;
    }
    
    function createEnroll ($matricula) {
        $domain='https://campus.uaovirtual.edu.co';

		$token = getenv('TOKEN_WS');
		$function_name='enrol_manual_enrol_users';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';

        $list_students = array();
        $student = array('roleid' => 5,
                'userid' => $matricula["userid"], 
                'courseid' => $matricula["courseid"],
                'timestart' => strtotime($matricula["timestart"]),
                'timeend' => strtotime($matricula["timeend"]));
        $list_students[] = $student;

        $args = array('enrolments' => $list_students);

		$url_str=http_build_query($args);
		$curl=curl_init($service_url . $restformat);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $url_str);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		curl_close($curl);         
        
        $response_object = json_decode($curl_response);

        if (isset($response_object->exception)) {
            $data = array("status" => "failed", "service" => "createEnroll", "message" => $response_object->message);
        } else {
            $data = array("status" => "success", "message" => "Matricula creada!");
        }
        
        return $data;
    }
    
    function deleteEnroll($matricula) {
        $domain='https://campus.uaovirtual.edu.co';

		$token = getenv('TOKEN_WS');
		$function_name='enrol_manual_unenrol_users';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';
        
        $list_students = array();
        $student = array('roleid' => 5,
                'userid' => $matricula["userid"], 
                'courseid' => $matricula["courseid"],
                'roleid' => 5);

        $list_students[] = $student;
        
        $args = array('enrolments' => $list_students);
        
		$url_str=http_build_query($args);
		$curl=curl_init($service_url . $restformat);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $url_str);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$curl_response = curl_exec($curl);
		if ($curl_response === false) {
			$info = curl_getinfo($curl);
			curl_close($curl);
			die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		curl_close($curl);             

        $response_object = json_decode($curl_response);

        if (isset($response_object->exception)) {
            $data = array("status" => "failed", "service" => "deleteEnroll", "message" => $response_object->message);
        } else {
            $data = array("status" => "success", "message" => "Desmatriculado!");
        }

        return $data;
    }
?>
