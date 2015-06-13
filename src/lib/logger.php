<?php
class Logger {

	private $outputFile;
	public static $singleton;

	public function __construct() {
		self::$singleton = $this;
	}

	public function setOutputFile($outputFile = null) {		
		self::$singleton->outputFile = isset($outputFile) ? $outputFile : null;
	}

	public static function log($message, $newline = true) {
		$str = $message;
		$str .= $newline ? "\n" : "";
		self::output($str);
	}

	public static function timestamp() {
		self::output("\nDatetime is " . date("Y-m-d H:i:s") . "\n");
	}

	public static function hr() {
		self::output("\n***\n");
	}

	public static function br() {
		self::output("\n");
	}

	// todo
	// cannot yet dump to file
	public static function dump($label, $value) {
		self::log('===');
		self::log($label . ':');
		ob_start();
		var_dump($value);
		$valueStr = ob_get_clean();
		self::log($valueStr);
	}

	private static function output($str) {
		if(isset(self::$singleton->outputFile)) {
			
			$fh = fopen(self::$singleton->outputFile, 'a') or die('cant open file');
			fwrite($fh, $str);
			fclose($fh);

		} else {
			print $str;	
		}		
	}
}