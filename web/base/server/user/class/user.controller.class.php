<?php
/**
 * User Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class user_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'user_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'user_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'user_tab';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;

/**
* lang
* @access public
* @var string
*/
var $lang = array(
	'label' => 'User Administration',
	'label_users' => 'Users',
	'label_account' => 'Account',
	'label_edit' => 'Edit %s',
	'label_delete' => 'Delete user(s)',
	'name' => 'Name',
	'role' => 'Role',
	'lang' => 'Lang',
	'forename' => 'Forename',
	'lastname' => 'Surname',
	'gender' => 'Gender',
	'male' => 'male',
	'female' => 'female',
	'state' => 'State',
	'activated' => 'activated',
	'deactivated' => 'deactivated',
	'state' => 'State',
	'office' => 'Office',
	'department' => 'Department',
	'description' => 'Description',
	'capabilities' => 'Capabilities',
	'password' => 'Password',
	'password_repeat' => 'Password (repeat)',
	'action_delete' => 'delete',
	'action_edit' => 'edit',
	'action_new' => 'New User',
	'msg_saved' => 'User %s has been saved',
	'msg_deleted' => 'User %s has been deleted',
	'error_no_match' => '%s does not match %s',
	'error_user_exists' => 'User %s already exists',
	'error_name' => 'string must be a-z0-9_-',
	'please_wait' => 'Loading. Please wait ...',
	'canceled' => 'Operation canceled. Please wait ...',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->user     = $htvcenter->get('user');
		$this->file     = $htvcenter->get('file');
		$this->response = $response;
		$this->classdir = $htvcenter->get('webdir').'/server/user/class/';
		$this->tpldir   = $htvcenter->get('webdir').'/server/user/tpl/';
		$this->lang     = $this->user->translate($this->lang, $htvcenter->get('webdir')."/server/user/lang", 'user.ini');
		$this->htvcenter = $htvcenter;
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
		if($this->action === '' || $this->user->isAdmin() === false ) {
			$this->action = 'account';
		}

		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'account':
				$content[] = $this->account();
				if($this->user->isAdmin() === true) {
					$content[] = $this->users(true);
				}
			break;
			case 'users':
				$content[] = $this->account(true);
				if($this->user->isAdmin() === true) {
					$content[] = $this->users();
				}
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
	 * Users
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function users( $hidden = false ) {
		$data = '';
		if( $hidden === false && (strtolower($this->user->role) === 'administrator' || $this->user->role === 0) ) {
			require_once($this->classdir.'/user.users.class.php');
			$controller = new user_users($this->htvcenter, $this->response);
			$controller->actions_name = 'users_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->prefix_tab = $this->prefix_tab;
			$controller->lang = $this->lang;
			$data = $controller->action();	
		}
		$content['label']   = $this->lang['label_users'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'users' );
		$content['onclick'] = false;
		if($this->action === 'users'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Account
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function account( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once($this->classdir.'/user.users.class.php');
			$controller = new user_users($this->htvcenter, $this->response);
			$controller->actions_name = 'users_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->prefix_tab = $this->prefix_tab;
			$controller->lang = $this->lang;
			$data = $controller->account();	
		}
		$content['label']   = $this->lang['label_account'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'account' );
		$content['onclick'] = false;
		if($this->action === 'account'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
