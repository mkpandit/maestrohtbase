<?php
/**
 * ESX Hosts Update VM
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_vm_update
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
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_vm_components = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_components';
		$this->statfile_vm_config = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.'.$this->vm_name.'.vm_config';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
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
		$response = $this->vm_update();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm-update.tpl.php');
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
	function vm_update() {

		$this->reload_vm_config();
		$response = $this->get_response();
		$form     = $response->form;
		// iso
		$iso_path = '';
		if($form->get_request('boot') !== '' && $form->get_request('boot') === 'iso') {
			if($form->get_request('iso_path') === '') {
				$form->set_error('iso_path', $this->lang['error_iso_path']);
			} else {
				$iso_path = ' -iso '.$form->get_request('iso_path');
			}
		}

		if(!$form->get_errors() && $this->response->submit()) {
			$name			= $form->get_static('name');
			$mac			= $form->get_static('mac');
			$vm_id			= $this->response->html->request()->get('vm_id');
			$vswitch		= $form->get_request('vswitch');
			$type			= $form->get_request('type');
			$memory			= $form->get_request('memory');
			$cpu			= $form->get_request('cpu');
			$vnc			= $form->get_request('vnc');
			$bootorder		= $form->get_request('boot');
			// TODO Update guest id if possible
			//$guest_id		= $form->get_request('guestid');

			// handle additional nics
			$enabled = array();
			for($i = 1; $i < 5; $i++) {
				$enabled[$i] = true;
				if($form->get_request('net'.$i) !== '') {
					if($form->get_request('mac'.$i) === '') {
						$form->set_error('mac'.$i, $this->lang['error_mac']);
						$enabled[$i] = false;
					}
					if($form->get_request('type'.$i) === '') {
						$form->set_error('type'.$i, $this->lang['error_nic']);
						$enabled[$i] = false;
					}
					if($form->get_request('vswitch'.$i) === '') {
						$form->set_error('vswitch'.$i, $this->lang['error_bridge']);
						$enabled[$i] = false;
					}
				} else {
					$enabled[$i] = false;
				}
			}
			if(!$form->get_errors()) {

				// checks
				if (file_exists($this->statfile_vm)) {
					$error = sprintf($this->lang['error_not_exist'], $name);
					$lines = explode("\n", file_get_contents($this->statfile_vm));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								if($name === $line[0]) {
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
					$vm_resource = new resource();
					$vm_resource->get_instance_by_mac($mac);
					if ($vm_resource->vnc == '') {
						$vnc_port = $vm_resource->generate_vnc_port($this->resource->id);
						$resource_fields["resource_vname"] = $name;
						$resource_fields["resource_vnc"] = $vnc_port;
						$vm_resource->update_info($vm_resource->id, $resource_fields);
					} else {
						$vnc_port = $vm_resource->vnc;
					}
					// send command to create the vm
					$command  = $this->htvcenter->get('basedir')."/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm update";
					$command .= " -i ".$this->resource->ip;
					$command .= " -n ".$name;
					$command .= " -m ".$mac;
					$command .= " -t ".$type;
					$command .= " -v ".$vswitch;
					$command .= " -r ".$memory;
					$command .= " -c ".$cpu;
					$command .= " -va ".$vnc;
					$command .= " -vp ".$vnc_port;
					$command .= " -b ".$bootorder." ".$iso_path;
					//$command .= " --guest-id ".$guest_id;
					$command .= ' --htvcenter-ui-user '.$this->user->name;
					$command .= ' --htvcenter-cmd-mode background';
					
					$i = 1;
					foreach($enabled as $key => $value) {
						if($value === true) {
							$command .= ' -m'.($i).' '.$form->get_request('mac'.$key);
							$command .= ' -t'.($i).' '.$form->get_request('type'.$key);
							$command .= ' -v'.($i).' '.str_replace(" ", "@", $form->get_request('vswitch'.$key));
							$i++;
						}
					}
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command($command, NULL, true);
					while (!file_exists($this->statfile_vm)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					$response->msg = sprintf($this->lang['msg_updated'], $name);
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
		$form = $response->get_form($this->actions_name, 'update');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$datastore_select_arr = array();
		$vswitch_select_arr =  array();
		// get the datastore list for the select
		if (file_exists($this->statfile_vm_components)) {
			$lines = explode("\n", file_get_contents($this->statfile_vm_components));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						switch ($line[0]) {
							case 'ds':
								$datastore_select_arr[] = array($line[1]);
								break;
							case 'vs':
								$vswitch_select_arr[] = array($line[1]);
								break;
						}
					}
				}
			}
		}

		// get the current config
		$vm_configuration = array();
		if (file_exists($this->statfile_vm_config)) {
			$vm_configuration = htvcenter_parse_conf($this->statfile_vm_config);
		}

		// debug
		// print_r($vm_configuration);

		// genenrate mac
		$vm_resource = new resource();
		$vm_resource = $vm_resource->get_instance_by_id($this->vm_id);
		if (strlen($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC2'])) {
			$mac1 = $vm_configuration['htvcenter_VMWARE_ESX_VM_MAC2'];
		} else {
			$vm_resource->generate_mac();
			$mac1 = $this->verify_vmware_esx_mac_update($vm_resource->mac);
		}
		if (strlen($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC3'])) {
			$mac2 = $vm_configuration['htvcenter_VMWARE_ESX_VM_MAC3'];
		} else {
			$vm_resource->generate_mac();
			$mac2 = $this->verify_vmware_esx_mac_update($vm_resource->mac);
		}
		if (strlen($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC4'])) {
			$mac3 = $vm_configuration['htvcenter_VMWARE_ESX_VM_MAC4'];
		} else {
			$vm_resource->generate_mac();
			$mac3 = $this->verify_vmware_esx_mac_update($vm_resource->mac);
		}
		if (strlen($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC5'])) {
			$mac4 = $vm_configuration['htvcenter_VMWARE_ESX_VM_MAC5'];
		} else {
			$vm_resource->generate_mac();
			$mac4 = $this->verify_vmware_esx_mac_update($vm_resource->mac);
		}

		$disk_select_arr[] = array('1048576','1 GB');
		$disk_select_arr[] = array('2097152','2 GB');
		$disk_select_arr[] = array('10485760','10 GB');
		$disk_select_arr[] = array('20971520','20 GB');
		$disk_select_arr[] = array('52428800','50 GB');
		$disk_select_arr[] = array('104857600','100 GB');

		$swap_select_arr[] = array('1048576', '1 GB');
		$swap_select_arr[] = array('2097152','2 GB');

		$memory_select_arr[] = array('512','512 MB');
		$memory_select_arr[] = array('1024','1 GB');
		$memory_select_arr[] = array('2048','2 GB');
		$memory_select_arr[] = array('4096','4 GB');
		$memory_select_arr[] = array('8192','8 GB');
		$memory_select_arr[] = array('16384','16 GB');

		$cpu_select_arr[] = array('1','1 CPU');
		$cpu_select_arr[] = array('2','2 CPUs');
		$cpu_select_arr[] = array('4','4 CPUs');
		$cpu_select_arr[] = array('8','8 CPUs');
		$cpu_select_arr[] = array('16','16 CPUs');

		$type_select_arr[] = array('e1000','Intel E1000');
		$type_select_arr[] = array('pcnet','PCNet 32');
		$type_select_arr[] = array('vmxnet3','VMX');

		$d['name']['label']										= $this->lang['form_name'];
		$d['name']['static']									= true;
		$d['name']['object']['type']							= 'htmlobject_input';
		$d['name']['object']['attrib']['name']					= 'vm_name';
		$d['name']['object']['attrib']['type']					= 'text';
		$d['name']['object']['attrib']['value']					= $this->vm_name;
		$d['name']['object']['attrib']['disabled']				= true;

		$d['memory']['label']									= $this->lang['form_memory'];
		$d['memory']['object']['type']							= 'htmlobject_select';
		$d['memory']['object']['attrib']['index']				= array(0,1);
		$d['memory']['object']['attrib']['id']					= 'memory';
		$d['memory']['object']['attrib']['name']				= 'memory';
		$d['memory']['object']['attrib']['options']				= $memory_select_arr;
		$d['memory']['object']['attrib']['selected']			= array($vm_configuration['htvcenter_VMWARE_ESX_VM_RAM']);

		$d['cpu']['label']										= $this->lang['form_cpu'];
		$d['cpu']['object']['type']								= 'htmlobject_select';
		$d['cpu']['object']['attrib']['index']					= array(0,1);
		$d['cpu']['object']['attrib']['id']						= 'cpu';
		$d['cpu']['object']['attrib']['name']					= 'cpu';
		$d['cpu']['object']['attrib']['options']				= $cpu_select_arr;
		$d['cpu']['object']['attrib']['selected']				= array($vm_configuration['htvcenter_VMWARE_ESX_VM_CPUS']);

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

		$d['net0']['label']                        = $this->lang['lang_net_0'];
		$d['net0']['object']['type']               = 'htmlobject_input';
		$d['net0']['object']['attrib']['type']     = 'checkbox';
		$d['net0']['object']['attrib']['id']       = 'net0';
		$d['net0']['object']['attrib']['name']     = 'net0';
		$d['net0']['object']['attrib']['value']    = 'enabled';
		$d['net0']['object']['attrib']['checked']  = true;
		$d['net0']['object']['attrib']['disabled'] = true;

		$d['mac']['label']										= $this->lang['form_mac'];
		$d['mac']['static']										= true;
		$d['mac']['object']['type']								= 'htmlobject_input';
		$d['mac']['object']['attrib']['id']						= 'mac';
		$d['mac']['object']['attrib']['name']					= 'mac';
		$d['mac']['object']['attrib']['type']					= 'text';
		$d['mac']['object']['attrib']['value']					= $this->vm_mac;
		$d['mac']['object']['attrib']['disabled']				= true;

		$d['type']['label']										= $this->lang['form_type'];
		$d['type']['object']['type']							= 'htmlobject_select';
		$d['type']['object']['attrib']['index']					= array(0,1);
		$d['type']['object']['attrib']['id']					= 'type';
		$d['type']['object']['attrib']['name']					= 'type';
		$d['type']['object']['attrib']['options']				= $type_select_arr;
		$d['type']['object']['attrib']['selected']				= array($vm_configuration['htvcenter_VMWARE_ESX_VM_NIC_TYPE']);

		$d['vswitch']['label']									= $this->lang['form_vswitch'];
		$d['vswitch']['object']['type']							= 'htmlobject_select';
		$d['vswitch']['object']['attrib']['index']				= array(0,0);
		$d['vswitch']['object']['attrib']['id']					= 'vswitch';
		$d['vswitch']['object']['attrib']['name']				= 'vswitch';
		$d['vswitch']['object']['attrib']['options']			= $vswitch_select_arr;
		$d['vswitch']['object']['attrib']['selected']			= array($vm_configuration['htvcenter_VMWARE_ESX_VM_VSWITCH']);

		$d['vnc']['label']										= $this->lang['form_vnc'];
		$d['vnc']['required']									= true;
		$d['vnc']['validate']['regex']							= '/^[a-z0-9._-]+$/i';
		$d['vnc']['validate']['errormsg']						= sprintf($this->lang['error_vnc'], 'a-z0-9._-');
		$d['vnc']['object']['type']								= 'htmlobject_input';
		$d['vnc']['object']['attrib']['id']  					= 'vnc';
		$d['vnc']['object']['attrib']['name']					= 'vnc';
		$d['vnc']['object']['attrib']['type']					= 'password';
		$d['vnc']['object']['attrib']['value']					= $vm_configuration['htvcenter_VMWARE_ESX_VM_VNC_PASSWORD'];
		$d['vnc']['object']['attrib']['maxlength']				= 50;

/*
		$gids = $this->file->get_contents($this->htvcenter->get('basedir').'/plugins/vmware-esx/conf/vmware-guest-ids.csv');
		$gids = explode("\n",$gids);
		$guestids = array();
		foreach($gids as $k => $gid) {
			$tmp = explode("\t",$gid);
			if(isset($tmp[1])) {
				$guestids[$k][0] = $tmp[0];
				$guestids[$k][1] = $tmp[1];
			}
		} 
		$d['guestid']['label']						  = 'Guest Id';
		$d['guestid']['object']['type']				  = 'htmlobject_select';
		$d['guestid']['object']['attrib']['index']	  = array(0,1);
		$d['guestid']['object']['attrib']['id']		  = 'guestid';
		$d['guestid']['object']['attrib']['name']	  = 'guestid';
		$d['guestid']['object']['attrib']['options']  = $guestids;
		$d['guestid']['object']['attrib']['selected'] = array($vm_configuration['htvcenter_VMWARE_ESX_VM_GUEST_ID']);
*/

		// Net 1
		$d['net1']['label']                     = $this->lang['lang_net_1'];
		$d['net1']['object']['type']            = 'htmlobject_input';
		$d['net1']['object']['attrib']['type']  = 'checkbox';
		$d['net1']['object']['attrib']['name']  = 'net1';
		$d['net1']['object']['attrib']['id']    = 'net1';
		$d['net1']['object']['attrib']['value'] = 'enabled';
		$d['net1']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';
		if($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC2'] !== '') {
			$d['net1']['object']['attrib']['checked'] = true;
		}

		$d['mac1']['label']								= $this->lang['form_mac'];
		$d['mac1']['object']['type']					= 'htmlobject_input';
		$d['mac1']['object']['attrib']['id']			= 'mac1';
		$d['mac1']['object']['attrib']['name']			= 'mac1';
		$d['mac1']['object']['attrib']['type']			= 'text';
		$d['mac1']['object']['attrib']['value']			= $mac1;
		$d['mac1']['object']['attrib']['maxlength']		= 50;

		$d['type1']['label']							= $this->lang['form_type'];
		$d['type1']['object']['type']					= 'htmlobject_select';
		$d['type1']['object']['attrib']['index']		= array(0,1);
		$d['type1']['object']['attrib']['id']			= 'type1';
		$d['type1']['object']['attrib']['name']			= 'type1';
		$d['type1']['object']['attrib']['options']		= $type_select_arr;
		$d['type1']['object']['attrib']['selected']		= array($vm_configuration['htvcenter_VMWARE_ESX_VM_NIC_TYPE2']);

		$d['vswitch1']['label']							= $this->lang['form_vswitch'];
		$d['vswitch1']['object']['type']				= 'htmlobject_select';
		$d['vswitch1']['object']['attrib']['index']		= array(0,0);
		$d['vswitch1']['object']['attrib']['id']		= 'vswitch1';
		$d['vswitch1']['object']['attrib']['name']		= 'vswitch1';
		$d['vswitch1']['object']['attrib']['options']	= $vswitch_select_arr;
		$d['vswitch1']['object']['attrib']['selected']	= array($vm_configuration['htvcenter_VMWARE_ESX_VM_VSWITCH2']);

		// Net 2

		$d['net2']['label']                     = $this->lang['lang_net_2'];
		$d['net2']['object']['type']            = 'htmlobject_input';
		$d['net2']['object']['attrib']['type']  = 'checkbox';
		$d['net2']['object']['attrib']['name']  = 'net2';
		$d['net2']['object']['attrib']['id']    = 'net2';
		$d['net2']['object']['attrib']['value'] = 'enabled';
		$d['net2']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';
		if($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC3'] !== '') {
			$d['net2']['object']['attrib']['checked'] = true;
		}

		$d['mac2']['label']								= $this->lang['form_mac'];
		$d['mac2']['object']['type']					= 'htmlobject_input';
		$d['mac2']['object']['attrib']['id']			= 'mac2';
		$d['mac2']['object']['attrib']['name']			= 'mac2';
		$d['mac2']['object']['attrib']['type']			= 'text';
		$d['mac2']['object']['attrib']['value']			= $mac2;
		$d['mac2']['object']['attrib']['maxlength']		= 50;

		$d['type2']['label']							= $this->lang['form_type'];
		$d['type2']['object']['type']					= 'htmlobject_select';
		$d['type2']['object']['attrib']['index']		= array(0,1);
		$d['type2']['object']['attrib']['id']			= 'type2';
		$d['type2']['object']['attrib']['name']			= 'type2';
		$d['type2']['object']['attrib']['options']		= $type_select_arr;
		$d['type2']['object']['attrib']['selected']		= array($vm_configuration['htvcenter_VMWARE_ESX_VM_NIC_TYPE3']);

		$d['vswitch2']['label']							= $this->lang['form_vswitch'];
		$d['vswitch2']['object']['type']				= 'htmlobject_select';
		$d['vswitch2']['object']['attrib']['index']		= array(0,0);
		$d['vswitch2']['object']['attrib']['id']		= 'vswitch2';
		$d['vswitch2']['object']['attrib']['name']		= 'vswitch2';
		$d['vswitch2']['object']['attrib']['options']	= $vswitch_select_arr;
		$d['vswitch2']['object']['attrib']['selected']	= array($vm_configuration['htvcenter_VMWARE_ESX_VM_VSWITCH3']);

		// Net 3

		$d['net3']['label']                     = $this->lang['lang_net_3'];
		$d['net3']['object']['type']            = 'htmlobject_input';
		$d['net3']['object']['attrib']['type']  = 'checkbox';
		$d['net3']['object']['attrib']['name']  = 'net3';
		$d['net3']['object']['attrib']['id']    = 'net3';
		$d['net3']['object']['attrib']['value'] = 'enabled';
		$d['net3']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';
		if($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC4'] !== '') {
			$d['net3']['object']['attrib']['checked'] = true;
		}

		$d['mac3']['label']								= $this->lang['form_mac'];
		$d['mac3']['object']['type']					= 'htmlobject_input';
		$d['mac3']['object']['attrib']['id']			= 'mac3';
		$d['mac3']['object']['attrib']['name']			= 'mac3';
		$d['mac3']['object']['attrib']['type']			= 'text';
		$d['mac3']['object']['attrib']['value']			= $mac3;
		$d['mac3']['object']['attrib']['maxlength']		= 50;

		$d['type3']['label']							= $this->lang['form_type'];
		$d['type3']['object']['type']					= 'htmlobject_select';
		$d['type3']['object']['attrib']['index']		= array(0,1);
		$d['type3']['object']['attrib']['id']			= 'type3';
		$d['type3']['object']['attrib']['name']			= 'type3';
		$d['type3']['object']['attrib']['options']		= $type_select_arr;
		$d['type3']['object']['attrib']['selected']		= array($vm_configuration['htvcenter_VMWARE_ESX_VM_NIC_TYPE4']);

		$d['vswitch3']['label']							= $this->lang['form_vswitch'];
		$d['vswitch3']['object']['type']				= 'htmlobject_select';
		$d['vswitch3']['object']['attrib']['index']		= array(0,0);
		$d['vswitch3']['object']['attrib']['id']		= 'vswitch3';
		$d['vswitch3']['object']['attrib']['name']		= 'vswitch3';
		$d['vswitch3']['object']['attrib']['options']	= $vswitch_select_arr;
		$d['vswitch3']['object']['attrib']['selected']	= array($vm_configuration['htvcenter_VMWARE_ESX_VM_VSWITCH4']);

		// Net 4

		$d['net4']['label']                     = $this->lang['lang_net_4'];
		$d['net4']['object']['type']            = 'htmlobject_input';
		$d['net4']['object']['attrib']['type']  = 'checkbox';
		$d['net4']['object']['attrib']['name']  = 'net4';
		$d['net4']['object']['attrib']['id']    = 'net4';
		$d['net4']['object']['attrib']['value'] = 'enabled';
		$d['net4']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';
		if($vm_configuration['htvcenter_VMWARE_ESX_VM_MAC5'] !== '') {
			$d['net4']['object']['attrib']['checked'] = true;
		}

		$d['mac4']['label']								= $this->lang['form_mac'];
		$d['mac4']['object']['type']					= 'htmlobject_input';
		$d['mac4']['object']['attrib']['id']			= 'mac4';
		$d['mac4']['object']['attrib']['name']			= 'mac4';
		$d['mac4']['object']['attrib']['type']			= 'text';
		$d['mac4']['object']['attrib']['value']			= $mac4;
		$d['mac4']['object']['attrib']['maxlength']		= 50;

		$d['type4']['label']							= $this->lang['form_type'];
		$d['type4']['object']['type']					= 'htmlobject_select';
		$d['type4']['object']['attrib']['index']		= array(0,1);
		$d['type4']['object']['attrib']['id']			= 'type4';
		$d['type4']['object']['attrib']['name']			= 'type4';
		$d['type4']['object']['attrib']['options']		= $type_select_arr;
		$d['type4']['object']['attrib']['selected']		= array($vm_configuration['htvcenter_VMWARE_ESX_VM_NIC_TYPE5']);

		$d['vswitch4']['label']							= $this->lang['form_vswitch'];
		$d['vswitch4']['object']['type']				= 'htmlobject_select';
		$d['vswitch4']['object']['attrib']['index']		= array(0,0);
		$d['vswitch4']['object']['attrib']['id']		= 'vswitch4';
		$d['vswitch4']['object']['attrib']['name']		= 'vswitch4';
		$d['vswitch4']['object']['attrib']['options']	= $vswitch_select_arr;
		$d['vswitch4']['object']['attrib']['selected']	= array($vm_configuration['htvcenter_VMWARE_ESX_VM_VSWITCH5']);

		// boot from
		$d['boot_iso'] = '';
		$d['boot_iso_path'] = '';
		$d['boot_local'] = '';
		$d['browse_button'] = '';
		if($vmtype !== 'vmware-esx-vm-net') {
			$d['boot_net'] = '';

			$d['boot_iso']['label']                     = $this->lang['form_boot_iso'];
			$d['boot_iso']['object']['type']            = 'htmlobject_input';
			$d['boot_iso']['object']['attrib']['type']  = 'radio';
			$d['boot_iso']['object']['attrib']['id']    = 'boot_iso';
			$d['boot_iso']['object']['attrib']['name']  = 'boot';
			$d['boot_iso']['object']['attrib']['value'] = 'iso';
			if ($vm_configuration['htvcenter_VMWARE_ESX_VM_BOOT'] == 'cdrom')  {
				$d['boot_iso']['object']['attrib']['checked'] = true;
			}

			$d['boot_iso_path']['label']                    = $this->lang['form_iso_path'];
			$d['boot_iso_path']['object']['type']           = 'htmlobject_input';
			$d['boot_iso_path']['object']['attrib']['type'] = 'text';
			$d['boot_iso_path']['object']['attrib']['id']   = 'iso_path';
			$d['boot_iso_path']['object']['attrib']['name'] = 'iso_path';
			if (!strstr($vm_configuration['htvcenter_VMWARE_ESX_VM_CDROM_FILE'], "/dev/cdrom")) {
				$iso_path_default = str_replace(" ", "", $vm_configuration['htvcenter_VMWARE_ESX_VM_CDROM_FILE']);
				// remove beginning "ISO" string from filename
				$iso_path_default = substr($iso_path_default, 3);
				$d['boot_iso_path']['object']['attrib']['value']= $iso_path_default;
			}

			$d['boot_local']['label']                       = $this->lang['form_boot_local'];
			$d['boot_local']['object']['type']              = 'htmlobject_input';
			$d['boot_local']['object']['attrib']['type']    = 'radio';
			$d['boot_local']['object']['attrib']['name']    = 'boot';
			$d['boot_local']['object']['attrib']['value']   = 'local';
			$d['boot_local']['object']['attrib']['checked'] = true;
			if ($vm_configuration['htvcenter_VMWARE_ESX_VM_BOOT'] == 'local') {
				$d['boot_local']['object']['attrib']['checked'] = true;
			}

			$d['browse_button']['static']                      = true;
			$d['browse_button']['object']['type']              = 'htmlobject_input';
			$d['browse_button']['object']['attrib']['type']    = 'button';
			$d['browse_button']['object']['attrib']['name']    = 'browse_button';
			$d['browse_button']['object']['attrib']['id']      = 'browsebutton';
			$d['browse_button']['object']['attrib']['css']     = 'browse-button';
			$d['browse_button']['object']['attrib']['handler'] = 'onclick="filepicker.init(); return false;"';
			$d['browse_button']['object']['attrib']['style']   = "display:none;";
			$d['browse_button']['object']['attrib']['value']   = $this->lang['lang_browse'];
		} else {
			$d['boot_net']['label']                     = $this->lang['form_boot_net'];
			$d['boot_net']['object']['type']            = 'htmlobject_input';
			$d['boot_net']['object']['attrib']['type']  = 'radio';
			$d['boot_net']['object']['attrib']['name']  = 'boot';
			$d['boot_net']['object']['attrib']['value'] = 'network';
			$d['boot_net']['object']['attrib']['checked'] = true;
		}

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
		$command  = $this->htvcenter->get('basedir').'/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm post_vm_config';
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
	function verify_vmware_esx_mac_update($mac) {
		$new_forth_byte_first_bit = rand(1, 3);
		$mac = strtolower($this->vmware_mac_base.":".substr($mac, 9));
		$mac = substr_replace($mac , $new_forth_byte_first_bit, 9, 1);
		return $mac;
	}


}
?>
