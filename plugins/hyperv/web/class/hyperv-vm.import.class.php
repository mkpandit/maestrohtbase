<?php
/**
 * Imports existing VMs
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_vm_import
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_id';
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
		#$this->statfile_host = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.host_statistics';
		#$this->statfile_vm = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.vm_list';
		#$this->statfile_ne = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.net_config';
		#$this->statfile_ds = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.ds_list';
		$this->hyperv_mac_base = "00:50:56";
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
		$t = $this->response->html->template($this->tpldir.'/hyperv-vm-import.tpl.php');
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

			$this->controller->__reload_vm();
			$file = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$this->resource->ip.'.vm_list';
			// read stats file
			$line_nubmer = 1;
			$lines = explode("\n", file_get_contents($file));

			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						// first line is the host info
						if ($line_nubmer == 0) {
							continue;
						} else {
							if( $line[0] === $form->get_request('vm_name') ) {

								// vm infos
								$vm_name = $line[0];
								$vm_status = $line[1];
								$vm_cpu = $line[2];
								$vm_mem = $line[3];
								$vm_first_nic_str = explode(',', $line[4]);
								$vm_mac = strtolower($vm_first_nic_str[0]);
								if (!strlen($vm_mac)) {
									continue;
								}
								$vm_nic = 1;
								$vm_disk = $line[8];
								// filter ds/vmdk
								$ds_start_marker = strpos($line[8], '[');
								$ds_start_marker++;
								$ds_end_marker = strpos($line[8], ']');
								$vm_datastore = substr($line[8], $ds_start_marker, $ds_end_marker - $ds_start_marker);
								$vm_datastore = trim($vm_datastore);
								$vm_datastore_filename = substr($line[8], $ds_end_marker+1);
								$vm_datastore_filename = trim($vm_datastore_filename);
								$vm_image_name = str_replace(".vmdk", "", basename($vm_datastore_filename));
								$vm_boot = $line[12];

								if (!strlen($vm_mac)) {
									continue;
								}
								$vm_resource = $this->htvcenter->resource();
								$vm_resource->get_instance_by_mac($vm_mac);
								if (!$vm_resource->exists($vm_mac)) {

									// create resource, image and appliance
									$deployment = $this->htvcenter->deployment();
									$deployment->get_instance_by_name('hyperv-deployment');
									$virtualization = $this->htvcenter->virtualization();
									if (($vm_boot == "local") || ($vm_boot == "cdrom")) {
										$virtualization->get_instance_by_type("hyperv-vm-local");
									} else if ($vm_boot == "network") {
										$virtualization->get_instance_by_type("hyperv-vm-net");
									} else {
										continue;
									}
									// create resource
									$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
									$ip = "0.0.0.0";
									// send command to the htvcenter-server
									$htvcenter = new htvcenter_server();
									$htvcenter->send_command('htvcenter_server_add_resource '.$id.' '.$vm_mac.' '.$ip);
									// add to htvcenter database
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
									$vm_resource->add($rfields);
									$vm_resource->get_instance_by_mac($vm_mac);

									// local VMs only, check for the VMs image and create it if not existing yet
									if (($vm_boot == "local") || ($vm_boot == "cdrom")) {
										if (!strlen($vm_image_name)) {
											continue;
										}
										$image = $this->htvcenter->image();
										$image->get_instance_by_name($vm_image_name);
										if ($image->id == '') {
											// image storage already existing ? if not auto-create it
											$storage = new storage();
											$hyperv_id_list = $storage->get_ids_by_storage_type($deployment->id);
											$found_hyperv = false;
											$found_hyperv_id = -1;
											foreach ($hyperv_id_list as $list) {
												foreach ($list as $hyperv_id) {
													$storage->get_instance_by_id($hyperv_id);
													if ($storage->resource_id == $this->appliance->resources) {
														$found_hyperv = true;
														$found_hyperv_id = $storage->id;
														break;
													}
												}
											}
											if (!$found_hyperv) {
												$errors[] = 'Error: '.$vm_name.' storage not found';
											}

											// auto create image object
											$image_fields = array();
											$vm_image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
											$image_fields["image_id"] = $vm_image_id;
											$image_fields['image_name'] = $vm_image_name;
											$image_fields['image_type'] = 'hyperv-deployment';
											$image_fields['image_rootfstype'] = 'local';
											$image_fields['image_isactive']=1;
											$image_fields['image_storageid'] = $found_hyperv_id;
											$image_fields['image_comment'] = "Image Object for vmdk $vm_image_name";
											$image_fields['image_rootdevice'] = $vm_datastore.':'.$vm_datastore_filename;
											$image_fields['image_capabilities'] = 'TYPE='.$line[7];
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
												$afields['appliance_comment'] = 'Hyper-V VM Appliance for Resource '.$id;
												$this->appliance->add($afields);
												// update state/start+stoptime
												$aufields['appliance_stoptime']='1';
												$aufields['appliance_starttime']='';
												$aufields['appliance_state']='inactive';
												$this->appliance->update($new_appliance_id, $aufields);
											}
										}
									}
									$message[] = sprintf($this->lang['msg_imported'], $vm_name);
									// end auto-create/import
									unset($vm_name);
									unset($vm_status);
									unset($vm_first_nic_str);
									unset($vm_mac);
									unset($vm_nic);
									unset($vm_cpu);
									unset($vm_mem);
									unset($rfields);
									unset($aufields);
									unset($image_fields);
								} else {
									$errors[] = 'Error: '.$vm_name.' mac '.$vm_mac.' in use';
								}
								$line_nubmer++;
							}
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


}
?>
