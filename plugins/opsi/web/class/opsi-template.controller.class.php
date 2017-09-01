<?php
/**
 * OPSI Template Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class opsi_template_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'opsi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'opsi_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'opsi_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'opsi_identifier';
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
var $lang = array(
	'opsi_configuration' => 'OPSI Manager',
	'opsi_title' => 'OPSI Manager',
	'opsi_add_storages' => 'Please add an Opsi Server as Storage first!',
	'opsi_id' => 'ID',
	'opsi_mac' => 'MAC Adress',
	'opsi_ip' => 'IP Adress',
	'opsi_type' => 'Type',
	'opsi_name' => 'Name',
	'opsi_user' => 'User',
	'opsi_password' => 'Password',
	'opsi_comment' => 'Comment',
	'opsi_actions' => 'Actions',
);


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
		$this->htvcenter  = $htvcenter;
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->basedir  = $this->htvcenter->get('basedir');
		$this->tpldir   = $this->rootdir.'/plugins/opsi/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/opsi/lang", 'opsi-template.ini');
		require_once $this->basedir."/plugins/opsi/web/class/opsi.class.php";
		$this->opsi = new opsi();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Select OPSI template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/opsi/class/opsi-template.select.class.php');
			$controller = new opsi_template_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->opsi            = $this->opsi;
			$controller->lang            = $this->user->translate($this->lang, $this->rootdir."/plugins/opsi/lang", 'opsi-template.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/opsi/lang", 'opsi-template.ini');
		$content['label']   = $this->lang['opsi_configuration'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}




}
?>
