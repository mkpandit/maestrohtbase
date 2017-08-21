<?php
/**
 * FAI-Storage template controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class fai_template_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'fai_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'fai_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'fai_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'fai_identifier';
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
	'fai_configuration' => 'Fai Manager',
	'fai_title' => 'Fai Manager',
	'fai_add_storages' => 'Please add a Fai Server as Storage first!',
	'fai_id' => 'ID',
	'fai_mac' => 'MAC Adress',
	'fai_ip' => 'IP Adress',
	'fai_user' => 'User',
	'fai_password' => 'Password',
	'fai_comment' => 'Comment',
	'fai_actions' => 'Actions',
);

var $htvcenter_base_dir;
var $htvcenter;
var $htvcenter_ip;
var $event;


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
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/fai/lang", 'fai-template.ini');
		$this->tpldir   = $this->rootdir.'/plugins/fai/tpl';
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
	 * Select a FAI template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/fai/class/fai-template.select.class.php');
			$controller = new fai_template_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
//			$controller->message_param = $this->message_param;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/fai/lang", 'fai-template.ini');
			$data = $controller->action();
		}

		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/fai/lang", 'fai-template.ini');
		$content['label']   = $this->lang['fai_configuration'];
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
