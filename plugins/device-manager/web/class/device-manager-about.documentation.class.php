<?php
/**
 * device-manager-about Documentation
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class device_manager_about_documentation
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'device_manager_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'device_manager_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'device_manager_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'device_manager_about_identifier';
/**
* path to device-managers
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
		$this->htvcenter    = $htvcenter;
		$this->basedir    = $this->htvcenter->get('basedir');
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_device-manager
	 */
	//--------------------------------------------
	function action() {
		$svn_co_command = "";
		$t = $this->response->html->template($this->tpldir.'/device-manager-about-documentation.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['type_title'], 'type_title');
		$t->add($this->lang['type_content'], 'type_content');
		$t->add($this->lang['tested_title'], 'tested_title');
		$t->add($this->lang['tested_content'], 'tested_content');
		$t->add($this->lang['introduction_title'], 'introduction_title');
		$t->add($this->lang['introduction_content'], 'introduction_content');
		$t->add($this->lang['requirements_title'], 'requirements_title');
		$t->add($this->lang['requirements_list'], 'requirements_list');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		return $t;
	}


}
?>
