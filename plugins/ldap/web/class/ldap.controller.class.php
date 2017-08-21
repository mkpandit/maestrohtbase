<?php
/**
 * LDAP Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class ldap_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ldap_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "ldap_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ldap_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'ldap_identifier';
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
	'update' => array(
		'tab' => 'LDAP',
		'title' => 'LDAP settings',
		'enabled' => 'enabled',
		'check' => 'Check LDAP settings',
		'msg_updated' => 'Settings updated successfully',
		'please_wait' => 'Loading. Please wait.',
	),
	'check' => array(
		'msg_users' => 'Found %s users. LDAP configured successfully',
		'msg_gid' => 'Found htvcenter gid %s. LDAP configured successfully',
		'error_gid' => 'Error: Could not find the htvcenter gid',
		'error_users' => 'Error: Could not find users on LDAP',
		'error_bind' => 'Error: Could not connect to LDAP. Please check your settings'
	),
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
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/ldap/lang", 'ldap.ini');
		$this->tpldir   = $this->rootdir.'/plugins/ldap/tpl';
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
		$this->action = 'update';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'update':
				$content[] = $this->update(true);
			break;
			case 'check':
				$content[] = $this->update(false);
				$content[] = $this->check(true);
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
	 * Update LDAP settings
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ldap/class/ldap.update.class.php');
			$controller = new ldap_update($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['update'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['update']['tab'];
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
	 * Check LDAP settings
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function check( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ldap/class/ldap.check.class.php');
			$controller = new ldap_check($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['check'];
			$data = $controller->action();
		}
		$content['label']   = '';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'check' );
		$content['onclick'] = false;
		if($this->action === 'check'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
