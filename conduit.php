<?php
	
	/*
		Funciones para consultar a conduit
	*/
    function getUser($username) {
        $token = FALSE;
        $token = getenv('TOKEN_MR');
		
        if ($token) {
            $service_url = 'https://uao-sandbox.mrooms.net/blocks/conduit/webservices/rest/user.php';
            $curl=curl_init($service_url);
            $curl_post_data = array('token'=>$token,'method'=>'get_user', 'username'=>'guest');
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('token'=>$token,'method'=>'get_user', 'value'=>$username, 'field'=>'username'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $curl_response = curl_exec($curl);
            if ($curl_response === false) {
                $info = curl_getinfo($curl);
                curl_close($curl);
                die('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($curl);
			//convertir a un objeto simplexml
			$xml_body = simplexml_load_string($curl_response);
			
			$status = $xml_body->get_user->status;
			if ((string)$status == 'success') {
				$user_xml = $xml_body->get_user->user;
				$json = json_encode($user_xml);
				$user_array = json_decode($json,TRUE);
				$data = array("username" => $user_array['username'], "email" => $user_array['email'], "status" => "success");
			} 
			if ((string)$status == 'failed') {
				$message = (string)$xml_body->get_user->response->message;
				$data = array("status" => "failed", "message" => $message);
			}
			
		} else {
            $data = array("status" => "failed", "message" => "Token no existe");
        }
		
		return $data;
    }
	
	function createUser($usuario) {
		include 'scheme_conduit_user.php';

		// crea un objeto simplexml y carga schema xml tipo user
		$xml_user = new SimpleXMLElement($xmlstr);

		// asignamos los valores elementos con los datos de usuario a crear
		$xml_user->datum->mapping[0][0] = $usuario["username"];
		$xml_user->datum->mapping[1][0] = $usuario["password"];
		$xml_user->datum->mapping[2][0] = $usuario["nombre"];
		$xml_user->datum->mapping[3][0] = $usuario["apellido"];
		$xml_user->datum->mapping[4][0] = $usuario["email"];
		$xml_user->datum->mapping[5][0] = $usuario["auth"];

		$xml_user_str = $xml_user->asXML();
		$token = FALSE;
		$token =  getenv('TOKEN_MR');
		if ($token) {
			$service_url = 'https://uao-sandbox.mrooms.net/blocks/conduit/webservices/rest/user.php';
			$curl=curl_init($service_url);
			$curl_post_data = array('token'=>$token,'method'=>'handle', 'xml'=>$xml_user_str);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$curl_response = curl_exec($curl);
			if ($curl_response === false) {
				$info = curl_getinfo($curl);
				curl_close($curl);
				die('error occured during curl exec. Additioanl info: ' . var_export($info));
			}
		
			curl_close($curl);
			// la respuesta es un string xml lo convertimos el xml en un objeto de simpleXML
			$xml_response = simplexml_load_string($curl_response);
			
			//obtenemos el status 
			$status = $xml_response->handle->status;
			// $status es un objeto simple XML
			if ((string)$status == 'success') {
				$data = array("status" => "success");
			} else {
				//extraemos el mensaje
				$message = (string)$xml_response->handle->message;
				$data = array("status" => "failed", "message" => $message);
			}
		} 
		else {
			$data = array("status" => "failed", "message" => "Token no existe");
		}

		return $data;
	}
	
	function getCourse($shortname) {
		$token = FALSE;
		$token =  getenv('TOKEN_MR');
		if ($token) {
			$service_url = 'https://uao-sandbox.mrooms.net/blocks/conduit/webservices/rest/course.php';
			$curl=curl_init($service_url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, array('token'=>$token,'method'=>'get_course', 'value'=>$shortname, 'field'=>'shortname'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$curl_response = curl_exec($curl);
			if ($curl_response === false) {
				$info = curl_getinfo($curl);
				curl_close($curl);
				die('error occured during curl exec. Additioanl info: ' . var_export($info));
			}
			curl_close($curl);

			// convertir en un objeto simpleXML
			$xml_body = simplexml_load_string($curl_response);
			
			$status = $xml_body->get_course->status;
			if ((string)$status == 'success') {
				$course_xml = $xml_body->get_course->course;
				$json = json_encode($course_xml);
				$course_array = json_decode($json,TRUE);
				if ($course_array['idnumber'] == null) $course_array['idnumber'] = 0;
				$data = array("shortname" => $course_array['shortname'], "idnumber" => $course_array['idnumber'], "status" => "success");
			}

			if ((string)$status == 'failed') {
				$message = (string)$xml_body->get_course->response->message;
				$data = array("status" => "failed", "message" => $message);
			}
			
			
		} else {
			$data = array("status" => "failed", "message" => "Token no existe");
		}
		
		return $data;
	}
	
	function createEnroll($matricula) {
		include 'scheme_conduit_enroll.php';

		$service_url = 'https://uao-sandbox.mrooms.net/blocks/conduit/webservices/rest/enroll.php';

		// crea un objeto simplexml y carga schema xml tipo user
		$xml_enroll = new SimpleXMLElement($xmlstr);

		// cambiamos los valores elementos con los datos de usuario a crear
		$xml_enroll->datum->mapping[0][0] = $matricula["shortname"];
		$xml_enroll->datum->mapping[1][0] = $matricula["username"];
		$xml_enroll->datum->mapping[2][0] = $matricula["role"];
		$xml_enroll->datum->mapping[3][0] = strval(strtotime($matricula["timestart"]));
		$xml_enroll->datum->mapping[4][0] = strval(strtotime($matricula["timeend"]));

		// convertir objeto simplexml en string
		$xml_enroll_str = $xml_enroll->asXML();

		$token = FALSE;
		$token =  getenv('TOKEN_MR');
		if ($token) {
			$curl=curl_init($service_url);
			$curl_post_data = array('token'=>$token,'method'=>'handle', 'xml'=>$xml_enroll_str);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$curl_response = curl_exec($curl);
			if ($curl_response === false) {
				$info = curl_getinfo($curl);
				curl_close($curl);
				die('error occured during curl exec. Additioanl info: ' . var_export($info));
			}

			curl_close($curl);

			$xml_response = simplexml_load_string($curl_response);
			
			//obtenemos el status 
			$status = $xml_response->handle->status;
			// $status es un objeto simple XML
			if ((string)$status == 'success') {
				$data = array("status" => "success", "message" => "Matricula creada!");
			} else {
				//extraemos el mensaje
				$message = (string)$xml_response->handle->message;
				$data = array("status" => "failed", "message" => $message);
			}
			
		} 
		else {
			$data = array("status" => "failed", "message" => "token moodleroom no existe");
		}
		
		return $data;
	}
	
?>