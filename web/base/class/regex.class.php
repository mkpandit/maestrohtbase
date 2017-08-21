<?php
/**
 * @package htmlobjects
 */
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

//----------------------------------------------------------------------------------------
/**
 * Regex
 *
 * @package htmlobjects
 * @author HTBase <contact@htbase.com>
 * @copyright Copyright (c) 2015, HTBase Corp
 * @version 5.2
 */
//----------------------------------------------------------------------------------------
class regex
{
	//--------------------------------
	/**
	 * pereg_match()
	 *
	 * @param string $pattern
	 * @param string $match
	 * @return array|null
	 */
	//--------------------------------
	static public function match($pattern, $match) {
		@preg_match($pattern, $match, $matches);
		if(debug::active()) {
			$error = error_get_last();
			if(strstr($error['message'], 'preg_match')) {
				$msg = str_replace('preg_match() [<a href=\'function.preg-match\'>function.preg-match</a>]:', '' , $error['message']);
				debug::add($msg.' in '. $pattern, 'ERROR');
			}
		}
		if($matches) {
			return $matches;
		} else {
			return null;
		}
	}
	//--------------------------------
	/**
	 * pereg_replace()
	 *
	 * @param string $pattern
	 * @param string $replace
	 * @param string $string
	 * @return array|null
	 */
	//--------------------------------
	static public function replace($pattern, $replace, $string) {
		$error = '';
		$str = @preg_replace($pattern, $replace, $string) | $error;
		echo $error;
		if(debug::active()) {
			$error = error_get_last();
			if(strstr($error['message'], 'preg_replace')) {
				$msg = str_replace('preg_replace() [<a href=\'function.preg-replace\'>function.preg-replace</a>]:', '' , $error['message']);
				debug::add($msg.' in '. $pattern, 'ERROR');
			}
		}
		return $str;
	}

}
?>
