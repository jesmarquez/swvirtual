<?php
	/* -----------------------------------------------------------------
		Solicita al webservice de moodle nativo los cursos matriculados
		por medio del id del usuario
	   -----------------------------------------------------------------
	*/
	function getUserEnrolled($userid, $shortname) {
		$domain='https://uao-sandbox.mrooms.net';

		$token='9eb8b1740608c41ffcf345a6210fc2b3';
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
?>
