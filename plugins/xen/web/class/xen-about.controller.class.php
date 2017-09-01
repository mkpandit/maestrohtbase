<?php
/**
 * xen-about Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'xen_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'xen_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xen_about_identifier';
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
		'tab' => 'About Xen',
		'label' => 'About Xen',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "Xen" plugin manages Xen Virtual Machines and their belonging virtual disk.
					   As common in htvcenter the Virtual Machines and their virtual disk volumes are managed separately.
					   Therefore the "Xen" plugin splits up into VM- and Volume-Management.
					   The VM part provides the Virtual Machines which are abstracted as "resources".
					   The Storage part provides volumes which are abstracted as "images".
					   Appliance deployment automatically combines "resource" and "image".',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<ul><li>A resource for the Xen Host Appliance<br>(this can be a remote system integrated into htvcenter e.g. via the "local-server" plugin or the htvcenter server itself)</li>
				   <li>The server needs VT (Virtualization Technology) Support in its CPU (requirement for Xen)</li>
				   <li>The following packages must be installed: xen (eventual xen-pxe), socat, bridge-utils, lvm2</li>
				   <li>For Xen LVM Storage: One (or more) lvm volume group(s) with free space dedicated for the Xen VM storage</li>
				   <li>For Xen Blockfile Storage: free space dedicated for the Xen VM storage</li>
				   <li>One or more bridges configured for the virtual machines</li></ul>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with Xen 4',

		'provides_title' => 'Provides',
		'provides_list' => '<ul><li>Virtualization types: "Xen Host" and "Xen VM"</li>
				   <li>Storage types: "Xen LVM Storage" and "Xen Blockfile Storage"</li>
				   <li>Deployment types: "LVM deployment for Xen" and "Blockfile deployment for Xen"</li></ul>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization and Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Local Deployment for Virtual Machines',

		'migration_title' => 'Requirements for Xen live-migration',
		'migration_content' => 'Shared storage between the Xen Hosts for the location of the VM config files (/var/lib/xen/htvcenter)
					and a shared LVM volume group between the Xen Hosts',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'Xen Boot-Service',
		'boot_service_title' => 'Xen Host Boot-Service',
		'boot_service_content' => 'The Xen Plugin provides an htvcenter Boot-Service.
			This "Xen Boot-Service" is automatically downloaded and executed by the htvcenter-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/htvcenter/plugins/xen/web/boot-service-xen.tgz</b></i>
			<br>
			<br>
			The "Xen Boot-Service" contains the Client files of the Xen Plugin.<br>
			Also a configuration file for the Xen Hosts is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "htvcenter" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service view -n xen -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service view -n xen -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service configure -n xen -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service configure -n xen -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the Xen Host please edit:<br>
			<br>
				<i><b>/usr/share/htvcenter/plugins/xen/etc/htvcenter-plugin-xen.conf</b></i>
			<br>
			<br>
			and set the configuration keys according to your bridge-configuration.<br>
			<br>
			',
	),
	'storage' => array (
		'tab' => 'About Xen',
		'label' => 'About Storage in Xen',
		'storage_mgmt_title' => 'Xen Storage Management',
		'storage_mgmt_list' => '<ol><li>Create a new storage from type "Xen LVM Storage" or "Xen Blockfile Storage"</li>
				   <li>Create a new Volume on this storage (either LVM or Blockfile)</li>
				   <li>Creating the Volume automatically creates a new Image using volume as root-device</li></ol>',

	),
	'vms' => array (
		'tab' => 'About Xen',
		'label' => 'About Virtual Machines in Xen',
		'vm_mgmt_title' => 'Xen VM Management',
		'vm_mgmt_list' => '<ol><li>Create a new appliance and set its resource type to "Xen Host"</li>
				   <li>Create and manage Xen virtual machines via the Xen VM Manager</li></ol>',
	),
	'usage' => array (
		'tab' => 'About Xen',
		'label' => 'Xen Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/xen/lang", 'xen-about.ini');


		$this->tpldir   = $this->rootdir.'/plugins/xen/tpl';
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
			case 'bootservice':
				$content[] = $this->bootservice(true);
			break;
			case 'storage':
				$content[] = $this->storage(true);
			break;
			case 'vms':
				$content[] = $this->vms(true);
			break;
			case 'usage':
				$content[] = $this->usage(true);
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
	 * About Xen
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen/class/xen-about.documentation.class.php');
			$controller = new xen_about_documentation($this->htvcenter, $this->response);
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


	//--------------------------------------------
	/**
	 * Boot-Service
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function bootservice( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen/class/xen-about.bootservice.class.php');
			$controller = new xen_about_bootservice($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['bootservice'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['bootservice']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'bootservice' );
		$content['onclick'] = false;
		if($this->action === 'bootservice'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * About Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function storage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen/class/xen-about.storage.class.php');
			$controller = new xen_about_storage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['storage'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['storage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'storage' );
		$content['onclick'] = false;
		if($this->action === 'storage'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * About Xen VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen/class/xen-about.vms.class.php');
			$controller = new xen_about_vms($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['vms'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['vms']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vms' );
		$content['onclick'] = false;
		if($this->action === 'vms'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * About Xen Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen/class/xen-about.usage.class.php');
			$controller = new xen_about_usage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['usage'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['usage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'usage' );
		$content['onclick'] = false;
		if($this->action === 'usage'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
