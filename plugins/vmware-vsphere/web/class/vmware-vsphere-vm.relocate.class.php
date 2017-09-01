<?php
/**
 * vSphere Hosts Update VM
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vm_relocate
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_id';
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
		$this->response->add('vm_name', $this->response->html->request()->get('vm_name'));
		$this->response->add('vm_name', $this->response->html->request()->get('vm_name'));
		$this->response->add('datastore', $this->response->html->request()->get('datastore'));
		$this->response->add('resourcepool', $this->response->html->request()->get('resourcepool'));
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
		$vm_name = $this->response->html->request()->get('vm_name');
		if($vm_name === '') {
			return false;
		}
		$datastore = $this->response->html->request()->get('datastore');
		if($datastore === '') {
			return false;
		}
		$resourcepool = $this->response->html->request()->get('resourcepool');
		if($resourcepool === '') {
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
		$this->vmware_mac_base = "00:50:56";
		$this->vm_name = $vm_name;
		$this->datastore = $datastore;
		$this->resourcepool = $resourcepool;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_list';
		$this->statfile_vm_components = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_components';
		$this->statfile_vm_config = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.'.$this->vm_name.'.vm_config';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.ds_list';
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
		$response = $this->vm_relocate();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vm-relocate.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm_name), 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_location'], 'lang_location');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->actions_name, 'actions_name');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update VM
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm_relocate() {

		$this->reload_vm_relocate_config();
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $this->response->submit()) {
			$name			= $form->get_static('name');
			$newresourcepool	= $form->get_request('newresourcepool');
			$newdatastore		= $form->get_request('newdatastore');

			// checks
			if (file_exists($this->statfile_vm)) {
				$error = sprintf($this->lang['error_not_exist'], $name);
				$lines = explode("\n", file_get_contents($this->statfile_vm));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							if($name === $line['name']) {
								unset($error);
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_vm)) {
					unlink($this->statfile_vm);
				}

				// send command to relocate the vm
				$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm relocate";
				$command .= " -i ".$this->resource->ip;
				$command .= " -n ".$name;
				$command .= " --resourcepool ".$newresourcepool;
				if ($newdatastore != $this->datastore) {
					$command .= " -l ".$newdatastore;
				}
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';

				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, NULL, true);

/*				while (!file_exists($this->statfile_vm)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
  */
				$response->msg = sprintf($this->lang['msg_relocated'], $name);
			}
		}
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
		$form = $response->get_form($this->actions_name, 'relocate');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$datastore_select_arr = array();
		$rspool_select_arr =  array();
		// get the datastore and vswitchlist for the selects
		if (file_exists($this->statfile_vm_components)) {
			$lines = explode("\n", file_get_contents($this->statfile_vm_components));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = $this->htvcenter->string_to_array($line, '|', '=');
						switch ($line['t']) {
							case 'ds':
								$datastore_select_arr[] = array($line['name'],$line['name']);
								break;
							case 'rs':
								$rspool_select_arr[] = array($line['name'],$line['name']);
								break;
						}
					}
				}
			}
		}

		// get the current config
		$vm_configuration = array();
		if (file_exists($this->statfile_vm_config)) {
			$lines = explode("\n", file_get_contents($this->statfile_vm_config));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$vm_configuration = $this->htvcenter->string_to_array($line, '|', '=');
					}
				}
			}
		}
		
		$d['name']['label']										= $this->lang['form_name'];
		$d['name']['static']									= true;
		$d['name']['object']['type']							= 'htmlobject_input';
		$d['name']['object']['attrib']['name']					= 'vm_name';
		$d['name']['object']['attrib']['type']					= 'text';
		$d['name']['object']['attrib']['value']					= $this->vm_name;
		$d['name']['object']['attrib']['disabled']				= true;

		$d['newresourcepool']['label']							= $this->lang['form_resourcepool'];
		$d['newresourcepool']['object']['type']					= 'htmlobject_select';
		$d['newresourcepool']['object']['attrib']['index']		= array(0,1);
		$d['newresourcepool']['object']['attrib']['id']			= 'newresourcepool';
		$d['newresourcepool']['object']['attrib']['name']		= 'newresourcepool';
		$d['newresourcepool']['object']['attrib']['options']	= $rspool_select_arr;
		$d['newresourcepool']['object']['attrib']['selected']	= array($this->resourcepool);

		$d['newdatastore']['label']							= $this->lang['form_datastore'];
		$d['newdatastore']['object']['type']				= 'htmlobject_select';
		$d['newdatastore']['object']['attrib']['index']		= array(0,1);
		$d['newdatastore']['object']['attrib']['id']		= 'newdatastore';
		$d['newdatastore']['object']['attrib']['name']		= 'newdatastore';
		$d['newdatastore']['object']['attrib']['options']	= $datastore_select_arr;
		$d['newdatastore']['object']['attrib']['selected']	= array($this->datastore);
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}


	//--------------------------------------------
	/**
	 * Reload VM configuration
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_vm_relocate_config() {
		$command  = $this->htvcenter->get('basedir').'/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm post_vm_config';
		$command .=  ' -i '.$this->resource->ip;
		$command .=  ' -n '.$this->vm_name;
		if($this->file->exists($this->statfile_vm_config)) {
			$this->file->remove($this->statfile_vm_config);
		}
		$htvcenter_server = new htvcenter_server();
		$htvcenter_server->send_command($command, NULL, true);
		while (!$this->file->exists($this->statfile_vm_config)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}



}
?>
