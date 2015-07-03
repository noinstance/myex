<?php
class Utils {
	public static function fixSerializedData ($data) {
		return preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {

				$full_match = $match[0];
				$len = $match[1];
				$data = $match[2];

				if($len == strlen($data)) {
					// no biggie, all is cool
					return $full_match;
				} else {
					// special characters like \n or \t should not be counted twice
					$escape_count = substr_count($data, '\\');
					return 's:' . (strlen($data) - $escape_count) . ':"' . $data . '";';
				}
		    },
			str_replace('\"', '"', $data)
		);
	}	
}