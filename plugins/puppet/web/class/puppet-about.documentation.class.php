<?php
/**
 * puppet-about Documentation
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class puppet_about_documentation
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'puppet_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "puppet_about_msg";
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
		$this->htvcenter    = $htvcenter;
		$this->basedir    = $this->htvcenter->get('basedir');
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
		$svn_co_command = "svn co svn+ssh://[user]@[htvcenter-server-ip]".$this->basedir."/htvcenter/plugins/puppet/etc/puppet/";
		$t = $this->response->html->template($this->tpldir.'/puppet-about-documentation.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['type_title'], 'type_title');
		$t->add($this->lang['type_content'], 'type_content');
		$t->add($this->lang['tested_title'], 'tested_title');
		$t->add($this->lang['tested_content'], 'tested_content');
		$t->add($this->lang['provides_title'], 'provides_title');
		$t->add($this->lang['provides_list'], 'provides_list');
		$t->add($this->lang['introduction_title'], 'introduction_title');
		$t->add($this->lang['introduction_content'], 'introduction_content');
		$t->add($svn_co_command, 'introduction_command');
		$t->add($this->lang['introduction_title1'], 'introduction_title1');
		$t->add($this->lang['introduction_content1'], 'introduction_content1');
		$t->add($this->lang['requirements_title'], 'requirements_title');
		$t->add($this->lang['requirements_list'], 'requirements_list');
		$t->add($this->lang['create_node_title'], 'create_node_title');
		$t->add($this->lang['create_node_content'], 'create_node_content');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		return $t;
	}


}
?>
