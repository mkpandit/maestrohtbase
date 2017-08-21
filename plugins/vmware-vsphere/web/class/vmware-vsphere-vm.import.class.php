<?php
/**
 * Imports existing VMs
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vm_import
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
	function __construct($htvcenter, $response, $controller) {
		$this->controller = $controller;
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');
		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add($this->identifier_name.'[]', '');
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
		$this->vm_name = $vm_name;
		$this->statfile_vm_config = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.'.$this->vm_name.'.vm_config';
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
		$response = $this->vm_import();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vm-import.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['please_notice'], "please_notice");
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Import VM
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function vm_import() {
		$response = $this->get_response();
		$form     = $response->form;
		
		if(!$form->get_errors() && $response->submit()) {
			$errors     = array();
			$message    = array();

			$this->reload_vm_config();
			$line_nubmer = 1;
			$lines = explode("\n", file_get_contents($this->statfile_vm_config));

			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = $this->htvcenter->string_to_array($line, '|', '=');

						// vm infos
						$vm_name = $line['name'];
						$vm_status = $line[1];
						$vm_cpu = $line['numCpu'];
						$vm_mem = $line['guestMemoryUsage'];
						$first_nic_str = explode(',', $line['macAddress']);
						$vm_mac = $first_nic_str[0];
						if (!strlen($vm_mac)) {
							continue;
						}

						$first_vmdk_arr = explode(',', $line['fileName']);
						$ds_start_marker = strpos($first_vmdk_arr[0], '[');
						$ds_start_marker++;
						$ds_end_marker = strpos($first_vmdk_arr[0], ']');
						$vm_datastore = substr($first_vmdk_arr[0], $ds_start_marker, $ds_end_marker - $ds_start_marker);
						$vm_datastore = trim($vm_datastore);
						$vm_datastore_filename = substr($first_vmdk_arr[0], $ds_end_marker+1);
						$vm_datastore_filename = trim($vm_datastore_filename);
						$vm_datastore_link_content = '['.$vm_datastore.']'.basename($vm_datastore_filename);
						$vm_image_name = str_replace(".vmdk", "", basename($vm_datastore_filename));
						
						$vm_boot = $line['boot'];

						$vm_resource = $this->htvcenter->resource();
						$vm_resource->get_instance_by_mac($vm_mac);
						if (!$vm_resource->exists($vm_mac)) {

							// create resource, image and appliance
							$deployment = $this->htvcenter->deployment();
							$deployment->get_instance_by_name('vsphere-deployment');
							$virtualization = $this->htvcenter->virtualization();
							$virtualization->get_instance_by_type("vmware-vsphere-vm-local");
							
							// create resource
							$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
							$ip = "0.0.0.0";
							// send command to the HyperTask-server
							$htvcenter = new htvcenter_server();
							$htvcenter->send_command('htvcenter_server_add_resource '.$id.' '.$vm_mac.' '.$ip);
							// add to HyperTask database
							$rfields["resource_id"] = $id;
							$rfields["resource_ip"] = $ip;
							$rfields["resource_mac"] = $vm_mac;
							$rfields["resource_kernel"] = 'local';
							$rfields["resource_kernelid"] = 0;
							$rfields["resource_localboot"] = 0;
							$rfields["resource_hostname"] = $vm_name;
							$rfields["resource_vtype"] = $virtualization->id;
							$rfields["resource_vhostid"] = $this->appliance->resources;
							$rfields["resource_image"] = 'idle';
							$rfields["resource_imageid"] = 1;
							$rfields["resource_vname"] = $vm_name;							
							$vm_resource->add($rfields);
							$vm_resource->get_instance_by_mac($vm_mac);

							// local VMs only, check for the VMs image and create it if not existing yet
							if (!strlen($vm_image_name)) {
								continue;
							}
							$image = $this->htvcenter->image();
							$image->get_instance_by_name($vm_image_name);
							if ($image->id == '') {
								// image storage already existing ? if not auto-create it
								$storage = new storage();
								$vmware_vsphere_id_list = $storage->get_ids_by_storage_type($deployment->id);
								$found_vmware_vsphere = false;
								$found_vmware_vsphere_id = -1;
								foreach ($vmware_vsphere_id_list as $list) {
									foreach ($list as $vmware_vsphere_id) {
										$storage->get_instance_by_id($vmware_vsphere_id);
										if ($storage->resource_id == $this->appliance->resources) {
											$found_vmware_vsphere = true;
											$found_vmware_vsphere_id = $storage->id;
											break;
										}
									}
								}
								if (!$found_vmware_vsphere) {
									$errors[] = 'Error: '.$vm_name.' storage not found';
								}

								// auto create image object
								$image_fields = array();
								$vm_image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
								$image_fields["image_id"] = $vm_image_id;
								$image_fields['image_name'] = $vm_image_name;
								$image_fields['image_type'] = 'vsphere-deployment';
								$image_fields['image_rootfstype'] = 'local';
								$image_fields['image_isactive']=1;
								$image_fields['image_storageid'] = $found_vmware_vsphere_id;
								$image_fields['image_comment'] = "Image Object for vmdk $vm_image_name";
								$image_fields['image_rootdevice'] = $vm_datastore.':'.$vm_datastore_filename;
								$image_fields['image_capabilities'] = 'TYPE='.$line['guestId'];
								$image->add($image_fields);

								# update image object
								$image->get_instance_by_id($vm_image_id);
								// update resource with image infos
								#$rfields["resource_id"] = $id;
								#$rfields["resource_image"] = $image->name;
								#$rfields["resource_imageid"] = $image->id;
								#$rfields["resource_imageid"] = 1;
								$vm_resource->update_info($id, $rfields);
								$vm_resource->get_instance_by_mac($vm_mac);

								// check if an appliance exists for this resource, if not create it
								$appliance_exists = false;
								$vm_appl_exist_check = $this->htvcenter->appliance();
								$vm_appl_exist_check->get_instance_by_virtualization_and_resource($virtualization->id, $vm_resource->id);
								if ((isset($vm_appl_exist_check->id)) && ($vm_appl_exist_check->id > 0)) {
									$appliance_exists = true;
								} else {
									$now=$_SERVER['REQUEST_TIME'];
									$new_appliance_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
									$afields['appliance_id'] = $new_appliance_id;
									$afields['appliance_name'] = $vm_name;
									$afields['appliance_resources'] = $id;
									$afields['appliance_kernelid'] = '1';
									$afields['appliance_imageid'] = $vm_image_id;
									$afields["appliance_virtual"]= 0;
									$afields["appliance_virtualization"]=$virtualization->id;
									$afields['appliance_wizard'] = '';
									$afields['appliance_comment'] = 'VMWare vSphere VM Appliance for Resource '.$id;
									$this->appliance->add($afields);
									// update state/start+stoptime
									$aufields['appliance_stoptime']='1';
									$aufields['appliance_starttime']='';
									$aufields['appliance_state']='inactive';
									$this->appliance->update($new_appliance_id, $aufields);
								}
							}
							$message[] = sprintf($this->lang['msg_imported'], $vm_name);
						} else {
							$errors[] = 'Error: '.$vm_name.' mac '.$vm_mac.' in use';
						}
					}
				}
			}
			
			if(count($errors) === 0) {
				$response->msg = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response->error = join('<br>', $msg);
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'import');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$name =$this->response->html->request()->get('vm_name');

		$i = 0;
		$d['param_f'.$i]['label']                       = $name;
		$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
		$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
		$d['param_f'.$i]['object']['attrib']['name']    = 'vm_name';
		$d['param_f'.$i]['object']['attrib']['value']   = $name;
		$d['param_f'.$i]['object']['attrib']['checked'] = true;
		
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
	

}
?>
