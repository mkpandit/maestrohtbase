<?php
/**
 * Wake-up on lan Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class wakeuponlan_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'wol_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'wol_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'wol_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'wol_identifier';
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
	'wakeuponlan_configuration' => 'WOL Manager',
	'wakeuponlan_wakeup' => 'Wakeup',
	'wakeuponlan_title' => 'WOL Configuration',
	'wakeuponlan_add_resources' => 'Please add physical resources first!',
	'wakeuponlan_id' => 'ID',
	'wakeuponlan_mac' => 'MAC Adress',
	'wakeuponlan_ip' => 'IP Adress',
	'wakeuponlan_user' => 'User',
	'wakeuponlan_password' => 'Password',
	'wakeuponlan_comment' => 'Comment',
	'wakeuponlan_actions' => 'Actions',
	'wakeuponlan_woke_up_resource' => "Woke up resource ID ",
	'wakeuponlan_not_configured' => "WOL for resource not configured. Skipping wakeup!",
	'wakeuponlan_updated_configuration' => "Updated WOL configuration of resource.",
	'wakeuponlan_added_configuration' => "Added WOL configuration for resource.",
	'wakeuponlan_disabled' => "WOL disabled for resource.",
	'wakeuponlan_update' => "Update",
	'wakeuponlan_sleep' => "Sleep",
	'wakeuponlan_enable' => "Enable",
	'wakeuponlan_disable' => "Disable",
	'wakeuponlan_set_resource_to_sleep' => "Set resource to power-save.",
	'wakeuponlan_enabled_resource' => "Enabled WOL configuration for resource ID ",
	'wakeuponlan_disabled_resource' => "Disabled WOL configuration for resource ID ",
	'wakeuponlan_name' => "Name",
	'wakeuponlan_ip' => "IP-address",
	'wakeuponlan_type' => "Type",
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
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->basedir  = $this->htvcenter->get('basedir');
		require_once $this->basedir."/plugins/wakeuponlan/web/class/wakeuponlan.class.php";
		$this->wakeuponlan = new wakeuponlan();

		require_once $this->basedir."/web/base/class/event.class.php";
		require_once $this->basedir."/web/base/class/resource.class.php";
		require_once $this->basedir."/web/base/class/htvcenter_server.class.php";
		require_once $this->basedir."/web/base/include/htvcenter-server-config.php";
		$this->htvcenter_base_dir = $this->htvcenter->get('basedir');
		$htvcenter_server = new htvcenter_server();
		$this->htvcenter_server = $htvcenter_server;
		$this->htvcenter_ip = $htvcenter_server->get_ip_address();
		$event = new event();
		$this->event = $event;
		$this->tpldir   = $this->rootdir.'/plugins/wakeuponlan/tpl';
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
	 * Select resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/wakeuponlan/class/wakeuponlan.select.class.php');
			$controller = new wakeuponlan_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->wakeuponlan = $this->wakeuponlan;
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
		$content['label']   = $this->lang['wakeuponlan_configuration'];
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
	 * Wakeup resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function wakeup( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/wakeuponlan/class/wakeuponlan.wakeup.class.php');
			$controller = new wakeuponlan_wakeup($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/wakeuponlan/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
			$controller->wakeuponlan = $this->wakeuponlan;
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
		$content['label']   = $this->lang['wakeuponlan_wakeup'];
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
			require_once($this->rootdir.'/plugins/wakeuponlan/class/wakeuponlan.sleep.class.php');
			$controller = new wakeuponlan_sleep($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/wakeuponlan/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
			$controller->wakeuponlan = $this->wakeuponlan;
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
		$content['label']   = $this->lang['wakeuponlan_sleep'];
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
	 * Enable
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function enable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/wakeuponlan/class/wakeuponlan.enable.class.php');
			$controller = new wakeuponlan_enable($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/wakeuponlan/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
			$controller->wakeuponlan = $this->wakeuponlan;
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
		$content['label']   = $this->lang['wakeuponlan_enable'];
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
	 * Disable
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function disable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/wakeuponlan/class/wakeuponlan.disable.class.php');
			$controller = new wakeuponlan_disable($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->rootdir.'/plugins/wakeuponlan/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->htvcenter_base_dir = $this->htvcenter_base_dir;
			$controller->htvcenter_server = $this->htvcenter_server;
			$controller->htvcenter_ip = $this->htvcenter_ip;
			$controller->event = $this->event;
			$controller->wakeuponlan = $this->wakeuponlan;
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
			$data = $controller->action();
//			$this->response->html->help($data);
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/wakeuponlan/lang", 'wakeuponlan.ini');
		$content['label']   = $this->lang['wakeuponlan_disable'];
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
