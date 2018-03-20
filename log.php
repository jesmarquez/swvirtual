<?php
	// guarda registro en archivo log
	function save_record_to_log($reglog) {
		$fp = fopen("./log/conduit.log", "a+");
		
		fwrite($fp, $reglog);
		fclose($fp);
		return;
	}
?>