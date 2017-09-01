<?php
/**
 * Role-administration Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


class role_administration_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'role_administration_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'role_administration_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'role_administration_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'role_administration_about_identifier';
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
	'documentation' => array (
		'tab' => 'About Role-administration',
		'label' => 'About Role-administration',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The Role-administration plugin provides role based user administration.
			One role can be added to each user and n permission groups can be added to each role. In case permission group A and permission
			 group B have differnt permissions on e.g. AOE (Group A: select, edit. Group B: clone, resize) permissions will be merged
			 (User permissions on AOE: select, edit, clone, resize). There is no need to set permission groups for administrator role, permissions will be set automatically.
			 User administration and role-administration plugin is restricted to administrator role only. Documentation is visible for all users.<br><br>
			Setting up permissions:
			<ol><li>Create a permission group (Permissions) and set permissions</li>
			<li>Create a role (Roles) and add permission groups</li>
			<li>Go to user administration, select and edit a user to add a role</li>
			</ol>',
		'requirements_title' => 'Requirements',
		'requirements_list' => 'none',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with Debian, Ubuntu and CentOS Linux distributions.',
		'type_title' => 'Plugin Type',
		'type_content' => 'Enterprise',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/role-administration/lang", 'role-administration-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/role-administration/tpl';
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
		$content = array();
		switch( $this->action ) {
			case '':
			case 'documentation':
				$content[] = $this->documentation(true);
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
	 * About Role-administration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/role-administration/class/role-administration-about.documentation.class.php');
			$controller = new role_administration_about_documentation($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['documentation'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['documentation']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'documentation' );
		$content['onclick'] = false;
		if($this->action === 'documentation'){
			$content['active']  = true;
		}
		return $content;
	}



}
?>
