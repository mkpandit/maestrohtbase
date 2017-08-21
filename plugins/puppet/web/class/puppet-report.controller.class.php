<?php
/**
 * Puppet Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


class puppet_report_controller {
	
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
	var $lang = array(
		'report' => array(
			'tab' => 'Report Puppet',
			'label' => 'Puppet Report',
			'introduction_title' => 'Introduction',
			'introduction_content' => 'The Puppet plugin provides automated configuration-management for Appliances in htvcenter.
				It seamlessly integrates <a href="http://projects.puppetlabs.com/projects/puppet" target="_BLANK">Puppet</a> within the htvcenter GUI and assists to put specific Appliances into pre-made or custom Puppet-classes.
				By enabling the plugin the puppet-environment (server and client) is pre-configured and initialyzed automatically according to best-practice experiences e.g. by keeping the puppet-configuration within a svn-repsitory.
				The puppet-configuration is organized in "classes", "goups" and "appliances".
				Custom custom classes can be added to the class-directory.
				Classes should be combined to "groups" these will be automatically displayed in the puppet-plugin.
				The puppet-configuration repository is also available for external svn clients.
				To check out the puppet-repo please run<br><br>:',
			'introduction_title1' => 'Assigning Applications to Appliances',
			'introduction_content1' => '<ol><li>Go to the "Appliances" in the puppet-plugin menu</li>
				<li>Select an Appliance to be configured via puppet</li>
				<li>Select the puppet-groups the appliance should belong to</li></ol>
				Within short time the puppet-server will distribute the new configuration to the Appliance automatically.',
			/*'requirements_title' => 'Requirements',
			'requirements_list' => '<li>The Puppet Plugin depends on the DNS Plugin! Please make sure to have the DNS Plugin enabled and started before.</li>',
			'tested_title' => 'Tested with',
			'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
			'provides_title' => 'Provides',
			'provides_list' => '<li>Automated configuration-management for Appliances in htvcenter</li>',
			'type_title' => 'Plugin Type',
			'type_content' => 'Deployment',
			'documentation_title' => 'Documentation',
			'use_case_title' => 'Use-Case', */
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
			$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/puppet/lang", 'puppet-report.ini');
			$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';
			
			require_once($this->htvcenter->get('basedir').'/plugins/puppet/web/class/puppetconfig.class.php');
			$this->puppetconfig = new puppetconfig();
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
			$t = $this->response->html->template($this->tpldir.'/puppet-report.tpl.php');
			
			$t->add($this->response->html->thisfile, "thisfile");
			
			$t->add($this->lang['report']['label'], 'label');
			//$t->add($this->lang['type_title'], 'type_title');
			//$t->add($this->lang['type_content'], 'type_content');
			//$t->add($this->lang['tested_title'], 'tested_title');
			//$t->add($this->lang['tested_content'], 'tested_content');
			//$t->add($this->lang['provides_title'], 'provides_title');
			//$t->add($this->lang['provides_list'], 'provides_list');
			$t->add($this->lang['report']['introduction_title'], 'introduction_title');
			$t->add($this->lang['report']['introduction_content'], 'introduction_content');
			
			$t->add($svn_co_command, 'introduction_command');
			$t->add($this->randomString(), 'random_string');
			
			$p_config = "HTBase - " . $this->puppetconfig->get_count();
			$t->add($p_config, 'counted_config');
			
			
			$t->add($this->lang['report']['introduction_title1'], 'introduction_title1');
			$t->add($this->lang['report']['introduction_content1'], 'introduction_content1');
			//$t->add($this->lang['requirements_title'], 'requirements_title');
			//$t->add($this->lang['requirements_list'], 'requirements_list');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');
			return $t;
		}
		
		function randomString($length = 10) {
			
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return "HTBase - " . $randomString;
		}
}
?>
