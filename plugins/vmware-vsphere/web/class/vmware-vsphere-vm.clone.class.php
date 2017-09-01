<?php
/**
 * vSphere Hosts Clone VM
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vm_clone
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
		$this->response->add('vm_id', $this->response->html->request()->get('vm_id'));
		$this->response->add('vm_mac', $this->response->html->request()->get('vm_mac'));
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
		$vm_name = $this->response->html->request()->get('vm_name');
		if($vm_name === '') {
			return false;
		}
		$vm_mac = $this->response->html->request()->get('vm_mac');
		if($vm_mac === '') {
			return false;
		}
		$vm_id = $this->response->html->request()->get('vm_id');
		if($vm_id === '') {
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
		$this->vm_mac = $vm_mac;
		$this->vm_id = $vm_id;
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
		$response = $this->vm_clone();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vm-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm_name), 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_net_0'], 'lang_net_0');
		$t->add($this->lang['lang_net_1'], 'lang_net_1');
		$t->add($this->lang['lang_net_2'], 'lang_net_2');
		$t->add($this->lang['lang_net_3'], 'lang_net_3');
		$t->add($this->lang['lang_net_4'], 'lang_net_4');
		$t->add($this->lang['lang_boot'], 'lang_boot');
		$t->add($this->lang['lang_virtual_disk'], 'lang_virtual_disk');
		$t->add($this->lang['lang_browser'], 'lang_browser');
		$t->add($this->lang['lang_password_generate'], 'lang_password_generate');
		$t->add($this->lang['lang_password_show'], 'lang_password_show');
		$t->add($this->lang['lang_password_hide'], 'lang_password_hide');
		$t->add($this->lang['lang_vnc'], 'lang_vnc');
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
	function vm_clone() {

		$this->reload_vm_config();
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $this->response->submit()) {
			$name			= $form->get_static('name');
			$mac			= $form->get_request('mac');
			$vm_id			= $this->response->html->request()->get('vm_id');
			$vswitch		= $form->get_request('vswitch');
			$type			= $form->get_request('type');
			$vnc			= $form->get_request('vnc');
			$vncport		= $form->get_request('vncport');
			$vncserverip	= $form->get_request('vncserverip');
			$clone_name		= $form->get_request('clone_name');
			$resourcepool	= $form->get_request('resourcepool');
			$datastore		= $form->get_request('datastore');
			
			if(!$form->get_errors()) {

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
					// additional network cards
					// remove spaces from vswitch parameters
					$vswitch = str_replace(" ", "@", $vswitch);

					// create the vnc port
					$vncport_parameter = '';
					$vncport_resource_parameter = '';
					if ($vncport > 5900) {
						$vncport_parameter = " -vp ".$vncport;
						$vncport = $vncport - 5900;
					}
					
					// create clone resource
					$vm_resource_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$vm_resource_ip = "0.0.0.0";
					// send command to the HyperTask-server
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command("htvcenter_server_add_resource ".$vm_resource_id." ".$mac." ".$vm_resource_ip);
					$virtualization = new virtualization();
					$virtualization->get_instance_by_type("vmware-vsphere-vm-local");
					$vmtype = "vmware-vsphere-vm-local";
					// add to HyperTask database
					$resource_fields["resource_id"] = $vm_resource_id;
					$resource_fields["resource_ip"] = $vm_resource_ip;
					$resource_fields["resource_mac"] = $mac;
					$resource_fields["resource_kernel"] = 'default';
					$resource_fields["resource_kernelid"] = 1;
					$resource_fields["resource_image"] = 'idle';
					$resource_fields["resource_imageid"] = 1;
					$resource_fields["resource_localboot"] = 0;
					$resource_fields["resource_hostname"] = $clone_name;
					$resource_fields["resource_vtype"] = $virtualization->id;
					$resource_fields["resource_vhostid"] = $this->resource->id;
					$resource_fields["resource_vname"] = $clone_name;
					$vm_resource = new resource();
					$vm_resource->add($resource_fields);
					$vm_resource->get_instance_by_id($vm_resource_id);
					
					// send command to create the vm
					$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm clone";
					$command .= " -i ".$this->resource->ip;
					$command .= " -n ".$clone_name;
					$command .= " --vm-template ".$name;
					$command .= " -l ".$datastore;
					$command .= " --resourcepool ".$resourcepool;
					$command .= " -m ".$mac;
					$command .= " -t ".$type;
					$command .= " -v ".$vswitch;
					$command .= " -va ".$vnc;
					$command .= $vncport_parameter;
					$command .= ' --htvcenter-ui-user '.$this->user->name;
					$command .= ' --htvcenter-cmd-mode fork';
					
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command($command, NULL, true);
					while (!file_exists($this->statfile_vm)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					$vm_configuration = array();
					if (file_exists($this->statfile_vm)) {
						$lines = explode("\n", file_get_contents($this->statfile_vm));
						if(count($lines) >= 1) {
							foreach($lines as $line) {
								if($line !== '') {
									$line = $this->htvcenter->string_to_array($line, '|', '=');
									if($name === $line['name']) {
										$vm_configuration = $line;
										$resource_vnc_fields["resource_vnc"] = $vm_configuration['hostip'].":".$vm_configuration['vncport'];
										$vm_resource->update_info($vm_resource_id, $resource_vnc_fields);
										$vm_resource->get_instance_by_id($vm_resource_id);
									}
								}
							}
						}
					}
					
					// create image object for vmware-vsphere-vm-local deployment
					$deployment = new deployment();
					$deployment->get_instance_by_name('vsphere-deployment');
					if($vmtype === 'vmware-vsphere-vm-local') {
						$storage = new storage();
						$vmware_vsphere_id_list = $storage->get_ids_by_storage_type($deployment->id);
						$found_vmware_vsphere = false;
						$found_vmware_vsphere_id = -1;
						foreach ($vmware_vsphere_id_list as $list) {
							foreach ($list as $vmware_vsphere_id) {
								$storage->get_instance_by_id($vmware_vsphere_id);
								if ($storage->resource_id == $this->resource->id) {
									$found_vmware_vsphere = true;
									$found_vmware_vsphere_id = $storage->id;
									break;
								}
							}
						}
						if ($found_vmware_vsphere) {
							$image = new image();
							$image_fields = array();
							$vm_image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
							$image_fields["image_id"] = $vm_image_id;
							$image_fields['image_name'] = $clone_name;
							$image_fields['image_type'] = 'vsphere-deployment';
							$image_fields['image_rootfstype'] = 'local';
							$image_fields['image_isactive']=0;
							$image_fields['image_storageid'] = $found_vmware_vsphere_id;
							$image_fields['image_comment'] = "Image Object for vmdk $clone_name";
							$image_fields['image_rootdevice'] = $datastore.':'.$clone_name.'/'.$clone_name.'.vmdk';
							$image->add($image_fields);
							$image->get_instance_by_id($vm_image_id);
							
							$now=$_SERVER['REQUEST_TIME'];
							if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
								// update appliance for this VM if we are coming from the wizard
								$afields['appliance_resources'] = $vm_resource_id;
								$afields['appliance_kernelid'] = '1';
								$afields['appliance_imageid'] = $image->id;
								$afields["appliance_virtual"]= 0;
								$afields["appliance_virtualization"]=$virtualization->id;
								$afields['appliance_wizard'] = '';
								$afields['appliance_comment'] = 'VMware vSphere VM for resource '.$vm_resource_id;
								$afields['appliance_stoptime']=$now;
								$afields['appliance_starttime']='';
								$afields['appliance_state']='stopped';
								$this->appliance->update($this->user->wizard_id, $afields);
							} else {
								// auto create the appliance for this VM if we are not coming from the wizard
								$appliance_name = str_replace("_", "-", strtolower(trim($clone_name)));
								$new_appliance_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
								$afields['appliance_id'] = $new_appliance_id;
								$afields['appliance_name'] = $appliance_name;
								$afields['appliance_resources'] = $vm_resource_id;
								$afields['appliance_kernelid'] = '1';
								$afields['appliance_imageid'] = $image->id;
								$afields["appliance_virtual"]= 0;
								$afields["appliance_virtualization"]=$virtualization->id;
								$afields['appliance_wizard'] = '';
								$afields['appliance_comment'] = 'VMware vSphere VM for resource '.$vm_resource_id;
								$this->appliance->add($afields);
								// update state/start+stoptime
								$aufields['appliance_stoptime']=$now;
								$aufields['appliance_starttime']='';
								$aufields['appliance_state']='stopped';
								$this->appliance->update($new_appliance_id, $aufields);
							}
						}
					}
					$response->msg = sprintf($this->lang['msg_cloned'], $name);
				}
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
		$form = $response->get_form($this->actions_name, 'clone');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$datastore_select_arr = array();
		$vswitch_select_arr =  array();
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
							case 'vs':
								$vswitch_select_arr[] = array($line['name'],$line['name']);
								break;
							case 'rs':
								$rspool_select_arr[] = array($line['name'],$line['name']);
								break;
						}
					}
				}
			}
		}

		$type_select_arr[] = array('e1000','Intel E1000');
		$type_select_arr[] = array('pcnet','PCNet 32');
		$type_select_arr[] = array('vmxnet3','VMX');

	
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

		// debug
		// print_r($vm_configuration);

		// genenrate mac
		$vm_resource = new resource();
		$vm_resource = $vm_resource->get_instance_by_id($this->vm_id);
		$vm_resource->generate_mac();
		$vm_mac = $this->verify_vmware_vsphere_mac_clone($vm_resource->mac);
		
		$vnc_port_suggestion = $this->resource->generate_vnc_port($this->resource->id);
		$vnc_port_suggestion = $vnc_port_suggestion + 5900;
		
		$d['name']['label']										= $this->lang['form_name'];
		$d['name']['static']									= true;
		$d['name']['object']['type']							= 'htmlobject_input';
		$d['name']['object']['attrib']['name']					= 'vm_name';
		$d['name']['object']['attrib']['type']					= 'text';
		$d['name']['object']['attrib']['value']					= $this->vm_name;
		$d['name']['object']['attrib']['disabled']				= true;

		$d['clone_name']['label']								= $this->lang['form_clone_name'];
		$d['clone_name']['required']							= true;
		$d['clone_name']['object']['type']						= 'htmlobject_input';
		$d['clone_name']['object']['attrib']['name']			= 'clone_name';
		$d['clone_name']['object']['attrib']['type']			= 'text';
		$d['clone_name']['object']['attrib']['value']			= '';
		$d['clone_name']['object']['attrib']['maxlength']		= 50;

		$d['mac']['label']							= $this->lang['form_mac'];
		$d['mac']['required']						= true;
		$d['mac']['object']['type']					= 'htmlobject_input';
		$d['mac']['object']['attrib']['id']			= 'mac';
		$d['mac']['object']['attrib']['name']		= 'mac';
		$d['mac']['object']['attrib']['type']		= 'text';
		$d['mac']['object']['attrib']['value']		= $vm_mac;
		$d['mac']['object']['attrib']['maxlength']	= 50;

		$d['type']['label']							= $this->lang['form_type'];
		$d['type']['object']['type']				= 'htmlobject_select';
		$d['type']['object']['attrib']['index']		= array(0,1);
		$d['type']['object']['attrib']['id']		= 'type';
		$d['type']['object']['attrib']['name']		= 'type';
		$d['type']['object']['attrib']['options']	= $type_select_arr;

		$d['vswitch']['label']						= $this->lang['form_vswitch'];
		$d['vswitch']['object']['type']				= 'htmlobject_select';
		$d['vswitch']['object']['attrib']['index']	= array(0,1);
		$d['vswitch']['object']['attrib']['id']		= 'vswitch';
		$d['vswitch']['object']['attrib']['name']	= 'vswitch';
		$d['vswitch']['object']['attrib']['options']= $vswitch_select_arr;
		
		$virtualization = $this->htvcenter->virtualization();
		$virtualization->get_instance_by_id($vm_resource->vtype);
		$vmtype = $virtualization->type;

		$d['vm_id']['label']									= ' ';
		$d['vm_id']['static']									= true;
		$d['vm_id']['object']['type']							= 'htmlobject_input';
		$d['vm_id']['object']['attrib']['name']					= 'vm_id';
		$d['vm_id']['object']['attrib']['type']					= 'hidden';
		$d['vm_id']['object']['attrib']['value']				= $this->vm_id;
		$d['vm_id']['object']['attrib']['maxlength']			= 50;

		$d['resourcepool']['label']							= $this->lang['form_resourcepool'];
		$d['resourcepool']['object']['type']				= 'htmlobject_select';
		$d['resourcepool']['object']['attrib']['index']		= array(0,1);
		$d['resourcepool']['object']['attrib']['id']		= 'resourcepool';
		$d['resourcepool']['object']['attrib']['name']		= 'resourcepool';
		$d['resourcepool']['object']['attrib']['options']	= $rspool_select_arr;

		$d['datastore']['label']						= $this->lang['form_datastore'];
		$d['datastore']['object']['type']				= 'htmlobject_select';
		$d['datastore']['object']['attrib']['index']	= array(0,1);
		$d['datastore']['object']['attrib']['id']		= 'datastore';
		$d['datastore']['object']['attrib']['name']		= 'datastore';
		$d['datastore']['object']['attrib']['options']	= $datastore_select_arr;
		
		$d['vnc']['label']							= $this->lang['form_vnc'];
		$d['vnc']['required']						= true;
		$d['vnc']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vnc']['validate']['errormsg']			= sprintf($this->lang['error_vnc'], 'a-z0-9._-');
		$d['vnc']['object']['type']					= 'htmlobject_input';
		$d['vnc']['object']['attrib']['id']			= 'vnc';
		$d['vnc']['object']['attrib']['name']		= 'vnc';
		$d['vnc']['object']['attrib']['type']		= 'password';
		$d['vnc']['object']['attrib']['value']		= $vm_configuration['vncpass'];
		$d['vnc']['object']['attrib']['maxlength']	= 50;

		$d['vncport']['label']							= $this->lang['form_vncport'];
		$d['vncport']['required']						= true;
		$d['vncport']['validate']['regex']				= '/^[0-9]+$/i';
		$d['vncport']['validate']['errormsg']			= sprintf($this->lang['error_vnc'], '0-9');
		$d['vncport']['object']['type']					= 'htmlobject_input';
		$d['vncport']['object']['attrib']['id']			= 'vncport';
		$d['vncport']['object']['attrib']['name']		= 'vncport';
		$d['vncport']['object']['attrib']['type']		= 'text';
		$d['vncport']['object']['attrib']['value']		= $vnc_port_suggestion;
		$d['vncport']['object']['attrib']['maxlength']	= 50;

 		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Nic Type as shortname
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function translate_nic_type($nic_type) {
		switch ($nic_type) {
			case 'VirtualE1000':
				$translated_nic_type = "e1000";
				break;
			case 'VirtualPCNet32':
				$translated_nic_type = "pcnet";
				break;
			case 'VirtualVmxnet':
				$translated_nic_type = "vmxnet3";
				break;
			default:
				$translated_nic_type = "e1000";
				break;
		}
		return $translated_nic_type;
	}


	//--------------------------------------------
	/**
	 * Reload VM configuration
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_vm_config() {
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

	//--------------------------------------------
	/**
	 * verifies VMware compatible MAC addresses
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function verify_vmware_vsphere_mac_clone($mac) {
		$new_forth_byte_first_bit = rand(1, 3);
		$mac = strtolower($this->vmware_mac_base.":".substr($mac, 9));
		$mac = substr_replace($mac , $new_forth_byte_first_bit, 9, 1);
		return $mac;
	}


}
?>
