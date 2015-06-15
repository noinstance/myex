<?php

class Wordpress {

	public static function getDatabaseConnectionObject($wpRoot) {
		
		$ret = new stdClass;

		$lines = file($wpRoot . '/wp-config.php');
	    foreach ($lines as $lineNumber => $line) {
	        
	        $pattern = "/define\('DB_NAME', '(.+)'\);/i";
			preg_match($pattern, $line, $matches);
			if(count($matches)) {
				$ret->database = $matches[1];
				continue;
			}

			$pattern = "/define\('DB_USER', '(.+)'\);/i";
			preg_match($pattern, $line, $matches);
			if(count($matches)) {
				$ret->username = $matches[1];
				continue;
			}

			$pattern = "/define\('DB_PASSWORD', '(.+)'\);/i";
			preg_match($pattern, $line, $matches);
			if(count($matches)) {
				$ret->password = $matches[1];
				continue;
			}

			// $pattern = "/define\('DB_HOST', '(.+)'\);/i";
			// preg_match($pattern, $line, $matches);
			// if(count($matches)) {
			// 	$hostname = $matches[1];
			// 	$hostnameSegments = explode(':', $hostname);
			// 	$ret->hostname = $hostnameSegments[0];
			// 	$ret->port = isset($hostnameSegments[1]) ? $hostnameSegments[1] : 3306;
			// 	continue;
			// }	
	    }	

	    return $ret;
	}

}