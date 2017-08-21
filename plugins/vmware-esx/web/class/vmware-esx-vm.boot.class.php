<?php
/**
 * Toggles the VMs Boot sequence
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_vm_boot
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_id';
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
		$this->htvcenter = $htvcenter;
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('vm_bootorder', $this->response->html->request()->get('vm_bootorder'));
		$this->response->add('vm_name', $this->response->html->request()->get('vm_name'));
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		if($appliance_id === '') {
			return false;
		}
		// set ENV
		$virtualization = new virtualization();
		$appliance	= new appliance();
		$resource	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
		$this->vmware_mac_base = "00:50:56";
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
		$this->init();
		$response = $this->vm_boot();
		if(!isset($response->msg)) {
			$response->msg = "Default: Setting VM boot order";
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
		);
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm_boot() {
		$response		= $this->get_response();
		$form			= $response->form;
		$vm_name		= $this->response->html->request()->get('vm_name');
		$vm_bootorder	= $this->response->html->request()->get('vm_bootorder');

		switch ($vm_bootorder) {
			case 'network':
				$new_vm_boot_order = "network";
				break;
			case 'local':
				$new_vm_boot_order = "local";
				break;
			case 'cdrom':
				$new_vm_boot_order = "cdrom";
				break;
			default:
				$new_vm_boot_order = "net";
				break;
		}

		$command  = $this->htvcenter->get('basedir')."/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm setboot -i ".$this->resource->ip." -n ".$vm_name." -b ".$new_vm_boot_order;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		if(file_exists($this->statfile_vm)) {
			unlink($this->statfile_vm);
		}
		$htvcenter_server = new htvcenter_server();
		$htvcenter_server->send_command($command, NULL, true);
		while (!file_exists($this->statfile_vm)) {
			usleep(10000); // sleep 10ms to unload the CPU
			clearstatcache();
		}
		$response->msg = "Setting the boot sequence for VM ".$vm_name." to ".$new_vm_boot_order;
		return $response;
	}



	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'boot');
		$response->form = $form;
		return $response;
	}
	
}
?>
