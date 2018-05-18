<?php
	/* -----------------------------------------------------------------
		Solicita al webservice de moodle nativo los cursos matriculados
		por medio del id del usuario
	   -----------------------------------------------------------------
	*/
	function getUserEnrolled($userid, $shortname) {
		$domain='https://virtual.uao.edu.co';

		$token='e9f71033769b939f330fb85dd60c2172';
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

		$response_json = json_decode($curl_response);
		foreach($response_json as $valor) {
			if ($valor->shortname == $shortname) {
				$data = array("status" => "success");
				return $data;
			}
		}
		$data = array("status" => "failed");
		return $data;
	}
    
    function getUser($username) {
        $domain='https://test2.uao.edu.co/siga';

		$token='98053706d7ba2a06464113449c068fdd';
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
        $domain='https://test2.uao.edu.co/siga';

		$token='98053706d7ba2a06464113449c068fdd';
		$function_name='core_user_create_users';

		$service_url=$domain. '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $function_name;
		$restformat = '&moodlewsrestformat=json';

        $user = array('username'=> $usuario["username"], 'password' => $usuario["password"], 'firstname' => $usuario["nombre"], 'lastname' => $usuario["apellido"], 'email'=> $usuario["email"], 'auth' => $usuario["auth"]);
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
            $message = (string)$xml_response->handle->message;
            $data = array("status" => "failed", "service" => "createuser", "message" => $response_object->message);
        } else {
            $data = array("status" => "success");
        }
        
        return $data;
    }
?>
