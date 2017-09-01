<?php
/**
 * Tabmenu
*
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_tabmenu extends htmlobject_div
{
/**
* url to process request
*
* Form disabled if empty
* @access public
* @var string
*/
var $form_action = '';
/**
* Css class to highlight active tab
*
* @access public
* @var string
*/
var $tabcss = 'current';
/**
* Add a param to handle active tab
* If set to true ['active'] will be ignored
*
* @access public
* @var bool
*/
var $auto_tab = true;
/**
* Add a custom string to tabs
*
* @access public
* @var string
*/
var $custom_tab = '';
/**
* Add a floatbreaking div between tab and box
*
* @access public
* @var string
*/
var $floatbreaker = true;
/**
* Name of param to transport message to messagebox
*
* @access public
* @var string
*/
var $message_param = 'strMsg';
/**
* Regex pattern for messagebox (XSS)
*
* replace pattern with replace
* @access public
* @var array(array('pattern'=>'','replace'=>''));
*/
var $message_filter = array (
	array ( 'pattern' => '~</?script.+~i', 'replace' => ''),
	array ( 'pattern' => '~</?iframe.+~i', 'replace' => ''),
	array ( 'pattern' => '~</?object.+~i', 'replace' => ''),
	array ( 'pattern' => '~on.+=~i', 'replace' => ''),
	array ( 'pattern' => '~javascript~i', 'replace' => ''),
	array ( 'pattern' => '~://~', 'replace' => ':&frasl;&frasl;'),
	);
