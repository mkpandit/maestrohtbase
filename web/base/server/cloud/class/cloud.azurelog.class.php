<?php
/**
 * Storage Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class azurelog {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'azurelog';
/**
* message param
* @access public
* @var string
*/
var $message_param = "azurelog";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'azurelog';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'azurelog';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;
		$this->rootdir  = $this->htvcenter->get('webdir');
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$data = $this->log();
		$t = $this->response->html->template($this->tpldir.'/cloud-azure-log.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->response->get_array());
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		//$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($data['html_information'], 'html_information');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function log() {
		$d = array();
		$html_information = "";
		$content = shell_exec('sudo python /usr/share/htvcenter/web/base/server/cloud/script/azurelog.py');
		$log = json_decode($content, true);
		$log_html = "<table class='log-table'>";
		if(empty($log)){
			$log_html .= "<tr><td colspan=3>No log records found.</td></tr>";
		} else {
			foreach($log as $v) {
				$keys = array_keys($v);
				foreach($keys as $k){
					if(is_array($v[$k])){
						$log_html .= "<tr><td>" . ucfirst($k) ."</td><td>:</td><td><div style='word-wrap: break-word; width: 450px;'>";
						$key = array_keys($v[$k]);
						foreach($key as $ks){
							$log_html .= ucfirst($ks) . ": " . $v[$k][$ks] . "<br />";
						}
						$log_html .= "</div></td></tr>";
					} else {
						$log_html .= "<tr><td>" . ucfirst($k) . "</td><td>:</td><td><div style='word-wrap: break-word; width: 450px;'>" . $v[$k] . "</div></td></tr>";
					}
				}
			}
		}
		$log_html .= "</table>";
		$d['html_information']  = $log_html;
		return $d;
	}
}
?>
