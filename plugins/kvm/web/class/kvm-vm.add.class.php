<?php
/**
 * KVM-VM Add new VM
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kvm_vm_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_vm_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
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

var $freemb = 'undefined';
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
		$this->response   = $response;
		$this->file       = $htvcenter->file();
		$this->htvcenter    = $htvcenter;
		$id = $this->response->html->request()->get('appliance_id');
		$this->user = $htvcenter->user();
		$appliance = $this->htvcenter->appliance();
		$resource  = $this->htvcenter->resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->htvcenter->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
		$this->response->add('appliance_id', $id);
		$this->response->add('vmtype', $this->response->html->request()->get('vmtype'));

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
		$response = $this->add();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
				$this->controller->reload();
				$vmtype = $this->response->html->request()->get('vmtype');
				$foward_to_step = $this->user->wizard_step;
				$foward_to_step = 2;
				if($vmtype === 'kvm-vm-local') {
					if ($this->response->html->request()->get('localboot_image') !== '') {
						$foward_to_step = 4;
					}
				}
				if($vmtype === 'kvm-vm-net') {
					if ($this->response->html->request()->get('netboot_image') !== '') {
						$foward_to_step = 4;
					}
				}
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$foward_to_step.'&appliance_id='.$this->user->wizard_id.'&resource_id='.$response->resource_id
				);
			} else {
				if ($response->image_configured) {
					$this->response->redirect(
						$this->response->html->thisfile.'?base=appliance&appliance_msg='.$response->msg.'&resource_filter='.$response->resource_id
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
		
		$t = $this->response->html->template($this->tpldir.'/kvm-vm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);



		$t->add($this->lang['label'], 'label');
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
		$t->add($this->lang['lang_vnc'], 'lang_vnc');
		$t->add($this->lang['lang_browser'], 'lang_browser');
		$t->add($this->lang['lang_password_generate'], 'lang_password_generate');
		$t->add($this->lang['lang_password_show'], 'lang_password_show');
		$t->add($this->lang['lang_password_hide'], 'lang_password_hide');
		$t->add($action_add_vm_image, 'add_vm_image');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->actions_name, 'actions_name');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {



		//var_dump($_GET);
		$response = $this->get_response();
		$form     = $response->form;
		
			
		// handle no bridge error
		if(!isset($respnse->msg)) {
			// check vnc password
			$vnc = $form->get_request('vnc');
			if($vnc !== '' && $vnc !== $form->get_request('vnc_1')) {
				$form->set_error('vnc_1', $this->lang['error_vnc_password']);
			}
			if(isset($vnc) && $vnc !== '' && strlen($vnc) < 6) {
				$form->set_error('vnc', $this->lang['error_vnc_password_count']);
			}
			$vnckeymap = $form->get_request('vnc_keymap');
			$vnckeymap_parameter = '';
			if($vnc !== '') {
				$vnckeymap_parameter = ' -l '.$vnckeymap;
			}
			// disk interface
			$disk_interface = $form->get_request('disk_interface');
			$disk_interface_parameter = '';
			if($disk_interface !== '') {
				$disk_interface_parameter = ' -o '.$disk_interface;
			}
			$iso_path = '';
			if($form->get_request('boot') !== '' && $form->get_request('boot') === 'iso') {
				if($form->get_request('iso_path') === '') {
					$form->set_error('iso_path', $this->lang['error_iso_path']);
				} else {
					$iso_path = ' -i '.$form->get_request('iso_path');
				}
			}

			$image = new image();
			$vm_using_existing_image = false;
			if ($form->get_request('localboot_image') !== '') {
				$image->get_instance_by_id($form->get_request('localboot_image'));
				$vm_using_existing_image = true;
			} else if ($form->get_request('netboot_image') !== '') {
				$image->get_instance_by_id($form->get_request('netboot_image'));
				$vm_using_existing_image = true;
			}
			
			if(!$form->get_errors() && $this->response->submit()) {
				$errors = array();
				$name   = $form->get_request('name');
				$vmname = $name;
				if($form->get_request('boot') === '') {
					$errors[] = $this->lang['error_boot'];
				}

				$enabled = array();
				for($i = 1; $i < 5; $i++) {
					$enabled[$i] = true;
					if($form->get_request('net'.$i) !== '') {
						if($form->get_request('mac'.$i) === '') {
							$form->set_error('mac'.$i, $this->lang['error_mac']);
							$enabled[$i] = false;
						}
						if($form->get_request('nic'.$i) === '') {
							$form->set_error('nic'.$i, $this->lang['error_nic']);
							$enabled[$i] = false;
						}
						if($form->get_request('bridge'.$i) === '') {
							$form->set_error('bridge'.$i, $this->lang['error_bridge']);
							$enabled[$i] = false;
						}
					} else {
						$enabled[$i] = false;
					}
				}
				// check vm name
				if ($this->file->exists($this->statfile)) {
					$lines = explode("\n", $this->file->get_contents($this->statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$errors[] = sprintf($this->lang['error_exists'], $name);
								}
							}
						}
					}
				}
				if(count($errors) > 0 || $form->get_errors()) {
					$response->error = join('<br>', $errors);
				} else {
					$tables = $this->htvcenter->get('table');
					$resource = new resource();
					$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					
					$ip = rand(11, 1111); //"0.0.0.0";
					
					$mac = strtolower($form->get_request('mac'));
					// send command to the htvcenter-server
					$htvcenter = new htvcenter_server();
					$htvcenter->send_command('htvcenter_server_add_resource '.$id.' '.$mac.' '.$ip);
					// set resource type
					$vmtype = $this->response->html->request()->get('vmtype');
					if($vmtype === 'kvm-vm-net') {
						$virtualization = new virtualization();
						$virtualization->get_instance_by_type("kvm-vm-net");
					} else {
						$virtualization = new virtualization();
						$virtualization->get_instance_by_type("kvm-vm-local");
					}
					// add to htvcenter database
					$fields["resource_id"] = $id;
					$fields["resource_ip"] = $ip;
					$fields["resource_mac"] = $mac;
					$fields["resource_localboot"] = 0;
					$fields["resource_vtype"] = $virtualization->id;
					$fields["resource_vhostid"] = $this->resource->id;
					$fields["resource_vname"] = $name;
					$this->resource->add($fields);

					$command  = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm-vm create';
					$command .= ' -n '.$name;
					$command .= ' -y '.$vmtype;
					$command .= ' -m '.$mac;
					$command .= ' -r '.$form->get_request('memory');
					$command .= ' -c '.$form->get_request('cpus');
					$command .= ' -t '.$form->get_request('nic');
					$command .= ' -z '.$form->get_request('bridge');

					foreach($enabled as $key => $value) {
						if($value === true) {
							$command .= ' -m'.$key.' '.$form->get_request('mac'.$key);
							$command .= ' -t'.$key.' '.$form->get_request('nic'.$key);
							$command .= ' -z'.$key.' '.$form->get_request('bridge'.$key);
						}
					}

					if($form->get_request('cdrom')) {
						$command .= ' -cdrom '.$form->get_request('cdrom');
					}

					$command .= ' -b '.$form->get_request('boot');
					$command .= ' -v '.$form->get_request('vnc');
					$command .= $iso_path;
					$command .= $vnckeymap_parameter;
					$command .= $disk_interface_parameter;
					$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
					$command .= ' --htvcenter-ui-user '.$this->user->name;
					$command .= ' --htvcenter-cmd-mode regular';

					$this->resource->send_command($this->resource->ip, $command);
					$response->resource_id = $id;
					$response->msg = sprintf($this->lang['msg_added'], $name);
					
					// create server object
					$response->image_configured = false;
					if ($vm_using_existing_image) {
						$response->image_configured = true;
						// add/update server if any type of image was set
						$now=$_SERVER['REQUEST_TIME'];
						if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
							// update appliance for this VM if we are coming from the wizard
							$afields['appliance_resources'] = $id;
							$afields['appliance_kernelid'] = '1';
							$afields['appliance_imageid'] = $image->id;
							$afields["appliance_virtual"]= 0;
							$afields["appliance_virtualization"]=$virtualization->id;
							$afields['appliance_wizard'] = '';
							$afields['appliance_comment'] = 'KVM VM for resource '.$id;
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
							$afields['appliance_resources'] = $id;
							$afields['appliance_kernelid'] = '1';
							$afields['appliance_imageid'] = $image->id;
							$afields["appliance_virtual"]= 0;
							$afields["appliance_virtualization"]=$virtualization->id;
							$afields['appliance_wizard'] = '';
							$afields['appliance_comment'] = 'KVM VM for resource '.$id;
							$this->appliance->add($afields);
							// update state/start+stoptime
							$aufields['appliance_stoptime']=$now;
							$aufields['appliance_starttime']='';
							$aufields['appliance_state']='stopped';
							$this->appliance->update($new_appliance_id, $aufields);
						}
					}

				}
			} else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
		}


		//vlomes create:


		$names = $_GET['storagename'];
		$types = $_GET['storagetype'];
		$sizes = $_GET['storagesize'];

		$i = 1;
		foreach ($names as $key => $value) {
			# code...
			
				$name = $value;
				
			

				$command  = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm add';
				$command .= ' -n '.$name.' -m '.$sizes[$key];
				$command .= ' -o '.$types[$key];
				$command .= ' -t '.'kvm-bf-deployment'.' -v '.'storage1';
				$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';

				$statfile = $this->htvcenter->get('basedir').'/plugins/kvm/web/storage/'.'0'.'.'.'storage1'.'.lv.stat';
				if ($this->file->exists($statfile)) {
					$lines = explode("\n", $this->file->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$error = sprintf($this->lang['error_exists'], $name);
								}
							}
						}
					}
				}


			
					if($this->file->exists($statfile)) {
						$this->file->remove($statfile);
					}
					$this->resource->send_command($this->resource->ip, $command);
					

					// add check that volume $name is now in the statfile
					$created = false;
					$bf_volume_path = "";
					$lines = explode("\n", $this->file->get_contents($statfile));

					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$created = true;
									syslog(LOG_WARNING,"STORAGE CREATES: $name");
									$bf_volume_path = $line[2];
									break;
								}
							}
						}
					}

					
					// storage from "add" to 'idle' status:
					$devicename = '/var/lib/kvm/storage1/'.$name;


					$root_device = $devicename;
					if ($this->deployment->type == 'kvm-gluster-deployment') {
						$image_name = $this->response->html->request()->get('image_name');
					} else {
						$image_name = basename($root_device);
					}

					// check if image name is not in use yet
					$image = new image();
					$image->get_instance_by_name($image_name);
					if (strlen($image->id)) {
						$errors[] = sprintf($this->lang['error_exists'], $image_name);
					} else {
						$tables = $this->htvcenter->get('table');
						$image_fields = array();
						$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$image_fields['image_name'] = $image_name;
						$image_fields['image_type'] = 'kvm-bf-deployment';
						$image_fields['image_rootfstype'] = 'local';
						$image_fields['image_storageid'] = $this->storage->id;
						$image_fields['image_comment'] = "Image Object for volume $image_name";
						$image_fields['image_rootdevice'] = $root_device;
						$image = new image();
						$image->add($image_fields);
						$message[] = sprintf($this->lang['msg_added_image'], $image_name);
					}
					
					// --- end storage status changing ---

					$ipthis = $_SERVER["HTTP_HOST"];
		
		
		$i = $i+1;
		$file = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/tmp/disk'.$i;
		unlink($file);
		$fp = fopen($file, "w"); 
		$mytext = 'KVM_VM_DISK_'.$i.'="'.$devicename.'"'.PHP_EOL;
		$mytext .='KVM_VM_DISK_SIZE_'.$i.'="'.$sizes[$key].'"'.PHP_EOL;
		$test = fwrite($fp, $mytext); 
		
		$cmd = 'rm -rf /var/lib/kvm/htvcenter/'.$vmname.'/disk'.$i;
		$this->resource->send_command($this->resource->ip, $cmd);
		$cmd = 'sudo wget http://'.$ipthis.'/htvcenter/base/tmp/disk'.$i.' --user='.$this->htvcenter->admin()->name.' --password='.$this->htvcenter->admin()->password.' -P /var/lib/kvm/htvcenter/'.$vmname.'/';
		$this->resource->send_command($this->resource->ip, $cmd);
		//unlink($file);	
		
			
		}
		// --- end volumes create ---
		

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
		$htvcenter_SERVER_BASE_DIR = $this->htvcenter->get('basedir');
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add');

		$cpus[] = array("1", "1 CPU");
		$cpus[] = array("2", "2 CPUs");
		$cpus[] = array("4", "4 CPUs");
		$cpus[] = array("8", "8 CPUs");
		$cpus[] = array("16", "16 CPUs");

		$ram[] = array("256", "256 MB");
		$ram[] = array("512", "512 MB");
		$ram[] = array("1024", "1 GB");
		$ram[] = array("2048", "2 GB");
		$ram[] = array("4096", "4 GB");
		$ram[] = array("8192", "8 GB");
		$ram[] = array("16384", "16 GB");
		$ram[] = array("32768", "32 GB");
		$ram[] = array("65536", "64 GB");

		$nics[] = array("virtio", $this->lang['form_net_virtio']);
		$nics[] = array("e1000", $this->lang['form_net_e1000']);
		$nics[] = array("rtl8139", $this->lang['form_net_rtl8139']);

		$keymaps[] = array("en-us", "en-us");
		$keymaps[] = array("de", "de");
		$keymaps[] = array("es", "es");
		$keymaps[] = array("fr", "fr");
		$keymaps[] = array("it", "it");
		$keymaps[] = array("ja", "ja");
		$keymaps[] = array("nl", "nl");
		$keymaps[] = array("ru", "ru");
		$keymaps[] = array("none", "none");

		$disk_interfaces[] = array("virtio", "Virtio");
		$disk_interfaces[] = array("ide", "IDE");

		$swap_select_arr[] = array('1024', '1 GB');
		$swap_select_arr[] = array('2048', '2 GB');
		$swap_select_arr[] = array('4096', '4 GB');

		// if we come from the wizard suggest the server name
		$vm_name_suggestion = '';
		if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($this->user->wizard_id);
			$vm_name_suggestion = $appliance->name;
		}

		// get a list of existing kvm localboot images to select
		$existing_image_arr = array();
		$image = new image();
		$image_image_id_ar = $image->get_ids_by_type('kvm-lvm-deployment');
		foreach ($image_image_id_ar as $iid_ar) {
			$image_id = $iid_ar['image_id'];
			$image->get_instance_by_id($image_id);
			$existing_image_arr[] = array($image->id, $image->name);
		}
		$image_image_id_ar = $image->get_ids_by_type('kvm-bf-deployment');
		foreach ($image_image_id_ar as $iid_ar) {
			$image_id = $iid_ar['image_id'];
			$image->get_instance_by_id($image_id);
			$existing_image_arr[] = array($image->id, $image->name);
		}
		$image_image_id_ar = $image->get_ids_by_type('kvm-gluster-deployment');
		foreach ($image_image_id_ar as $iid_ar) {
			$image_id = $iid_ar['image_id'];
			$image->get_instance_by_id($image_id);
			$existing_image_arr[] = array($image->id, $image->name);
		}
		$existing_image_arr[] = array('', '');
		
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
		
		
		$file = $htvcenter_SERVER_BASE_DIR.'/plugins/kvm/web/kvm-stat/'.$this->resource->id.'.bridge_config';
		$data = htvcenter_parse_conf($file);
		$bridges = array();
		$bridge_list = $data['htvcenter_KVM_BRIDGES'];
		$bridge_list = rtrim($bridge_list, ":");
		$bridge_array = explode(':', $bridge_list);

		// handle no bridge error
		if(isset($bridge_array[0]) && $bridge_array[0] !== '') {
			foreach ($bridge_array as $b) {
				$bridges[] = array($b, $b);
			}

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$rescheck = array();
			$statfile = $this->htvcenter->get('basedir').'/plugins/kvm/web/storage/0.vg.stat';
			if ($this->file->exists($statfile)) {
					$lines = explode("\n", $this->file->get_contents($statfile));

					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$rescheck[] = $line;
							}
						}
					}
			}

			
			if (isset($rescheck[0][6])) {
				$this->freemb = $rescheck[0][6];
			}

	
			$d['name']['label']                             = $this->lang['form_name'];
			$d['freemb'] = $this->freemb;
			preg_match_all('@\d+@', $this->freemb, $matches);
			
			$d['freembplain'] = $matches[0][0];
			$d['name']['required']                          = true;
			$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
			$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
			$d['name']['object']['type']                    = 'htmlobject_input';
			$d['name']['object']['attrib']['name']          = 'name';
			$d['name']['object']['attrib']['id']            = 'name';
			$d['name']['object']['attrib']['type']          = 'text';
			$d['name']['object']['attrib']['css']           = 'namegen';
			$d['name']['object']['attrib']['customattribs'] = 'data-prefix="kvm" data-length="6"';
			$d['name']['object']['attrib']['value']		    = $vm_name_suggestion;
			$d['name']['object']['attrib']['maxlength']     = 50;

			$d['cpus']['label']                       = $this->lang['form_cpus'];
			$d['cpus']['required']                    = true;
			$d['cpus']['object']['type']              = 'htmlobject_select';
			$d['cpus']['object']['attrib']['name']    = 'cpus';
			$d['cpus']['object']['attrib']['index']   = array(0,1);
			$d['cpus']['object']['attrib']['options'] = $cpus;

			$d['memory']['label']                        = $this->lang['form_memory'];
			$d['memory']['required']                     = true;
			$d['memory']['object']['type']               = 'htmlobject_select';
			$d['memory']['object']['attrib']['name']     = 'memory';
			$d['memory']['object']['attrib']['index']    = array(0,1);
			$d['memory']['object']['attrib']['options']  = $ram;
			$d['memory']['object']['attrib']['selected'] = array(512);

			$vmtype = $this->response->html->request()->get('vmtype');
			if($vmtype === 'kvm-vm-net') {
				
				$d['netboot_image']['label']						= $this->lang['form_existing_disk'];
				$d['netboot_image']['object']['type']				= 'htmlobject_select';
				$d['netboot_image']['object']['attrib']['index']	= array(0,1);
				$d['netboot_image']['object']['attrib']['id']		= 'netboot_image';
				$d['netboot_image']['object']['attrib']['name']		= 'netboot_image';
				$d['netboot_image']['object']['attrib']['options']	= $existing_netboot_image_arr;
				$d['netboot_image']['object']['attrib']['selected']	= array('');

				$d['disk_interface'] = "";
				$d['localboot_image'] = "";
				$d['cdrom_iso_path'] = "";
				$d['cdrom_button'] = "";
				
			} else {
				
				$d['localboot_image']['label']						= $this->lang['form_existing_disk'];
				$d['localboot_image']['object']['type']				= 'htmlobject_select';
				$d['localboot_image']['object']['attrib']['index']	= array(0,1);
				$d['localboot_image']['object']['attrib']['id']		= 'localboot_image';
				$d['localboot_image']['object']['attrib']['name']		= 'localboot_image';
				$d['localboot_image']['object']['attrib']['options']	= $existing_image_arr;
				$d['localboot_image']['object']['attrib']['selected']	= array('');

				$d['disk_interface']['label']                       = $this->lang['form_disk_interface'];
				$d['disk_interface']['required']                    = true;
				$d['disk_interface']['object']['type']              = 'htmlobject_select';
				$d['disk_interface']['object']['attrib']['name']    = 'disk_interface';
				$d['disk_interface']['object']['attrib']['id']      = 'disk_interface';
				$d['disk_interface']['object']['attrib']['index']   = array(0,1);
				$d['disk_interface']['object']['attrib']['options'] = $disk_interfaces;

				$d['cdrom_iso_path']['label']                    = $this->lang['form_cdrom'];
				$d['cdrom_iso_path']['object']['type']           = 'htmlobject_input';
				$d['cdrom_iso_path']['object']['attrib']['type'] = 'text';
				$d['cdrom_iso_path']['object']['attrib']['id']   = 'cdrom';
				$d['cdrom_iso_path']['object']['attrib']['name'] = 'cdrom';

				$d['cdrom_button']['static']                      = true;
				$d['cdrom_button']['object']['type']              = 'htmlobject_input';
				$d['cdrom_button']['object']['attrib']['type']    = 'button';
				$d['cdrom_button']['object']['attrib']['name']    = 'cdrom_button';
				$d['cdrom_button']['object']['attrib']['id']      = 'cdrom_button';
				$d['cdrom_button']['object']['attrib']['css']     = 'browse-button';
				$d['cdrom_button']['object']['attrib']['handler'] = 'onclick="filepicker.init(\'cdrom\'); return false;"';
				$d['cdrom_button']['object']['attrib']['style']   = "display:none;";
				$d['cdrom_button']['object']['attrib']['value']   = 'Browse';
				
				$d['netboot_image'] = "";
				
			}

			$mac = '';
			if(isset($this->resource)) {
				$this->resource->generate_mac();
				$mac = $this->resource->mac;
			}
			
			$d['net0']['label']                        = $this->lang['lang_net_0'];
			$d['net0']['object']['type']               = 'htmlobject_input';
			$d['net0']['object']['attrib']['type']     = 'checkbox';
			$d['net0']['object']['attrib']['id']       = 'net0';
			$d['net0']['object']['attrib']['name']     = 'net0';
			$d['net0']['object']['attrib']['value']    = 'enabled';
			$d['net0']['object']['attrib']['checked']  = true;
			$d['net0']['object']['attrib']['disabled'] = true;

			$d['mac']['label']                         = $this->lang['form_mac'];
			$d['mac']['required']                      = true;
			$d['mac']['object']['type']                = 'htmlobject_input';
			$d['mac']['object']['attrib']['name']      = 'mac';
			$d['mac']['object']['attrib']['type']      = 'text';
			$d['mac']['object']['attrib']['value']     = $mac;
			$d['mac']['object']['attrib']['maxlength'] = 50;

			$d['bridge']['label']                       = $this->lang['form_bridge'];
			$d['bridge']['required']                    = true;
			$d['bridge']['object']['type']              = 'htmlobject_select';
			$d['bridge']['object']['attrib']['name']    = 'bridge';
			$d['bridge']['object']['attrib']['index']   = array(0,1);
			$d['bridge']['object']['attrib']['options'] = $bridges;
			
			
			// check ip-mgmt
			#$ip_mgmt_list_per_user_arr[] = array("value" => -2, "label" => "Auto");
			#$ip_mgmt_list_per_user_arr[] = array("value" => -1, "label" => "None");
			//$ip_network_names = array(" -- ", "Select one IP Network");
			/*if (!strcmp($show_ip_mgmt, "true")) {
				if (file_exists($this->rootdir."/plugins/ip-mgmt/.running")) {
				}
			}*/
			
			/*require_once $this->rootdir."/plugins/ip-mgmt/class/ip-mgmt.class.php";
			$ip_mgmt = new ip_mgmt();
			$ip_network_name = $ip_mgmt->get_names("");*/
			//echo "KVM - MAN";
			//echo "<pre>" . print_r ($ip_network_name) . "</pre>";
			//array_pop($ip_mgmt_list_per_user);
			//foreach($ip_network_name as $list) {
				//$ip_mgmt_id = $list['ip_mgmt_id'];
				//$ip_mgmt_name = trim($list['ip_mgmt_name']);
				//$ip_mgmt_address = trim($list['ip_mgmt_address']);
				//$ip_network_names[] = array($list, $list);
				//}
			
			/*$d['ip_network']['label']                       = "Network";
			$d['ip_network']['required']                    = true;
			$d['ip_network']['object']['type']              = 'htmlobject_select';
			$d['ip_network']['object']['attrib']['name']    = 'Network';
			$d['ip_network']['object']['attrib']['index']   = array(0,1);
			$d['ip_network']['object']['attrib']['options'] = $ip_network_names;*/



			$d['nic']['label']                         = $this->lang['form_netdevice'];
			$d['nic']['required']                      = true;
			$d['nic']['object']['type']                = 'htmlobject_select';
			$d['nic']['object']['attrib']['name']      = 'nic';
			$d['nic']['object']['attrib']['index']     = array(0,1);
			$d['nic']['object']['attrib']['options']   = $nics;

			// net 1
			if(isset($this->resource)) {
				$this->resource->generate_mac();
				$mac = $this->resource->mac;
			}

			$d['net1']['label']                       = $this->lang['lang_net_1'];
			$d['net1']['object']['type']              = 'htmlobject_input';
			$d['net1']['object']['attrib']['type']    = 'checkbox';
			$d['net1']['object']['attrib']['id']      = 'net1';
			$d['net1']['object']['attrib']['name']    = 'net1';
			$d['net1']['object']['attrib']['value']   = 'enabled';
			$d['net1']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

			$d['mac1']['label']                         = $this->lang['form_mac'];
			$d['mac1']['object']['type']                = 'htmlobject_input';
			$d['mac1']['object']['attrib']['name']      = 'mac1';
			$d['mac1']['object']['attrib']['type']      = 'text';
			$d['mac1']['object']['attrib']['value']     = $mac;
			$d['mac1']['object']['attrib']['maxlength'] = 50;

			$d['bridge1']['label']                       = $this->lang['form_bridge'];
			$d['bridge1']['object']['type']              = 'htmlobject_select';
			$d['bridge1']['object']['attrib']['name']    = 'bridge1';
			$d['bridge1']['object']['attrib']['index']   = array(0,1);
			$d['bridge1']['object']['attrib']['options'] = $bridges;

			$d['nic1']['label']                         = $this->lang['form_netdevice'];
			$d['nic1']['object']['type']                = 'htmlobject_select';
			$d['nic1']['object']['attrib']['name']      = 'nic1';
			$d['nic1']['object']['attrib']['index']     = array(0,1);
			$d['nic1']['object']['attrib']['options']   = $nics;

			// net 2
			if(isset($this->resource)) {
				$this->resource->generate_mac();
				$mac = $this->resource->mac;
			}

			$d['net2']['label']                     = $this->lang['lang_net_2'];
			$d['net2']['object']['type']            = 'htmlobject_input';
			$d['net2']['object']['attrib']['type']  = 'checkbox';
			$d['net2']['object']['attrib']['id']    = 'net2';
			$d['net2']['object']['attrib']['name']  = 'net2';
			$d['net2']['object']['attrib']['value'] = 'enabled';
			$d['net2']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

			$d['mac2']['label']                         = $this->lang['form_mac'];
			$d['mac2']['object']['type']                = 'htmlobject_input';
			$d['mac2']['object']['attrib']['name']      = 'mac2';
			$d['mac2']['object']['attrib']['type']      = 'text';
			$d['mac2']['object']['attrib']['value']     = $mac;
			$d['mac2']['object']['attrib']['maxlength'] = 50;

			$d['bridge2']['label']                       = $this->lang['form_bridge'];
			$d['bridge2']['object']['type']              = 'htmlobject_select';
			$d['bridge2']['object']['attrib']['name']    = 'bridge2';
			$d['bridge2']['object']['attrib']['index']   = array(0,1);
			$d['bridge2']['object']['attrib']['options'] = $bridges;

			$d['nic2']['label']                         = $this->lang['form_netdevice'];
			$d['nic2']['object']['type']                = 'htmlobject_select';
			$d['nic2']['object']['attrib']['name']      = 'nic2';
			$d['nic2']['object']['attrib']['index']     = array(0,1);
			$d['nic2']['object']['attrib']['options']   = $nics;

			// net 3
			if(isset($this->resource)) {
				$this->resource->generate_mac();
				$mac = $this->resource->mac;
			}

			$d['net3']['label']                     = $this->lang['lang_net_3'];
			$d['net3']['object']['type']            = 'htmlobject_input';
			$d['net3']['object']['attrib']['type']  = 'checkbox';
			$d['net3']['object']['attrib']['id']    = 'net3';
			$d['net3']['object']['attrib']['name']  = 'net3';
			$d['net3']['object']['attrib']['value'] = 'enabled';
			$d['net3']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

			$d['mac3']['label']                         = $this->lang['form_mac'];
			$d['mac3']['object']['type']                = 'htmlobject_input';
			$d['mac3']['object']['attrib']['name']      = 'mac3';
			$d['mac3']['object']['attrib']['type']      = 'text';
			$d['mac3']['object']['attrib']['value']     = $mac;
			$d['mac3']['object']['attrib']['maxlength'] = 50;

			$d['bridge3']['label']                       = $this->lang['form_bridge'];
			$d['bridge3']['object']['type']              = 'htmlobject_select';
			$d['bridge3']['object']['attrib']['name']    = 'bridge3';
			$d['bridge3']['object']['attrib']['index']   = array(0,1);
			$d['bridge3']['object']['attrib']['options'] = $bridges;

			$d['nic3']['label']                         = $this->lang['form_netdevice'];
			$d['nic3']['object']['type']                = 'htmlobject_select';
			$d['nic3']['object']['attrib']['name']      = 'nic3';
			$d['nic3']['object']['attrib']['index']     = array(0,1);
			$d['nic3']['object']['attrib']['options']   = $nics;

			// net 4
			if(isset($this->resource)) {
				$this->resource->generate_mac();
				$mac = $this->resource->mac;
			}

			$d['net4']['label']                     = $this->lang['lang_net_4'];
			$d['net4']['object']['type']            = 'htmlobject_input';
			$d['net4']['object']['attrib']['type']  = 'checkbox';
			$d['net4']['object']['attrib']['name']  = 'net4';
			$d['net4']['object']['attrib']['id']    = 'net4';
			$d['net4']['object']['attrib']['value'] = 'enabled';
			$d['net4']['object']['attrib']['handler'] = 'onchange="nettoggle(this);"';

			$d['mac4']['label']                         = $this->lang['form_mac'];
			$d['mac4']['object']['type']                = 'htmlobject_input';
			$d['mac4']['object']['attrib']['name']      = 'mac4';
			$d['mac4']['object']['attrib']['type']      = 'text';
			$d['mac4']['object']['attrib']['value']     = $mac;
			$d['mac4']['object']['attrib']['maxlength'] = 50;

			$d['bridge4']['label']                       = $this->lang['form_bridge'];
			$d['bridge4']['object']['type']              = 'htmlobject_select';
			$d['bridge4']['object']['attrib']['name']    = 'bridge4';
			$d['bridge4']['object']['attrib']['index']   = array(0,1);
			$d['bridge4']['object']['attrib']['options'] = $bridges;

			$d['nic4']['label']                         = $this->lang['form_netdevice'];
			$d['nic4']['object']['type']                = 'htmlobject_select';
			$d['nic4']['object']['attrib']['name']      = 'nic4';
			$d['nic4']['object']['attrib']['index']     = array(0,1);
			$d['nic4']['object']['attrib']['options']   = $nics;

			// boot from
			$d['boot_cd'] = '';
			$d['boot_iso'] = '';
			$d['boot_iso_path'] = '';
			$d['boot_local'] = '';
			$d['browse_button'] = '';
			if($vmtype !== 'kvm-vm-net') {
				$d['boot_cd']['label']                     = $this->lang['form_boot_cd'];
				$d['boot_cd']['object']['type']            = 'htmlobject_input';
				$d['boot_cd']['object']['attrib']['type']  = 'radio';
				$d['boot_cd']['object']['attrib']['name']  = 'boot';
				$d['boot_cd']['object']['attrib']['value'] = 'cdrom';

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
				$d['browse_button']['object']['attrib']['handler'] = 'onclick="filepicker.init(\'iso_path\'); return false;"';
				$d['browse_button']['object']['attrib']['style']   = "display:none;";
				$d['browse_button']['object']['attrib']['value']   = $this->lang['lang_browse'];
			}
			$d['boot_net']['label']                     = $this->lang['form_boot_net'];
			$d['boot_net']['object']['type']            = 'htmlobject_input';
			$d['boot_net']['object']['attrib']['type']  = 'radio';
			$d['boot_net']['object']['attrib']['name']  = 'boot';
			$d['boot_net']['object']['attrib']['value'] = 'network';
			if($vmtype === 'kvm-vm-net') {
				$d['boot_net']['object']['attrib']['checked'] = true;
			}

			$d['vnc']['label']                         = $this->lang['form_vnc'];
			$d['vnc']['required']                      = true;
			$d['vnc']['object']['type']                = 'htmlobject_input';
			$d['vnc']['object']['attrib']['name']      = 'vnc';
			$d['vnc']['object']['attrib']['id']        = 'vnc';
			$d['vnc']['object']['attrib']['type']      = 'password';
			$d['vnc']['object']['attrib']['value']     = '';
			$d['vnc']['object']['attrib']['maxlength'] = 50;

			$d['vnc_1']['label']                         = $this->lang['form_vnc_repeat'];
			$d['vnc_1']['required']                      = true;
			$d['vnc_1']['object']['type']                = 'htmlobject_input';
			$d['vnc_1']['object']['attrib']['name']      = 'vnc_1';
			$d['vnc_1']['object']['attrib']['id']        = 'vnc_1';
			$d['vnc_1']['object']['attrib']['value']     = '';
			$d['vnc_1']['object']['attrib']['type']      = 'password';
			$d['vnc_1']['object']['attrib']['maxlength'] = 50;

			$d['vnc_keymap']['label']                       = $this->lang['form_vnc_keymap'];
			$d['vnc_keymap']['object']['type']              = 'htmlobject_select';
			$d['vnc_keymap']['object']['attrib']['name']    = 'vnc_keymap';
			$d['vnc_keymap']['object']['attrib']['index']   = array(0,1);
			$d['vnc_keymap']['object']['attrib']['options'] = $keymaps;

			$form->add($d);
			$response->form = $form;

		} else {
			$response->msg = $this->lang['error_no_bridge'];
			$response->form = $form;
		}
		return $response;
	}

}
?>
