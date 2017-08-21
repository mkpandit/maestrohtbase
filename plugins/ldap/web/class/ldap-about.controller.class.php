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

class ldap_about_controller
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
	'tab' => 'About LDAP',
	'label' => 'About LDAP',
	'introduction_title' => 'Introduction',
	'introduction_content' => 'The LDAP-plugin connects htvcenter and its cloud-fortis for user-authentication to your LDAP server.',
	'provides_title' => 'Provides',
	'provides_list' => '<ul><li>LDAP user-authentication.</li></ul>',
	'requirements_title' => 'Requirements',
	'requirements_list' => '<ol>
								<li>install openldap</li>
								<li>migrate the htvcenter system accounts to the ldap server</li>
								<li>cloud users will be synced</li>
								<li>htvcenter users will be synced</li>
								<li>if they are in the htvcenter group they will be created as administrator</li>
							</ol>',
	'type_title' => 'Plugin Type',
	'type_content' => 'Enterprise',
	'tested_title' => 'Tested with',
	'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/ldap/lang", 'ldap-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/ldap/tpl';
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
		$t = $this->response->html->template($this->tpldir."/ldap-about.tpl.php");
		$t->add($this->lang);
		$t->add($this->htvcenter->get('baseurl'), "baseurl");

		$content['label']   = $this->lang['tab'];
		$content['value']   = $t->get_string();
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'about' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add(array($content));
		return $tab;
	}

}
?>
