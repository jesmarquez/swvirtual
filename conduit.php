<?php
	/*
		Funciones para consultar a conduit
	*/
    function getUser($username) {
        $token = FALSE;
        // $token = getenv('TOKEN_MR');
		$token = 'B72AEE5B7615496B4657ACE146FEB';
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
	
	function createUser() {
		$respuesta = array("");
		return "Ok";
	}
?>