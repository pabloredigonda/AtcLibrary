<?php

namespace Core\Exception;

class SmException extends \Exception {
	
	/**
	 * Returns the exception key.
	 * @return string	the key
	 */
	public function getKey() { 
		$key = "";
		$className = str_ireplace(__NAMESPACE__."\\", "", get_called_class());
		foreach(str_split($className) as $char ) {
			strtoupper($char) == $char and $key and $key .= ".";
			$key .= $char;
		}
		return strtolower($key);
	}
}

?>