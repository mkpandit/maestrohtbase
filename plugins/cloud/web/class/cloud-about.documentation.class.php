<?php
/**
 * Cloud Documentation About
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/



class cloud_about_documentation
{
var $tpldir;
var $lang;
var $actions_name = 'cloud_documentation';


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->webdir  = $this->htvcenter->get('webdir');
		$this->rootdir  = $this->htvcenter->get('basedir');
		$this->baseurl  = $this->htvcenter->get('baseurl');
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
	}

	//--------------------------------------------
	/**
	 * Action About
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
	    $external_portal_name = $this->cloud_config->get_value(3);  // 3 is the external name;
	    if (!strlen($external_portal_name)) {
		    $htvcenter_server = new htvcenter_server();
		    $htvcenter_server_ip = $htvcenter_server->get_ip_address();
		    $external_portal_name = "http://".$htvcenter_server_ip."/cloud-fortis";
	    }
	    $template = $this->response->html->template($this->tpldir."/cloud-documentation-about.tpl.php");
	    $template->add($this->lang['cloud_documentation_title'], 'title');
	    $template->add($this->lang['cloud_documentation_intro'], 'cloud_documentation_intro');
	    $template->add($this->lang['cloud_documentation_label'], 'cloud_documentation_label');
	    $template->add($this->lang['cloud_documentation_setup'], 'cloud_documentation_setup');
	    $template->add($this->lang['cloud_documentation_setup_title'], 'cloud_documentation_setup_title');
   	    $template->add($this->lang['cloud_documentation_setup_steps'], 'cloud_documentation_setup_steps');
	    $template->add($this->lang['cloud_documentation_users'], 'cloud_documentation_users');
	    $template->add($this->lang['cloud_documentation_create_user'], 'cloud_documentation_create_user');
	    $template->add($this->lang['cloud_documentation_ip_management'], 'cloud_documentation_ip_management');
	    $template->add($this->lang['cloud_documentation_ip_management_setup'], 'cloud_documentation_ip_management_setup');
		$template->add($this->lang['cloud_documentation_type_title'], 'cloud_documentation_type_title');
		$template->add($this->lang['cloud_documentation_type_content'], 'cloud_documentation_type_content');
		$template->add($this->lang['cloud_documentation_tested_title'], 'cloud_documentation_tested_title');
		$template->add($this->lang['cloud_documentation_tested_content'], 'cloud_documentation_tested_content');
	    $template->add($this->lang['cloud_documentation_api'], 'cloud_documentation_api');
	    $template->add($this->lang['cloud_documentation_soap'], 'cloud_documentation_soap');
	    $template->add($this->lang['cloud_documentation_lockfile'], 'cloud_documentation_lockfile');
	    $template->add(sprintf($this->lang['cloud_documentation_lockfile_details'], $this->rootdir."/web/action/cloud-conf/cloud-monitor.lock"), 'cloud_documentation_lockfile_details');
	    $template->add($this->baseurl, 'baseurl');
	    $template->add($external_portal_name, 'external_portal_name');
	    $template->add($this->response->html->thisfile, "thisfile");
	    $template->group_elements(array('param_' => 'form'));
	    return $template;
	}

}


?>
