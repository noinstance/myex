<?php

class Required {

	public static function import($files) {
		foreach($files as $file) {
			$file .= '.php';
			include_once($file);
		}
	}
}