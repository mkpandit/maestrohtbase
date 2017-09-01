<?php
/**
 * ESX Hosts Add VM
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_vm_add
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
	function __construct($htvcenter, $response, $controller) {
		$this->controller = $controller;
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('vmtype', $this->response->html->request()->get('vmtype'));
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
		$htvcenter_server	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$htvcenter_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->htvcenter_server		= $htvcenter_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_vm_components = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_components';
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
		$response = $this->vm_add();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
				$this->controller->__reload_vm();
				$vmtype = $this->response->html->request()->get('vmtype');
				$foward_to_step = $this->user->wizard_step;
				if($vmtype === 'vmware-esx-vm-local') {
					$foward_to_step = 4;
				}
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$foward_to_step.'&appliance_id='.$this->user->wizard_id.'&resource_id='.$response->resource_id
				);
			} else {
				if ($response->image_configured) {
					$this->response->redirect(
						$this->response->html->thisfile.'?base=appliance&appliance_msg='.$response->msg
					);
				} else {
					$this->response->redirect(
						$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
					);
				}
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		
		$a = $this->response->html->a();
		$a->label   = $this->lang['action_add_vm_image'];
		$a->css     = 'add';
		$a->handler = 'onclick="wait();"';
		$a->href    = $this->response->html->thisfile.'?base=image&image_action=add';
		$action_add_vm_image   = $a->get_string();
		
		
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
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
		$t->add($action_add_vm_image, 'add_vm_image');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->actions_name, 'actions_name');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm_add() {
		$response = $this->get_response();
		$form     = $response->form;
		$htvcenter_server = new htvcenter_server();

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

			$name			= $form->get_request('name');
			$mac			= $form->get_request('mac');
			$type			= $form->get_request('type');
			$vswitch		= $form->get_request('vswitch');
			$datastore		= $form->get_request('datastore');
			$memory			= $form->get_request('memory');
			$cpu			= $form->get_request('cpu');
			$vnc			= $form->get_request('vnc');
			$bootorder		= $form->get_request('boot');
			$guest_id		= $form->get_request('guestid');
			$disk_parameter = '';
			$vm_using_existing_vmdk = false;
			if ($form->get_request('existing_vmdk') !== '') {
				$image = new image();
				$image->get_instance_by_id($form->get_request('existing_vmdk'));
				$disk_parameter = " --existing-vmdk ".$image->rootdevice;
				$vm_using_existing_vmdk = true;
			} else if ($form->get_request('disk') !== '') {
				$disksize = intval($form->get_request('disk'))*1024;
				$disk_parameter = " -d ".$disksize;
			} else {
				$form->set_error('disk', $this->lang['error_disk']);
			}
			
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
					$lines = explode("\n", file_get_contents($this->statfile_vm));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								if($name === $line[0]) {
									$error = sprintf($this->lang['error_exists'], $name);
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

					// create VM resource in db
					$vm_resource = new resource();
					$vm_resource_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$vm_resource_ip = "0.0.0.0";
					// send command to the htvcenter-server
					$htvcenter_server->send_command("htvcenter_server_add_resource ".$vm_resource_id." ".$mac." ".$vm_resource_ip);
					// set resource type
					$vmtype = $this->response->html->request()->get('vmtype');
					if($vmtype === 'vmware-esx-vm-net') {
						$virtualization = new virtualization();
						$virtualization->get_instance_by_type("vmware-esx-vm-net");
					} else {
						$virtualization = new virtualization();
						$virtualization->get_instance_by_type("vmware-esx-vm-local");
					}
					// add to htvcenter database
					$resource_fields["resource_id"] = $vm_resource_id;
					$resource_fields["resource_ip"] = $vm_resource_ip;
					$resource_fields["resource_mac"] = $mac;
					$resource_fields["resource_kernel"] = 'default';
					$resource_fields["resource_kernelid"] = 1;
					$resource_fields["resource_image"] = 'idle';
					$resource_fields["resource_imageid"] = 1;
					$resource_fields["resource_localboot"] = 0;
					$resource_fields["resource_hostname"] = $name;
					$resource_fields["resource_vtype"] = $virtualization->id;
					$resource_fields["resource_vhostid"] = $this->resource->id;
					$resource_fields["resource_vname"] = $name;
					$resource_fields["resource_vnc"] = $vm_resource->generate_vnc_port($this->resource->id);
					$vm_resource->add($resource_fields);
					$vm_resource->get_instance_by_id($vm_resource_id);

					// set id in response
					$response->resource_id = $vm_resource_id;

					// remove spaces from vswitch + datastore parameters
					$vswitch = str_replace(" ", "@", $vswitch);
					$datastore = str_replace(" ", "@", $datastore);
					
					// send command to create the vm
					$command  = $this->htvcenter->get('basedir')."/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm create";
					$command .= " -i ".$this->resource->ip;
					$command .= " -n ".$name;
					$command .= " -m ".$mac;
					$command .= " -l ".$datastore;
					$command .= " -r ".$memory;
					$command .= $disk_parameter;
					$command .= " -c ".$cpu;
					$command .= " -va ".$vnc;
					$command .= " -vp ".$vm_resource->vnc;
					$command .= " -b ".$bootorder." ".$iso_path;
					$command .= " -t ".$type;
					$command .= " -v ".$vswitch;
					$command .= " -vmtype ".$vmtype;
					$command .= " --guest-id ".$guest_id;
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

					$htvcenter_server->send_command($command, NULL, true);
					while (!file_exists($this->statfile_vm)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}

					$vm_with_image_configured = false;
					// create image object for vmware-esx-vm-local deployment
					$deployment = new deployment();
					$deployment->get_instance_by_name('esx-deployment');
					if($vmtype === 'vmware-esx-vm-local') {
						$storage = new storage();
						$vmware_esx_id_list = $storage->get_ids_by_storage_type($deployment->id);
						$found_vmware_esx = false;
						$found_vmware_esx_id = -1;
						foreach ($vmware_esx_id_list as $list) {
							foreach ($list as $vmware_esx_id) {
								$storage->get_instance_by_id($vmware_esx_id);
								if ($storage->resource_id == $this->resource->id) {
									$found_vmware_esx = true;
									$found_vmware_esx_id = $storage->id;
									break;
								}
							}
						}
						if ($found_vmware_esx) {
							$image = new image();
							// auto create image object if not using existing vmdk
							if ($vm_using_existing_vmdk) {
								$image->get_instance_by_id($form->get_request('existing_vmdk'));
								$image_fields['image_capabilities'] = 'TYPE='.$guest_id;
								$image->update($image->id, $image_fields);
								$vm_with_image_configured = true;
							} else {
								$image_fields = array();
								$vm_image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
								$image_fields["image_id"] = $vm_image_id;
								$image_fields['image_name'] = $name;
								$image_fields['image_type'] = 'esx-deployment';
								$image_fields['image_rootfstype'] = 'local';
								$image_fields['image_isactive']=0;
								$image_fields['image_storageid'] = $found_vmware_esx_id;
								$image_fields['image_comment'] = "Image Object for vmdk $name";
								$image_fields['image_rootdevice'] = $datastore.':'.$name.'/'.$name.'.vmdk';
								$image_fields['image_capabilities'] = 'TYPE='.$guest_id;
								$image->add($image_fields);
								$image->get_instance_by_id($vm_image_id);
								$vm_with_image_configured = true;
							}
						}
					}
					
					// netboot VM
					if($vmtype === 'vmware-esx-vm-net') {
						if ($form->get_request('netboot_image') !== '') {
							$image = new image();
							$image->get_instance_by_id($form->get_request('netboot_image'));
							$vm_with_image_configured = true;
						}
					}					
					// add/update server if any type of image was set
					if ($vm_with_image_configured) {
						$now=$_SERVER['REQUEST_TIME'];
						if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
							// update appliance for this VM if we are coming from the wizard
							$afields['appliance_resources'] = $vm_resource_id;
							$afields['appliance_kernelid'] = '1';
							$afields['appliance_imageid'] = $image->id;
							$afields["appliance_virtual"]= 0;
							$afields["appliance_virtualization"]=$virtualization->id;
							$afields['appliance_wizard'] = '';
							$afields['appliance_comment'] = 'VMware ESX VM for resource '.$vm_resource_id;
							$afields['appliance_stoptime']=$now;
							$afields['appliance_starttime']='';
							$afields['appliance_state']='stopped';
							$this->appliance->update($this->user->wizard_id, $afields);
						} else {
							// auto create the appliance for this VM if we are not coming from the wizard
							$appliance_name = str_replace("_", "-", strtolower(trim($name)));
							$new_appliance_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
							$afields['appliance_id'] = $new_appliance_id;
							$afields['appliance_name'] = $appliance_name;
							$afields['appliance_resources'] = $vm_resource_id;
							$afields['appliance_kernelid'] = '1';
							$afields['appliance_imageid'] = $image->id;
							$afields["appliance_virtual"]= 0;
							$afields["appliance_virtualization"]=$virtualization->id;
							$afields['appliance_wizard'] = '';
							$afields['appliance_comment'] = 'VMware ESX VM for resource '.$vm_resource_id;
							$this->appliance->add($afields);
							// update state/start+stoptime
							$aufields['appliance_stoptime']=$now;
							$aufields['appliance_starttime']='';
							$aufields['appliance_state']='stopped';
							$this->appliance->update($new_appliance_id, $aufields);
						}
						$response->image_configured = true;
					} else {
						$response->image_configured = false;
					}
					$response->msg = sprintf($this->lang['msg_added'], $name);
				}
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
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
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$datastore_select_arr = array();
		$vswitch_select_arr =  array();
		// get the datastore and vswitchlist for the selects
		if (file_exists($this->statfile_vm_components)) {
			$lines = explode("\n", file_get_contents($this->statfile_vm_components));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						switch ($line[0]) {
							case 'ds':
								$datastore_select_arr[] = array($line[1],$line[1]);
								break;
							case 'vs':
								$vswitch_select_arr[] = array($line[1],$line[1]);
								break;
						}
					}
				}
			}
		}

		// if we come from the wizard suggest the server name
		$vm_name_suggestion = '';
		if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($this->user->wizard_id);
			$vm_name_suggestion = $appliance->name;
		}

		// get a list of existing vmdks to select
		$existing_vmdk_arr = array();
		$image = new image();
		$vmdk_image_id_ar = $image->get_ids_by_type('esx-deployment');
		foreach ($vmdk_image_id_ar as $iid_ar) {
			$image_id = $iid_ar['image_id'];
			$image->get_instance_by_id($image_id);
			$existing_vmdk_arr[] = array($image->id, $image->name);
		}
		$existing_vmdk_arr[] = array('', '');
		
		// get a list of network-deployment images for netboot vms
		$existing_netboot_image_arr = array();
		$existing_netboot_image_id_ar = $image->get_ids();
		foreach ($existing_netboot_image_id_ar as $iid_ar) {
			$image_id = $iid_ar['image_id'];
			$image->get_instance_by_id($image_id);
			if ($image->is_network_deployment()) {
				$existing_netboot_image_arr[] = array($image->id, $image->name);
			}
		}
		$existing_netboot_image_arr[] = array('', '');
		
		// genenrate mac
		$vm_resource = new resource();
		$vm_resource->generate_mac();
		$vm_mac = $this->verify_vmware_esx_mac_add($vm_resource->mac);
		// 1 nic
		$vm_resource->generate_mac();
		$vm_mac1 = $this->verify_vmware_esx_mac_add($vm_resource->mac);
		// 2 nic
		$vm_resource->generate_mac();
		$vm_mac2 = $this->verify_vmware_esx_mac_add($vm_resource->mac);
		// 3 nic
		$vm_resource->generate_mac();
		$vm_mac3 = $this->verify_vmware_esx_mac_add($vm_resource->mac);
		// 4 nic
		$vm_resource->generate_mac();
		$vm_mac4 = $this->verify_vmware_esx_mac_add($vm_resource->mac);

		#$disk_select_arr[] = array('','');
		#$disk_select_arr[] = array('1048576','1 GB');
		#$disk_select_arr[] = array('2097152','2 GB');
		#$disk_select_arr[] = array('10485760','10 GB');
		#$disk_select_arr[] = array('20971520','20 GB');
		#$disk_select_arr[] = array('52428800','50 GB');
		#$disk_select_arr[] = array('104857600','100 GB');

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

		$d['name']['label']							    = $this->lang['form_name'];
		$d['name']['required']						    = true;
		$d['name']['validate']['regex']				    = $this->htvcenter->get('regex', 'hostname');
		$d['name']['validate']['errormsg']			    = sprintf($this->lang['error_name'], $this->htvcenter->get('regex', 'hostname'));
		$d['name']['object']['type']				    = 'htmlobject_input';
		$d['name']['object']['attrib']['id']		    = 'name';
		$d['name']['object']['attrib']['name']		    = 'name';
		$d['name']['object']['attrib']['type']		    = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="vm" data-length="8"';
		$d['name']['object']['attrib']['value']		    = $vm_name_suggestion;
		$d['name']['object']['attrib']['maxlength']	    = 50;

		$d['memory']['label']						= $this->lang['form_memory'];
		$d['memory']['object']['type']				= 'htmlobject_select';
		$d['memory']['object']['attrib']['index']	= array(0,1);
		$d['memory']['object']['attrib']['id']		= 'memory';
		$d['memory']['object']['attrib']['name']	= 'memory';
		$d['memory']['object']['attrib']['options']	= $memory_select_arr;

		$d['cpu']['label']						= $this->lang['form_cpu'];
		$d['cpu']['object']['type']				= 'htmlobject_select';
		$d['cpu']['object']['attrib']['index']	= array(0,1);
		$d['cpu']['object']['attrib']['id']		= 'cpu';
		$d['cpu']['object']['attrib']['name']	= 'cpu';
		$d['cpu']['object']['attrib']['options']	= $cpu_select_arr;

		$vmtype = $this->response->html->request()->get('vmtype');
		if($vmtype === 'vmware-esx-vm-net') {
			$d['disk']['label'] = $this->lang['form_swap'];
			$d['disk']['object']['type']				= 'htmlobject_select';
			$d['disk']['object']['attrib']['index']		= array(0,1);
			$d['disk']['object']['attrib']['id']		= 'disk';
			$d['disk']['object']['attrib']['name']		= 'disk';
			$d['disk']['object']['attrib']['options']	= $swap_select_arr;

			$d['netboot_image']['label']						= $this->lang['form_existing_disk'];
			$d['netboot_image']['object']['type']				= 'htmlobject_select';
			$d['netboot_image']['object']['attrib']['index']	= array(0,1);
			$d['netboot_image']['object']['attrib']['id']		= 'netboot_image';
			$d['netboot_image']['object']['attrib']['name']		= 'netboot_image';
			$d['netboot_image']['object']['attrib']['options']	= $existing_netboot_image_arr;
			$d['netboot_image']['object']['attrib']['selected']	= array('');

			$d['existing_vmdk'] = "";
			
		} else {
			$d['disk']['label'] 						= $this->lang['form_disk'];
			$d['disk']['object']['type']				= 'htmlobject_input';
			$d['disk']['validate']['regex']				= '/^[1-9]{1,}[0-9]+$/i';
			$d['disk']['validate']['errormsg']			= $this->lang['error_disk_size'];
			$d['disk']['object']['attrib']['type']		= 'text';
			$d['disk']['object']['attrib']['id']		= 'disk';
			$d['disk']['object']['attrib']['name']		= 'disk';
			$d['disk']['object']['attrib']['value']		= '';

			$d['existing_vmdk']['label']						= $this->lang['form_existing_disk'];
			$d['existing_vmdk']['object']['type']				= 'htmlobject_select';
			$d['existing_vmdk']['object']['attrib']['index']	= array(0,1);
			$d['existing_vmdk']['object']['attrib']['id']		= 'existing_vmdk';
			$d['existing_vmdk']['object']['attrib']['name']		= 'existing_vmdk';
			$d['existing_vmdk']['object']['attrib']['options']	= $existing_vmdk_arr;
			$d['existing_vmdk']['object']['attrib']['selected']	= array('');

			$d['netboot_image'] = "";
		}

		$d['datastore']['label']						= $this->lang['form_datastore'];
		$d['datastore']['object']['type']				= 'htmlobject_select';
		$d['datastore']['object']['attrib']['index']	= array(0,1);
		$d['datastore']['object']['attrib']['id']		= 'datastore';
		$d['datastore']['object']['attrib']['name']		= 'datastore';
		$d['datastore']['object']['attrib']['options']	= $datastore_select_arr;

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
		$d['guestid']['label']						 = 'Guest Id';
		$d['guestid']['object']['type']				 = 'htmlobject_select';
		$d['guestid']['object']['attrib']['index']	 = array(0,1);
		$d['guestid']['object']['attrib']['id']		 = 'guestid';
		$d['guestid']['object']['attrib']['name']	 = 'guestid';
		$d['guestid']['object']['attrib']['options'] = $guestids;

		
		$d['net0']['label']                        = $this->lang['lang_net_0'];
		$d['net0']['object']['type']               = 'htmlobject_input';
		$d['net0']['object']['attrib']['type']     = 'checkbox';
		$d['net0']['object']['attrib']['id']       = 'net0';
		$d['net0']['object']['attrib']['name']     = 'net0';
		$d['net0']['object']['attrib']['value']    = 'enabled';
		$d['net0']['object']['attrib']['checked']  = true;
		$d['net0']['object']['attrib']['disabled'] = true;
		
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

		// Net 1
		$d['net1']['label']                       = $this->lang['lang_net_1'];
		$d['net1']['object']['type']              = 'htmlobject_input';
		$d['net1']['object']['attrib']['type']    = 'checkbox';
		$d['net1']['object']['attrib']['id']      = 'net1';
		$d['net1']['object']['attrib']['name']    = 'net1';
		$d['net1']['object']['attrib']['value']   = 'enabled';
		$d['net1']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

		$d['mac1']['label']								= $this->lang['form_mac'];
		$d['mac1']['object']['type']					= 'htmlobject_input';
		$d['mac1']['object']['attrib']['id']			= 'mac1';
		$d['mac1']['object']['attrib']['name']			= 'mac1';
		$d['mac1']['object']['attrib']['type']			= 'text';
		$d['mac1']['object']['attrib']['value']			= $vm_mac1;
		$d['mac1']['object']['attrib']['maxlength']		= 50;

		$d['type1']['label']							= $this->lang['form_type'];
		$d['type1']['object']['type']					= 'htmlobject_select';
		$d['type1']['object']['attrib']['index']		= array(0,1);
		$d['type1']['object']['attrib']['id']			= 'type1';
		$d['type1']['object']['attrib']['name']			= 'type1';
		$d['type1']['object']['attrib']['options']		= $type_select_arr;

		$d['vswitch1']['label']							= $this->lang['form_vswitch'];
		$d['vswitch1']['object']['type']				= 'htmlobject_select';
		$d['vswitch1']['object']['attrib']['index']		= array(0,1);
		$d['vswitch1']['object']['attrib']['id']		= 'vswitch1';
		$d['vswitch1']['object']['attrib']['name']		= 'vswitch1';
		$d['vswitch1']['object']['attrib']['options']	= $vswitch_select_arr;

		// Net 2

		$d['net2']['label']                     = $this->lang['lang_net_2'];
		$d['net2']['object']['type']            = 'htmlobject_input';
		$d['net2']['object']['attrib']['type']  = 'checkbox';
		$d['net2']['object']['attrib']['id']    = 'net2';
		$d['net2']['object']['attrib']['name']  = 'net2';
		$d['net2']['object']['attrib']['value'] = 'enabled';
		$d['net2']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

		$d['mac2']['label']								= $this->lang['form_mac'];
		$d['mac2']['object']['type']					= 'htmlobject_input';
		$d['mac2']['object']['attrib']['id']			= 'mac2';
		$d['mac2']['object']['attrib']['name']			= 'mac2';
		$d['mac2']['object']['attrib']['type']			= 'text';
		$d['mac2']['object']['attrib']['value']			= $vm_mac2;
		$d['mac2']['object']['attrib']['maxlength']		= 50;

		$d['type2']['label']							= $this->lang['form_type'];
		$d['type2']['object']['type']					= 'htmlobject_select';
		$d['type2']['object']['attrib']['index']		= array(0,1);
		$d['type2']['object']['attrib']['id']			= 'type2';
		$d['type2']['object']['attrib']['name']			= 'type2';
		$d['type2']['object']['attrib']['options']		= $type_select_arr;

		$d['vswitch2']['label']							= $this->lang['form_vswitch'];
		$d['vswitch2']['object']['type']				= 'htmlobject_select';
		$d['vswitch2']['object']['attrib']['index']		= array(0,1);
		$d['vswitch2']['object']['attrib']['id']		= 'vswitch2';
		$d['vswitch2']['object']['attrib']['name']		= 'vswitch2';
		$d['vswitch2']['object']['attrib']['options']	= $vswitch_select_arr;

		// Net 3

		$d['net3']['label']                     = $this->lang['lang_net_3'];
		$d['net3']['object']['type']            = 'htmlobject_input';
		$d['net3']['object']['attrib']['type']  = 'checkbox';
		$d['net3']['object']['attrib']['id']    = 'net3';
		$d['net3']['object']['attrib']['name']  = 'net3';
		$d['net3']['object']['attrib']['value'] = 'enabled';
		$d['net3']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

		$d['mac3']['label']								= $this->lang['form_mac'];
		$d['mac3']['object']['type']					= 'htmlobject_input';
		$d['mac3']['object']['attrib']['id']			= 'mac3';
		$d['mac3']['object']['attrib']['name']			= 'mac3';
		$d['mac3']['object']['attrib']['type']			= 'text';
		$d['mac3']['object']['attrib']['value']			= $vm_mac3;
		$d['mac3']['object']['attrib']['maxlength']		= 50;

		$d['type3']['label']							= $this->lang['form_type'];
		$d['type3']['object']['type']					= 'htmlobject_select';
		$d['type3']['object']['attrib']['index']		= array(0,1);
		$d['type3']['object']['attrib']['id']			= 'type3';
		$d['type3']['object']['attrib']['name']			= 'type3';
		$d['type3']['object']['attrib']['options']		= $type_select_arr;

		$d['vswitch3']['label']							= $this->lang['form_vswitch'];
		$d['vswitch3']['object']['type']				= 'htmlobject_select';
		$d['vswitch3']['object']['attrib']['index']		= array(0,1);
		$d['vswitch3']['object']['attrib']['id']		= 'vswitch3';
		$d['vswitch3']['object']['attrib']['name']		= 'vswitch3';
		$d['vswitch3']['object']['attrib']['options']	= $vswitch_select_arr;

		// Net 4

		$d['net4']['label']                     = $this->lang['lang_net_4'];
		$d['net4']['object']['type']            = 'htmlobject_input';
		$d['net4']['object']['attrib']['type']  = 'checkbox';
		$d['net4']['object']['attrib']['name']  = 'net4';
		$d['net4']['object']['attrib']['id']    = 'net4';
		$d['net4']['object']['attrib']['value'] = 'enabled';
		$d['net4']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

		$d['mac4']['label']								= $this->lang['form_mac'];
		$d['mac4']['object']['type']					= 'htmlobject_input';
		$d['mac4']['object']['attrib']['id']			= 'mac4';
		$d['mac4']['object']['attrib']['name']			= 'mac4';
		$d['mac4']['object']['attrib']['type']			= 'text';
		$d['mac4']['object']['attrib']['value']			= $vm_mac4;
		$d['mac4']['object']['attrib']['maxlength']		= 50;

		$d['type4']['label']							= $this->lang['form_type'];
		$d['type4']['object']['type']					= 'htmlobject_select';
		$d['type4']['object']['attrib']['index']		= array(0,1);
		$d['type4']['object']['attrib']['id']			= 'type4';
		$d['type4']['object']['attrib']['name']			= 'type4';
		$d['type4']['object']['attrib']['options']		= $type_select_arr;

		$d['vswitch4']['label']							= $this->lang['form_vswitch'];
		$d['vswitch4']['object']['type']				= 'htmlobject_select';
		$d['vswitch4']['object']['attrib']['index']		= array(0,1);
		$d['vswitch4']['object']['attrib']['id']		= 'vswitch4';
		$d['vswitch4']['object']['attrib']['name']		= 'vswitch4';
		$d['vswitch4']['object']['attrib']['options']	= $vswitch_select_arr;

		// boot from
		$d['boot_iso'] = '';
		$d['boot_iso_path'] = '';
		$d['boot_local'] = '';
		$d['boot_net'] = '';
		$d['browse_button'] = '';
		if($vmtype !== 'vmware-esx-vm-net') {
			$d['boot_iso']['label']                     = $this->lang['form_boot_iso'];
			$d['boot_iso']['object']['type']            = 'htmlobject_input';
			$d['boot_iso']['object']['attrib']['type']  = 'radio';
			$d['boot_iso']['object']['attrib']['id']    = 'boot_iso';
			$d['boot_iso']['object']['attrib']['name']  = 'boot';
			$d['boot_iso']['object']['attrib']['value'] = 'iso';

			$d['boot_iso_path']['label']                    = $this->lang['form_iso_path'];
			$d['boot_iso_path']['object']['type']           = 'htmlobject_input';
			$d['boot_iso_path']['object']['attrib']['type'] = 'text';
			$d['boot_iso_path']['object']['attrib']['id']   = 'iso_path';
			$d['boot_iso_path']['object']['attrib']['name'] = 'iso_path';

			$d['boot_local']['label']                       = $this->lang['form_boot_local'];
			$d['boot_local']['object']['type']              = 'htmlobject_input';
			$d['boot_local']['object']['attrib']['type']    = 'radio';
			$d['boot_local']['object']['attrib']['name']    = 'boot';
			$d['boot_local']['object']['attrib']['value']   = 'local';
			$d['boot_local']['object']['attrib']['checked'] = true;

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
			if($vmtype === 'vmware-esx-vm-net') {
				$d['boot_net']['object']['attrib']['checked'] = true;
			}
		}

		$d['vnc']['label']							= $this->lang['form_vnc'];
		$d['vnc']['required']						= true;
		$d['vnc']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vnc']['validate']['errormsg']			= sprintf($this->lang['error_vnc'], 'a-z0-9._-');
		$d['vnc']['object']['type']					= 'htmlobject_input';
		$d['vnc']['object']['attrib']['id']			= 'vnc';
		$d['vnc']['object']['attrib']['name']		= 'vnc';
		$d['vnc']['object']['attrib']['type']		= 'password';
		$d['vnc']['object']['attrib']['value']		= '';
		$d['vnc']['object']['attrib']['maxlength']	= 50;

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}


	//--------------------------------------------
	/**
	 * verifies VMware compatible MAC addresses
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function verify_vmware_esx_mac_add($mac) {
		$new_forth_byte_first_bit = rand(1, 3);
		$mac = strtolower($this->vmware_mac_base.":".substr($mac, 9));
		$mac = substr_replace($mac , $new_forth_byte_first_bit, 9, 1);
		return $mac;
	}

}
?>