/**
* Time to show messagebox in milliseconds (1/1000 sec.)
*
* @access public
* @var int
*/
var $message_time = 10000;
/**
* Css class for messagebox
*
* @access public
* @var string
*/
var $message_css = 'msgBox';

	//------------------------------------------------
	/**
	* Constructor
	*
	* @access public
	* @param htmlobject $htmlobject
	* @param string $id
	*/
	//------------------------------------------------
	function __construct($htmlobject, $id = 'currenttab') {
		$this->html = $htmlobject;
		$this->id   = $id;
	}

	//------------------------------------------------
	/**
	* Init tabs
	*
	* @access public
	*/
	//------------------------------------------------
	function init() {
		if(isset($this->__data)) {
			$float = $this->html->div();
			$float->style = 'line-height:0px;clear:both;';
			$float->css   = 'floatbreaker';
			$float->add('&#160;');
			$i = 0;
			foreach ($this->__data as $key => $val) {
				if(isset($val) && $val !== '') {
					$val['id'] = $this->id.$key;
					if(isset($val['value'])) {
						$v      = $this->html->div();
						$v->id  = $val['id'];
						$v->css = $this->css.'_box';
						$v->add($val['value']);
						$v->add($float);
						unset($val['value']);
					}
					elseif(isset($this->__elements[$key])) {
						$v = $this->__elements[$key];
					} else { 
						$v = null;
					}
					if(!isset($val['label']))   { $val['label'] = ''; }
					if(!isset($val['target']))  { $val['target'] = $this->html->thisfile; }
					if(!isset($val['request'])) { $val['request'] = array(); }
					if(!isset($val['onclick'])) { $val['onclick'] = false; }
					if(
						isset($val['active']) &&
						$val['active'] === true &&
						$this->html->request()->get($this->id) === ''
					) {
						$_REQUEST[$this->id] = "$key";
					}
					$this->__data[$key] = $val;
					isset($v) ? $this->__elements[$key] = $v : null;
					$i++;
				}
			}
		}
	}

	//------------------------------------------------
	/**
	 * Add content
	 *
	 * @access public
	 * @param array $data
	 * @param null $key not in use
	 * <code>
	 * $html = new htmlobject('path_to_htmlobjects');
	 * $tab  = $html->tabmenu('id');
	 *
	 * $content               = array();
	 * $content[0]['label']   = 'some title';
	 * $content[0]['value']   = 'some content text';
	 * $content[0]['target']  = 'somefile.php';
	 * $content[0]['request'] = array('param1'=>'value1');
	 * $content[0]['onclick'] = false;
	 * $content[0]['active']  = false;
	 * $content[0]['hidden']  = false;
	 *
	 * $tab->add($content);
	 * </code>
	 */
	//------------------------------------------------
	function add($data, $key = null) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				if(is_array($v)) {
					if(
						isset($v['label']) ||
						isset($v['value']) ||
						isset($v['target']) ||
						isset($v['request']) ||
						isset($v['onclick']) ||
						isset($v['active'])
					) {
						if(isset($this->__data[$k])) {
							$v = $v + $this->__data[$k];
						}
						$this->__data[$k] = $v;
					}
				}
			}
		}
	}

	//------------------------------------------------
	/**
	* Get tabs as string
	*
	* @access public
	* @param array $arr
	* @return string
	*/
	//------------------------------------------------
	function get_string() {
		$this->init();
		$_str = '';
		($this->form_action != '') ? $_str .= '<form action="'.$this->form_action.'" method="POST">' : null;
		if(isset($this->__data)) {
			$current = $this->get_current();
			$_str .= $this->__get_js();
			$_str .= $this->__get_tabs($current);
			foreach ($this->__data as $key => $value) {
				if(isset($this->__elements[$key])) {
					$html = $this->__elements[$key];
					if($value['id'] !== $this->id.$current) {
						$html->style = 'display:none;';
					} else {
						$old = $html;
						$html = $this->html->div();
						$html->css = $old->css;
						$html->id  = $old->id; 
						$html->add($this->__get_messagebox(), 'msg');
						$html->add($old->get_elements());
					}
					$_str .= $html->get_string();
				}
			}
		}
		($this->form_action != '') ? $_str .= '</form>' : null;
		return $_str;
	}

	//------------------------------------------------
	/**
	* Get array key of current element
	*
	* @access public
	* @return string | null
	*/
	//------------------------------------------------
	function get_current() {
		$req = $this->html->request()->get($this->id);
		if($req !== '') {
			return "$req";
		} else {
			if(isset($this->__data)) {
				$current = array_keys($this->__data);
				return "$current[0]";
			}
		}
	}

	//------------------------------------------------
	/**
	* Create tabs
	*
	* @access private
	* @param string $currenttab
	* @return string
	*/
	//------------------------------------------------
	function __get_tabs($currenttab) {
		$thisfile = $this->html->thisfile;
		$attribs  = $this->__attribs();
		$_str = '';	
		foreach($this->__data as $key => $tab) {
			$css = '';
			if(!isset($tab['hidden']) || !$tab['hidden'] === true) {
				if($tab['id'] == $this->id.$currenttab) {
					$css = ' class="'.$this->tabcss.'"';
				}
				$auto = '';
				if($this->auto_tab === true) {
					$auto = '?'.$this->id.'='.$key;
				}
				$i = 0;
				if(!isset($tab['request']) || $tab['request'] === '') {
					$tab['target'] = $tab['target'].$auto;
				}
				else if(is_array($tab['request'])) {
					$r = '';
					foreach ($tab['request'] as $ke => $arg) {
						$d = '&amp;';
						if($i === 0 && $auto === '') {
							$d = '?';
							$i = 1;
						}
						if(is_array($arg)) {
							foreach($arg as $k => $v) {
								$r .= $d.$ke.'['.$k.']='.$v;
							}
						}
						if(is_string($arg)) {
							$r .= $d.$ke.'='.$arg;
						}
					}
					$tab['target'] = $tab['target'].$auto.$r;
				}
				else if(is_string($tab['request'])) {
					if($auto !== '') {
						$tab['target'] = $tab['target'].$auto.'&amp;'.$tab['request'];
					} else {
						$tab['target'] = $tab['target'].'?'.$tab['request'];
					}
				}
				$_str .= '<li id="tab_'.$tab['id'].'"'.$css.'>';
				$_str .= "<span>";

				if(strstr($tab['target'], $thisfile) && $tab['onclick'] !== false) {
					$_str .= '<a href="'.$tab['target'].'" onclick="'.$this->id.'Toggle(\''.$tab['id'].'\'); this.blur(); return false;">';
				} else {
					$_str .= '<a href="'.$tab['target'].'" onclick="this.blur();">';
				}
				$_str .= $tab['label'];
				$_str .= "</a>";
				$_str .= "</span>";
				$_str .= "</li>\n";
			}
		}
		// build tab box
		$str = '';
		if($_str !== '') {
			$str = "\n<div ".$attribs.">\n";
			$str .= "<ul>\n";
			$str .= $_str;
			$str .= "</ul>\n";
			if($this->custom_tab != '') {
				$str .= "<div class=\"custom_tab\">".$this->custom_tab."</div>\n";
			}
			if($this->floatbreaker === true) {
				$str .= "<div class=\"floatbreaker\" style=\"line-height:0px;clear:both;\">&#160;</div>\n";
			}
			$str .= "</div>\n";
		}
		return $str;
	}

	//------------------------------------------------
	/**
	* Create JS toggle function
	*
	* @access private
	* @return string
	*/
	//------------------------------------------------
	function __get_js() {
	$_str = '';
		$_str .= "\n<script type=\"text/javascript\">\n";
		$_str .= "function ".$this->id."Toggle(id) {\n";
		foreach($this->__data as $key => $tab) {
			if(isset($this->__elements[$key])) {
				$_str .= "document.getElementById('".$tab['id']."').style.display = 'none';\n";
				$_str .= "document.getElementById('tab_".$tab['id']."').className = '';\n";
			}
		}
		$_str .= "document.getElementById(id).style.display = 'block';\n";
		$_str .= "document.getElementById('tab_' + id).className = '".$this->tabcss."';\n";
		$_str .= "}\n";	
		$_str .= "</script>\n";
	return $_str;
	}

	//------------------------------------------------
	/**
	* Create messagebox
	*
	* @access private
	* @return string
	*/
	//------------------------------------------------	
	function __get_messagebox() {
	$_str = '';
		$tmpfilter = $this->html->request()->filter;
		$filter    = $tmpfilter + $this->message_filter;
		$this->html->request()->set_filter($filter);
		$msg = $this->html->request()->get($this->message_param);
		// reset filter
		$this->html->request()->set_filter($tmpfilter);
		if($msg !== "") {
			$_str .= '';
			$_str .= '<div class="'.$this->message_css.'" id="'.$this->id.'msgBox">'.$msg.'</div>';
			$_str .= '<script type="text/javascript">';
			$_str .= 'var '.$this->id.'aktiv = window.setInterval("'.$this->id.'msgBox()", '.$this->message_time.');';
			$_str .= 'function '.$this->id.'msgBox() {';
			$_str .= '    document.getElementById(\''.$this->id.'msgBox\').style.display = \'none\';';
			$_str .= '    window.clearInterval('.$this->id.'aktiv);';
			$_str .= '}';
			$_str .= '</script>';
			$_str .= '';
		}
	return $_str;
	}

}
?>
