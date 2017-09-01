<?php
/**
 * kvm-about Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kvm_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_about_identifier';
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
		'tab' => 'About KVM',
		'label' => 'About KVM',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "KVM" plugin manages KVM Virtual Machines and their belonging virtual disk.
					   As common in htvcenter the Virtual Machines and their virtual disk volumes are managed separately.
					   Therefore the "KVM" plugin splits up into VM- and Volume-Management.
					   The VM part provides the Virtual Machines which are abstracted as "Resources".
					   The Storage part provides volumes which are abstracted as "Images".
					   Appliance deployment automatically combines "Resource" and "Image".',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<ul><li>A resource for the KVM Host Appliance<br>(this can be a remote system integrated into htvcenter e.g. via the "local-server" plugin or the htvcenter server itself)</li>
				   <li>The server needs VT (Virtualization Technology) Support in its CPU (requirement for KVM)</li>
				   <li>The following packages must be installed: kvm (eventual kvm-pxe), socat, bridge-utils, lvm2</li>
				   <li>For KVM LVM Storage: One (or more) lvm volume group(s) with free space dedicated for the KVM VM storage</li>
				   <li>For KVM Blockfile Storage: free space dedicated for the KVM VM storage</li>
				   <li>For KVM Gluster Storage: One or more Gluster Storage Cluster</li>
				   <li>One or more bridges configured for the virtual machines</li></ul>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with KVM Version kvm-62 or better<br>To benefit from the KVM "virtio" feature at least kvm-84 is needed',

		'provides_title' => 'Provides',
		'provides_list' => '<ul><li>Virtualization types: "KVM Host", "KVM VM (localboot)" and "KVM VM (netboot)"</li>
				   <li>Storage types: "KVM LVM Storage", "KVM Blockfile Storage" and "KVM Gluster Storage"</li>
				   <li>Deployment types: "LVM deployment for KVM", "Blockfile deployment for KVM" and "Gluster deployment for KVM"</li></ul>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization and Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Local and Network Deployment for Virtual Machines',

		'migration_title' => 'Requirements for KVM live-migration',
		'migration_content' => 'Shared storage between the KVM Hosts for the location of the VM config files (/var/lib/kvm/htvcenter)
					and a shared LVM volume group between the KVM Hosts',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'KVM Boot-Service',
		'boot_service_title' => 'KVM Host Boot-Service',
		'boot_service_content' => 'The KVM Plugin provides an htvcenter Boot-Service.
			This "KVM Boot-Service" is automatically downloaded and executed by the htvcenter-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/htvcenter/plugins/kvm/web/boot-service-kvm.tgz</b></i>
			<br>
			<br>
			The "KVM Boot-Service" contains the Client files of the KVM Plugin.<br>
			Also a configuration file for the KVM Hosts is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "htvcenter" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service view -n kvm -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service view -n kvm -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service configure -n kvm -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/htvcenter/bin/htvcenter boot-service configure -n kvm -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the KVM Host please edit:<br>
			<br>
				<i><b>/usr/share/htvcenter/plugins/kvm/etc/htvcenter-plugin-kvm.conf</b></i>
			<br>
			<br>
			and set the configuration keys according to your bridge-configuration.<br>
			<br>
			',
	),
	'storage' => array (
		'tab' => 'About KVM',
		'label' => 'About Storage in KVM',
		'storage_mgmt_title' => 'KVM Storage Management',
		'storage_mgmt_list' => '<ul>li>Create a new storage from type "KVM LVM Storage" or "KVM Blockfile Storage"</li>
				   <li>Create a new Volume on this storage (either LVM or Blockfile)</li>
				   <li>Creating the Volume automatically creates a new Image using volume as root-device</li></ul>',

	),
	'vms' => array (
		'tab' => 'About KVM',
		'label' => 'About Virtual Machines in KVM',
		'vm_mgmt_title' => 'KVM VM Management',
		'vm_mgmt_list' => '<ul><li>Create a new appliance and set its resource type to "KVM Host"</li>
				   <li>Create and manage KVM virtual machines via the KVM VM Manager</li></ul>',
	),
	'usage' => array (
		'tab' => 'About KVM',
		'label' => 'KVM Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/kvm/lang", 'kvm-about.ini');


		$this->tpldir   = $this->rootdir.'/plugins/kvm/tpl';
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
	 * About KVM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.documentation.class.php');
			$controller = new kvm_about_documentation($this->htvcenter, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.bootservice.class.php');
			$controller = new kvm_about_bootservice($this->htvcenter, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.storage.class.php');
			$controller = new kvm_about_storage($this->htvcenter, $this->response);
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
	 * About KVM VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.vms.class.php');
			$controller = new kvm_about_vms($this->htvcenter, $this->response);
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
	 * About KVM Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.usage.class.php');
			$controller = new kvm_about_usage($this->htvcenter, $this->response);
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
