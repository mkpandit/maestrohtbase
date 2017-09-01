<?php
/**
 * hybrid-cloud-about Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hybrid_cloud_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'hybrid_cloud_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_about_identifier';
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
		'tab' => 'About Hybrid-Cloud',
		'label' => 'About Hybrid-Cloud',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'TThe hybrid-cloud-plugin provides a seamless migration-path "from" and "to" Public-Cloud Providers such as Amazone EC2, Ubuntu Enterprise Cloud and Eucalyptus.',

		'introduction_title1' => 'Configure Hybrid-Cloud Account',
		'introduction_content1' => 'Create a new Hybrid-Cloud Account configuration using the "Migration" menu item.<br>
				The following informations are required :
				<ul>
					<li>Hybrid-Cloud Account Name</li>
					<li>rc-config (file)</li>
					<li>SSH-Key (file)</li>
					<li>Description</li>
				</ul>
				The rc-config file is typically provided by the Public-Cloud Provider. This rc-config file (installed on htvcenter at e.g. /home/cloud/.eucarc) should define all parameters for the public cloud tools (e.g. ec2-ami-tools, ec2-api-tools or euca2ools) to work seamlessly.<br>
				A typical rc-config file for UEC looks similar to <a href="/htvcenter/base/plugins/hybrid-cloud/hybrid-cloud-example-rc-config.php" title="A sample rc-config file containing the Cloud Account configuration" target="_blank">this</a>.<br><br>
				The Cloud ssh-key (on htvcenter at e.g. /home/cloud/.euca/mykey.priv) provides the console login to the Public Cloud systems.',

		'introduction_title2' => 'Import Servers from Hybrid-Cloud',
		'introduction_content2' => 'To import an Cloud Server (-> the AMI of an active EC2 Instance) follow the steps below :<br>
				<ol>
					<li>Select an Hybrid-Cloud Account to use for import</li>
					<li>Select an active Public-Cloud Instance running the AMI to import</li>
					<li>Select an (empty) htvcenter Server image (from type NFS- or LVM-NFS)</li>
				</ol>
				This will automatically import the AMI from the selcted Public-Cloud Instance into the (previously created) empty Server Image in htvcenter.<br><br>
				The imported AMI now can be used with all existing "resource-types" in htvcenter so e.g. it can now also run on a physical system or on any other virtulization type.',

		'introduction_title3' => 'Export Servers to Hybrid-Cloud',
		'introduction_content3' => 'To export an htvcenter Image to a Public-Cloud Server as an AMI follow the steps below :
				<ol>
					<li>Select an Hybrid-Cloud Account to use for the export</li>
					<li>Select the Image (from type NFS- or LVM-NFS) to turn into an AMI for the export</li>
					<li>Provide a name for the AMI, its size and architecture</li>
				</ol>
				This will automatically export the selected htvcenter Image to the Public-Cloud Provider.<br>
				It will be available as new AMI as soon as the transfer procedure is finished.',

		'introduction_title4' => '',
		'introduction_content4' => '',

		'introduction_title5' => '',
		'introduction_content5' => '',

		'introduction_title6' => '',
		'introduction_content6' => '',

		'requirements_title' => 'Requirements',
		'requirements_list' => '<ul><li>none</li></ul>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
		
		'type_title' => 'Plugin Type',
		'type_content' => 'Deployment',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
	),

	'usage' => array (
		'tab' => 'About Hybrid-Cloud',
		'label' => 'Hybrid-Cloud Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/hybrid-cloud/lang", 'hybrid-cloud-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/hybrid-cloud/tpl';
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
		if($this->response->cancel()) {
			$this->action = "documentation";
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
	 * About Nagios
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hybrid-cloud/class/hybrid-cloud-about.documentation.class.php');
			$controller = new hybrid_cloud_about_documentation($this->htvcenter, $this->response);
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
