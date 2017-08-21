<?php
/**
 * IPMI Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class ipmi_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ipmi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ipmi_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ipmi_tab';
/**
* id for tabs
* @access public
* @var string
*/
var $identifier_name = 'ipmi_ident';
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
	'ipmi_configuration' => 'IPMI Manager',
	'ipmi_wakeup' => 'Wakeup',
	'ipmi_title' => 'IPMI Configuration',
	'ipmi_add_resources' => 'Please add physical resources first!',
	'ipmi_id' => 'ID',
	'ipmi_mac' => 'MAC Adress',
	'ipmi_ip' => 'IP Adress',
	'ipmi_user' => 'User',
	'ipmi_password' => 'Password',
	'ipmi_comment' => 'Comment',
	'ipmi_actions' => 'Actions',
	'ipmi_woke_up_resource' => "Woke up resource ID ",
	'ipmi_not_configured' => "IPMI for resource not configured. Skipping wakeup!",
	'ipmi_updated_configuration' => "Updated IPMI configuration of resource.",
	'ipmi_added_configuration' => "Added IPMI configuration for resource.",
	'ipmi_disabled' => "IPMI disabled for resource.",
	'ipmi_update' => "Update",
	'ipmi_sleep' => "Sleep",
	'ipmi_enable' => "Enable",
	'ipmi_disable' => "Disable",
	'ipmi_set_resource_to_sleep' => "Set resource to power-save.",
	'ipmi_enabled_resource' => "Enabled IPMI configuration for resource ID ",
	'ipmi_disabled_resource' => "Disabled IPMI configuration for resource ID ",


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
		$this->response = $response;
		$this->htvcenter  = $htvcenter;
		$this->user     = $this->htvcenter->user();
		$this->identifier_name = "ipmi_ident";
		$this->rootdir = $this->htvcenter->get('webdir');
		$this->basedir  = $this->htvcenter->get('basedir');
		require_once($this->basedir.'/plugins/ipmi/web/class/ipmi.class.php');
		$this->ipmi = new ipmi();
		$this->tpldir   = $this->rootdir.'/plugins/ipmi/tpl';

		require_once $this->basedir."/web/base/class/event.class.php";
		require_once $this->basedir."/web/base/class/resource.class.php";
		require_once $this->basedir."/web/base/class/htvcenter_server.class.php";
		require_once $this->basedir."/web/base/include/htvcenter-server-config.php";
		$this->htvcenter_base_dir = $this->basedir;
		$htvcenter_server = new htvcenter_server();
		$this->htvcenter_server = $htvcenter_server;
		$this->htvcenter_ip = $htvcenter_server->get_ip_address();
		$this->event = new event();
		$this->htvcenter->lc();
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
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
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
			case 'update':
				$content[] = $this->select(false);
				$content[] = $this->update(true);
			case 'wakeup':
				$content[] = $this->select(false);
				$content[] = $this->wakeup(true);
			case 'sleep':
				$content[] = $this->select(false);
				$content[] = $this->sleep(true);
			case 'enable':
				$content[] = $this->select(false);
				$content[] = $this->enable(true);
			case 'disable':
				$content[] = $this->select(false);
				$content[] = $this->disable(true);

		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Select a resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ipmi/class/ipmi.select.class.php');
			$controller = new ipmi_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->ipmi            = $this->ipmi;
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
		$content['label']   = $this->lang['ipmi_configuration'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Update a resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ipmi/class/ipmi.update.class.php');
			$controller = new ipmi_update($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/ipmi/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->ipmi            = $this->ipmi;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
		$content['label']   = $this->lang['ipmi_update'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Wakeup a resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function wakeup( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ipmi/class/ipmi.wakeup.class.php');
			$controller = new ipmi_wakeup($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/ipmi/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->ipmi            = $this->ipmi;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
		$content['label']   = $this->lang['ipmi_wakeup'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'wakeup' );
		$content['onclick'] = false;
		if($this->action === 'wakeup'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Send resource to sleep
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function sleep( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ipmi/class/ipmi.sleep.class.php');
			$controller = new ipmi_sleep($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/ipmi/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->ipmi            = $this->ipmi;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
		$content['label']   = $this->lang['ipmi_sleep'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'sleep' );
		$content['onclick'] = false;
		if($this->action === 'sleep'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Enable IPMI for resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function enable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ipmi/class/ipmi.enable.class.php');
			$controller = new ipmi_enable($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/ipmi/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->ipmi            = $this->ipmi;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
		$content['label']   = $this->lang['ipmi_enable'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'enable' );
		$content['onclick'] = false;
		if($this->action === 'enable'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Disable IPMI for resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function disable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ipmi/class/ipmi.disable.class.php');
			$controller = new ipmi_disable($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/ipmi/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->ipmi            = $this->ipmi;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/ipmi/lang", 'ipmi.ini');
		$content['label']   = $this->lang['ipmi_disable'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'disable' );
		$content['onclick'] = false;
		if($this->action === 'disable'){
			$content['active']  = true;
		}
		return $content;
	}



}
?>
