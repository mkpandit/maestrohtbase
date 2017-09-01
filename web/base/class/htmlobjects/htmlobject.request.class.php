<?php
/**
 * Http Request Handler
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_request
{
/**
* regex pattern for http request (crosssitescripting)
*
* @access public
* @var array
* <code>
* $request = new htmlobject_request();
* $request->filter = array(
*    array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
*  );
* </code>
*/
var $filter = array();


	//-------------------------------------------------
	/**
	* Get http request as cleaned string
	* Returns empty when request not set
	*
	* @access public
	* @param string $arg
	* @param bool $raw enable return null
	* @return string | array | null
	*/
	//-------------------------------------------------
	function get($arg,  $raw = false) 
	{
		$return = '';
		$Req = '$_REQUEST'.$this->string_to_index($arg);
		if(eval("return isset($Req);")) {
			if(eval("return $Req;") != '') {			
				if(is_array(eval("return $Req;"))) {
					$return = $this->__get_request_array(eval("return $Req;"));
				} else {
					$return = $this->__filter_request(eval("return $Req;"));
				}
			}
		} else {
			if($raw === true) {
				$return = null;
			}
		}
		return $return;
	}

	//-------------------------------------------------
	/**
	* Set filter for request handling (XSS)
	*
	* @access public
	* @param  array $arg
	* @return string
	* <code>
	* $request = new htmlobject_request();
	* $request->set_filter(array(
	*    array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
	*  );
	* </code>
	*/
	//-------------------------------------------------
	function set_filter($arg = array()) {
		if(isset($arg) && is_array($arg)) {
			$this->filter = array();
			foreach($arg as $key => $value) {
				if(isset($value['pattern'])) {
					if(!isset($value['replace'])) {
						$value['replace'] = '';
					}
					$this->filter[] = array('pattern' => $value['pattern'], 'replace' => $value['replace']);
				} 
			}
		}
	}

	//-------------------------------------------------
	/**
	 * Transform string to array index string
	 *
	 * @access public
	 * @param string $arg
	 * @return string
	 */
	//-------------------------------------------------	
	function string_to_index($arg) {
		$str = '';
		// replace unindexed array
		$arg   = $this->unindex_array($arg);
		$regex = '~(\[.*\])~';
		preg_match($regex, $arg, $matches);
		if($matches) {
			$str = '['.preg_replace('~\[.*\]~', '', $arg).']'.$matches[0];
		}
		else  {
			$str = '['.$arg.']';
		}
		// add quots to make it array
		$str = str_replace('[', '["', $str);
		$str = str_replace(']', '"]', $str);		
		return $str;
	}
	//-------------------------------------------------
	/**
	 * Remove unindexed array
	 *
	 * @access public
	 * @param string $arg
	 * @return string
	 */
	//-------------------------------------------------	
	function unindex_array($name) {
		return preg_replace('~\[]$~', '', $name);
	}

	//-------------------------------------------------
	/**
	 * Get values from http request as array
	 *
	 * @access protected
	 * @param string $arg
	 * @return array 
	 */
	//-------------------------------------------------
	function __get_request_array($arg) {
		$ar = array();
		if(is_array($arg)) {
			foreach($arg as $key => $value) {
				if(is_array($value)) {
					$ar[$key] = $this->get_request_array($value);
				}
				if(is_string($value)) {
					$ar[$key] = $this->__filter_request($value);
				}
			}
		}
		if(is_string($arg)) {
			$ar[$key] = $this->__filter_request($value);
		}
		return $ar;
	}

	//-------------------------------------------------
	/**
	* Performes preg_replace
	*
	* @access protected
	* @param string $arg
	* @return string
	*/
	//-------------------------------------------------
	function __filter_request($arg) {
		if(is_string($arg)) {
			if(get_magic_quotes_gpc()) {
				$arg = stripslashes($arg);
			}
			if(is_array($this->filter)) {
				foreach ($this->filter as $reg) {
					$arg = preg_replace($reg['pattern'], $reg['replace'], $arg);
				}
			}
			return $arg;
		}
	}

}
?>
