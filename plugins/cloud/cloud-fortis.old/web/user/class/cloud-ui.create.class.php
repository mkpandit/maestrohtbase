<?php
/**
 * Create Cloud User Request
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/

class cloud_ui_create
{
var $identifier_name;
var $lang;
var $actions_name;

var $cloud_max_applications = 20;
var $cloud_max_network = 4;

/**
* user
* @access public
* @var string
*/
var $user;
/**
* cloud-id
* @access public
* @var int
*/
var $cloud_id;
/**
* config
* @access public
* @var object
*/
var $config;
/**
* use api
* @access public
* @var string
*/
var $use_api = true;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response  = $response;
		$this->rootdir   = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->htvcenter   = $htvcenter;
		$this->clouduser = $this->htvcenter->user();
		// include classes and prepare ojects
		require_once $this->rootdir."/plugins/cloud/class/clouduserslimits.class.php";
		$this->clouduserlimits = new clouduserlimits();
		$this->clouduserlimits->get_instance_by_cu_id($this->htvcenter->user()->id);

		require_once $this->rootdir."/plugins/cloud/class/cloudselector.class.php";
		$this->cloudselector = new cloudselector();
		require_once $this->rootdir."/plugins/cloud/class/cloudprivateimage.class.php";
		$this->cloudprivateimage = new cloudprivateimage();
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloudrequest = new cloudrequest();
		require_once $this->rootdir."/plugins/cloud/class/cloudmailer.class.php";
		$this->cloudmailer = new cloudmailer();
		require_once $this->rootdir."/plugins/cloud/class/cloudprofile.class.php";
		$this->cloudprofile = new cloudprofile();
		require_once $this->rootdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloudconfig = new cloudconfig();
		
		require_once "cloud.limits.class.php";
		$this->cloud_limits = new cloud_limits($this->htvcenter, $this->cloudconfig, $this->clouduserlimits, $this->cloudrequest);
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$response = $this->form();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$param = '';
			if(isset($response->saved_profile)) {
				$param = '&profile='.$response->saved_profile;
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'create', $this->message_param, $response->msg).$param
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'appliances', $this->message_param, $response->msg)
				);
			}
		}

		$t = $this->response->html->template($this->tpldir."/cloud-ui.create.tpl.php");

		// Billing
		$billing = $this->cloudconfig->get_value_by_key('cloud_billing_enabled');
		if ($billing === 'true') {
			$t->add('block', 'display_price_list');
		} else {
			$t->add('none', 'display_price_list');
		}

		// check resource and max_apps_per_user
		$apps = $this->cloudrequest->get_all_active_ids($this->clouduser->id);
		if(count($apps) >= $this->cloud_limits->max('resource')) {
			$t->add('none', 'display_component_table');
			$t->add('block', 'display_error');
			$t->add('none', 'display_price_list');
			$t->add(sprintf($this->lang['error_resource_limit'], $this->cloud_limits->max('resource')), 'error');
		}
		else if($this->cloudconfig->get_value_by_key('cloud_enabled') === 'false') {
			$t->add('none', 'display_component_table');
			$t->add('block', 'display_error');
			$t->add('none', 'display_price_list');
			$t->add($this->lang['error_cloud_disabled'], 'error');
		}
		else if($billing === 'true' && $this->clouduser->ccunits < 1) {
			$t->add('none', 'display_component_table');
			$t->add('block', 'display_error');
			$t->add('none', 'display_price_list');
			$t->add($this->lang['error_ccus_low'], 'error');
		} else {
			$t->add('block', 'display_component_table');
			$t->add('none', 'display_error');
			$t->add('', 'error_resource_limit');
		}

		// Private images
		$a = '';
		if (!strcmp($this->cloudconfig->get_value_by_key('show_private_image'), "true")) {
			$a = $this->response->html->a();
			$a->label = $this->lang['label_private_images'];
			$a->href  = $this->response->get_url($this->actions_name, 'images');
			$a = '<li>'.$a->get_string().'</li>';
		}
		$t->add($a, "private_images_link");

		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['table_components'], 'table_components');
		$t->add($this->lang['table_ccus'], 'table_ccus');
		$t->add($this->lang['table_ips'], 'table_ips');
		$t->add($this->lang['price_hour'],'price_hour');
		$t->add($this->lang['price_day'],'price_day');
		$t->add($this->lang['price_month'],'price_month');
		$t->add($this->lang['ccu_per_hour'],'ccu_per_hour');

		$t->add($response->form->get_elements());
		$t->group_elements(array('param_' => 'form', 'cloud_application_select_' => 'cloud_applications'));

		// Profiles
		$a = $this->response->html->a();
		$a->label = $this->lang['label_profiles'];
		$a->css   = 'last';
		$a->href  = $this->response->get_url($this->actions_name, 'profiles');
		$t->add($a, 'profiles_link');
		
		$profiles = $this->cloudprofile->display_overview_per_user($this->clouduser->id, 'ASC');
		$profile_action = '';
		/*foreach ($profiles as $k => $v) {
			$a = $this->response->html->a();
			$a->label   = $v['pr_name'];
			$a->href    = $this->response->get_url($this->actions_name, 'create').'&profile='.$v['pr_id'];
			if($this->response->html->request()->get('profile') === $v['pr_id']) {
				$a->css = 'selected';
			}
			$profile_action .= $a->get_string().'<br>';
		}*/
		$profiles_select = '<select  id="profiles_select">';
		foreach ($profiles as $k => $v) {
				if($this->response->html->request()->get('profile') === $v['pr_id']) {
					$selected = 'selected="true"';
				} else {
					$selected = '';
				}
			$hrefo = $this->response->get_url($this->actions_name, 'create').'&profile='.$v['pr_id'];
			$profiles_select .= '<option value="'.$hrefo.'" '.$selected.'>'.$v['pr_name'].'</option>';
		}
		$profiles_select .='</select>';
		$t->add($profiles_select, 'profiles');

		// add js image switcher data
		$t->add($this->js_resources,'js_formbuilder');
		
		// api switch
		$this->use_api === true ? $use_api = 'true' : $use_api = 'false'; 
		$t->add('var use_api = '.$use_api.';','js_use_api');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Create Cloud User Request
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function form() {
		$errors  = array();
		$message = array();
		$response = $this->get_response();
		
		$form     = $response->form;


		if(!$form->get_errors() && $response->submit()) {
			$data = $form->get_request();



					//size checking:
	
			$username = $this->htvcenter->user()->name;
			$query = "SELECT `size` FROM `cloud_volumes` WHERE `user_name` = '$username'";
			$res = mysql_query($query);
				
			$volsum = 0;
			while($rez = mysql_fetch_row($res)) {
				$volsum = $rez[0] + $volsum;
			}

			
			$newsize = (int) $data['cloud_disk_select'];
			require_once "cloud.limits.class.php";
			$this->cloud_limits = new cloud_limits($this->htvcenter, $this->cloudconfig, $this->clouduserlimits, $this->cloudrequest);
			$limits = $this->cloud_limits->free('disk');
			
			$limits = $limits - $volsum;
		
			if ($newsize > $limits) {
				$space = $limits/1000;
				$space = $space.'GB';
				$errors[] = sprintf('You have not got free disk space for instance creation');
				$form->set_error('You have not got free disk space for instance creation');
				$response->msg = sprintf('You have not got free disk space for instance creation! Available space is '.$space);
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'create', $this->message_param, $response->msg).$param
				);
				return $response;
			}
			
	

		// --- end size checking ---




			// check image fits virtualization
			$virt = $this->htvcenter->virtualization();
			$virt->get_instance_by_id($data['cloud_virtualization_select']);
			$tmp = $virt->type;
			// tag network vms
			if(strstr($tmp, "-net")) {
				$tmp = 'vm-net';
			}
			// store virtualization type
			$virt_types[$tmp] = $virt->get_plugin_name();
			$img = $this->htvcenter->image();
			$img->get_instance_by_id($data['cloud_image_select']);
			if($this->__get_virt_tag($img, $virt_types) === '') {
				$form->set_error('cloud_image_select', $this->lang['error_image_no_fit']);
			}
			// check limits
			if($data['cloud_memory_select'] > $this->cloud_limits->free('memory')) {
				$form->set_error('cloud_memory_select', $this->lang['error_limit_exceeded']);
			}
			if($data['cloud_disk_select'] > $this->cloud_limits->free('disk')) {
				$form->set_error('cloud_disk_select', $this->lang['error_limit_exceeded']);
			}
			if($data['cloud_network_select'] > $this->cloud_limits->free('network')) {
				$form->set_error('cloud_network_select', $this->lang['error_limit_exceeded']);
			}
			if($data['cloud_cpu_select'] > $this->cloud_limits->free('cpu')) {
				$form->set_error('cloud_cpu_select', $this->lang['error_limit_exceeded']);
			}
			// check hostname
			if(isset($data['cloud_hostname_input'])) {
				$chk_hostname = $this->htvcenter->appliance();
				$chk_hostname->get_instance_by_name($data['cloud_hostname_input']);
				if ($chk_hostname->id > 0) {
					$form->set_error('cloud_hostname_input', sprintf($this->lang['error_hostname'], $data['cloud_hostname_input']));
				}
			}



			if(!$form->get_errors()) {

				$application_groups_str = '';
				$ip_mgmt_config_str = '';
				// add request
				$now = $_SERVER['REQUEST_TIME'];
				$cr["cr_cu_id"] = $this->clouduser->id;
				$cr['cr_start'] = $now;
				$cr['cr_request_time'] = $now;
				$cr['cr_stop'] = $now + 830000000;
				$cr['cr_resource_quantity'] = 1;
				// form data
				$cr['cr_resource_type_req'] = $data['cloud_virtualization_select'];
				$cr['cr_kernel_id'] = $data['cloud_kernel_select'];
				$cr['cr_image_id'] = $data['cloud_image_select'];
				$cr['cr_ram_req'] = $data['cloud_memory_select'];
				$cr['cr_cpu_req'] = $data['cloud_cpu_select'];
				$cr['cr_disk_req'] = $data['cloud_disk_select'];
				$cr['cr_network_req'] = $data['cloud_network_select'];

				// capabilities input
				if(isset($data['cloud_appliance_capabilities'])) {
					$cr['cr_appliance_capabilities'] = $data['cloud_appliance_capabilities'];
				}
				// hostname input
				if (isset($data['cloud_hostname_input'])) {
					$cr['cr_appliance_hostname'] = $data['cloud_hostname_input'];
				}
				// apps
				for ($a = 0; $a < $this->cloud_max_applications; $a++) {
					if (isset($data['cloud_application_select_'.$a])) {
						$application_groups_str .= $data['cloud_application_select_'.$a].",";
					}
				}
				$application_groups_str = rtrim($application_groups_str, ",");
				$cr['cr_puppet_groups'] = $application_groups_str;
				// ips
				$max_network_interfaces = $this->cloudconfig->get_value_by_key('max_network_interfaces');
				for ($a = 0; $a <= $max_network_interfaces; $a++) {
					if (isset($data['cloud_ip_select_'.$a])) {
						$ip_mgmt_id = $data['cloud_ip_select_'.$a];
						if ($ip_mgmt_id != -1) {
							$nic_no = $a + 1;
							$ip_mgmt_config_str .= $nic_no.":".$ip_mgmt_id.",";
						}
					}
				}
				$ip_mgmt_config_str = rtrim($ip_mgmt_config_str, ",");

				$cr['cr_ip_mgmt'] = $ip_mgmt_config_str;
			
				// ha
				if (isset($data['cloud_ha_select'])) {
					$cr["cr_ha_req"] = 1;
				}
				// clone on deploy
				$clone_on_deploy = $this->cloudconfig->get_value_by_key('default_clone_on_deploy');
				if (!strcmp($clone_on_deploy, "true")) {
					$cr["cr_shared_req"] = 1;
				} else {
					$cr["cr_shared_req"] = 0;
				}
			
				// save as profile or request directly
				if (isset($data['cloud_profile_name'])) {
					$profile_name = $data['cloud_profile_name'];
					// check profile name not in use
					$profiles = $this->cloudprofile->display_overview_per_user($this->clouduser->id, 'ASC');
					if(count($profiles) > 0) {
						foreach($profiles as $profile) {
							if ($profile['pr_name'] === $profile_name) {
								$errors[] = sprintf($this->lang['msg_profile_in_use'], $profile_name);
								break;
							}
						}
					}
					// check max profile number
					$pr_count = $this->cloudprofile->get_count_per_user($this->clouduser->id);
					if ($pr_count >= $this->cloudprofile->max_profile_count) {
						$errors[] = sprintf($this->lang['error_max_profiles'],$this->cloudprofile->max_profile_count);
					}
					// add profile


		

		

					if(count($errors) === 0) {
						// remap fields from cr to pr
						$pr['pr_request_time'] = $cr['cr_request_time'];
						$pr['pr_start'] = $cr['cr_start'];
						$pr['pr_stop'] = $cr['cr_stop'];
						$pr['pr_kernel_id'] = $cr['cr_kernel_id'];
						$pr['pr_image_id'] = $cr['cr_image_id'];
						$pr['pr_ram_req'] = $cr['cr_ram_req'];
						$pr['pr_cpu_req'] = $cr['cr_cpu_req'];
						$pr['pr_disk_req'] = $cr['cr_disk_req'];
						$pr['pr_network_req'] = $cr['cr_network_req'];
						$pr['pr_resource_quantity'] = $cr['cr_resource_quantity'];
						$pr['pr_resource_type_req'] = $cr['cr_resource_type_req'];
						if(isset($cr['cr_ha_req'])) {
							$pr['pr_ha_req'] = $cr['cr_ha_req'];
						}
						$pr['pr_shared_req'] = $cr['cr_shared_req'];
						$pr['pr_puppet_groups'] = $cr['cr_puppet_groups'];
						$pr['pr_ip_mgmt'] = $cr['cr_ip_mgmt'];
						$pr['pr_name'] = $profile_name;
						// hostname
						if (isset($cr['cr_appliance_hostname'])) {
							$pr['pr_appliance_hostname'] = $cr['cr_appliance_hostname'];
						}
						// capabilities
						if(isset($cr['cr_appliance_capabilities'])) {
							$pr['pr_appliance_capabilities'] = $cr['cr_appliance_capabilities'];
						}
						$pr['pr_cu_id'] = $this->clouduser->id;
						$pr['pr_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$this->cloudprofile->add($pr);
					
						$response->msg = sprintf($this->lang['msg_saved_profile'], $profile_name);
						$response->saved_profile = $pr['pr_id'];
					} else {
						$msg = array_merge($errors, $message);
						$response->error = implode('<br>', $msg);
					}
				
				} else {
					$cr['cr_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$this->cloudrequest->add($cr);
					// mail to admin
					$cc_admin_email = $this->cloudconfig->get_value_by_key('cloud_admin_email');
					$this->cloudmailer->to = $cc_admin_email;
					$this->cloudmailer->from = $cc_admin_email;
					$this->cloudmailer->subject = sprintf($this->lang['mailer_create_subject'], $this->clouduser->name);
					$this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
					$arr = array('@@USER@@' => $this->clouduser->name, '@@ID@@' => $cr['cr_id'], '@@htvcenter_SERVER_IP_ADDRESS@@' => $_SERVER['SERVER_NAME'], '@@CLOUDADMIN@@' => $cc_admin_email);
					$this->cloudmailer->var_array = $arr;
					$this->cloudmailer->send();
					// success msg
					$response->msg = $this->lang['msg_created'];
				}
			} else {
				$response->error = implode('<br>', $form->get_errors());
			}
		} elseif ($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}









		

		

		//vlomes create:


		$names = $_GET['storagename'];
		$types = $_GET['storagetype'];
		$sizes = $_GET['storagesize'];
		
		/*
		$rest = $cr['cr_resource_type_req'];
		$sql = "SELECT `virtualization_type` FROM `virtualization_info` WHERE `virtualization_id` = '$rest'";
		$res = mysql_query($sql);
		while( $rez = mysql_fetch_assoc($res) ) {
			$ttpe = $rez["virtualization_type"];
		}

		if ($ttpe == 'kvm-vm-local') {
			$sql = "SELECT `virtualization_id` FROM `virtualization_info` WHERE `virtualization_type` = 'kvm'";
			$res = mysql_query($sql);
			while( $rez = mysql_fetch_assoc($res) ) {
				$apl_vid = $rez["virtualization_id"];
			}

			$sql = "SELECT `appliance_id` FROM `appliance_info` WHERE `appliance_virtualization` = '$apl_vid' AND `appliance_id` != '1'";
			$res = mysql_query($sql);
			while( $rez = mysql_fetch_assoc($res) ) {
				$apl_id = $rez["appliance_id"];
			}
		}

		
		require_once($_SERVER['DOCUMENT_ROOT'].'/htvcenter/base/class/appliance.class.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/htvcenter/base/class/resource.class.php');

		$appliance = new appliance();
		$appliance->get_instance_by_id($apl_id);
		
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		
		$resip = $resource->ip;

		*/

		
		// get image
		$image = new image();
		$image->get_instance_by_id($cr['cr_image_id']);
		$image_id = $image->id;
		$image_name = $image->name;
		$image_type = $image->type;
		$image_version = $image->version;
		$image_rootdevice = $image->rootdevice;
		$image_rootfstype = $image->rootfstype;
		$image_storageid = $image->storageid;
		$image_isshared = $image->isshared;
		$image_comment = $image->comment;
		$image_capabilities = $image->capabilities;
		$image_deployment_parameter = $image->deployment_parameter;


		

		// get image storage
		$storage = new storage();
		$storage->get_instance_by_id($image_storageid);
		$storage_resource_id = $storage->resource_id;
		// get storage resource
		$resource = new resource();
		$resource->get_instance_by_id($storage_resource_id);
		$resource_id = $resource->id;
		$resip = $resource->ip;
		
		
		if (isset($resip) && !empty($resip) && $resip != 'NULL') {

		$i = 1;
		foreach ($names as $key => $value) {
			# code...
			
				$name = $value;
				
			   

				$command  = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm add';
				$command .= ' -n '.$name.' -m '.$sizes[$key];
				$command .= ' -o '.$types[$key];
				$command .= ' -t '.'kvm-bf-deployment'.' -v '.'storage1';
				$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->htvcenter->admin()->name;
				$command .= ' --htvcenter-cmd-mode background';

				$statfile = $this->htvcenter->get('basedir').'/plugins/kvm/web/storage/'.'0'.'.'.'storage1'.'.lv.stat';

				if (file_exists($statfile)) {
					$lines = explode("\n", file_get_contents($statfile));
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


			
					if(file_exists($statfile)) {
						unlink($statfile);
					}
					
					$resource  = $this->htvcenter->resource();
					$this->resource = $resource;
					$this->resource->ip = $resip;

					
					$this->resource->send_command($this->resource->ip, $command);
					

					// add check that volume $name is now in the statfile
					$created = false;
					$bf_volume_path = "";
					$lines = explode("\n", file_get_contents($statfile));

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

					$queryimg = "SELECT `image_storageid` FROM `image_info` WHERE `image_id` = '".$cr['cr_image_id']."'";

					$rrrez = mysql_query($queryimg);
					while($rrow = mysql_fetch_assoc($rrrez)) {
						$storage_idd = $rrow['image_storageid'];
					}

					
					if (strlen($image->id)) {
						$errors[] = sprintf($this->lang['error_exists'], $image_name);
					} else {
						$tables = $this->htvcenter->get('table');
						$image_fields = array();
						$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$image_fields['image_name'] = $image_name;
						$image_fields['image_type'] = 'kvm-bf-deployment';
						$image_fields['image_rootfstype'] = 'local';
						//var_dump($this); die();
						//var_dump($this->storage); die();
						$image_fields['image_storageid'] = $storage_idd;
						$image_fields['image_comment'] = "Image Object for volume $image_name";
						$image_fields['image_rootdevice'] = $root_device;
						$image = new image();
						$image->add($image_fields);
						$message[] = sprintf($this->lang['msg_added_image'], $image_name);
					}

					
						//var_dump($imga); die();


					//$qquery = "INSERT INTO `cloud_volumes`(`instance_name`, `volume_name`, `size`, `type`) VALUES ('".$data['cloud_hostname_input']."', '".$devicename."', '".$sizes[$key]."', 'raw')";
					//mysql_query($qquery);
					



					$ipthis = $_SERVER["HTTP_HOST"];
		
		
		$i = $i+1;
		$file = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/tmp/diskcloud'.$i;
		unlink($file);
		$vmname = $data['cloud_hostname_input'];
		
		$fp = fopen($file, "w"); 
		$mytext = 'KVM_VM_DISK_'.$i.'="'.$devicename.'"'.PHP_EOL;
		$mytext .='KVM_VM_DISK_SIZE_'.$i.'="'.$sizes[$key].'"'.PHP_EOL;
		$test = fwrite($fp, $mytext); 
		

		/*
		$cmd = 'rm -rf /var/lib/kvm/htvcenter/'.$vmname.'/diskcloud'.$i;
		$this->resource->send_command($resipz, $cmd);

		$cmd = 'sudo wget http://'.$ipthis.'/htvcenter/base/tmp/diskcloud'.$i.' --user='.$this->htvcenter->admin()->name.' --password='.$this->htvcenter->admin()->password.' -P /var/lib/kvm/htvcenter/'.$vmname.'/';
		$this->resource->send_command($resipz, $cmd);
		*/
		
		//unlink($file);	
		
			
		}
		}
		// --- end volumes create ---
		

		return $response;
	}

	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "create");

		if($response->html->request()->get('profile')) {
			require_once $this->rootdir."/plugins/cloud/class/cloudprofile.class.php";
			$this->cloudprofile	= new cloudprofile();
			$this->cloudprofile->get_instance_by_id($response->html->request()->get('profile'));
			$msg = $response->html->request()->get($this->message_param);
			if($msg !== '') {
				$msg = $msg.'<br>';
			}
			$_REQUEST[$this->message_param] = $msg.sprintf($this->lang['msg_loading_profile'],$this->cloudprofile->name);
		}
		// pre-define select arrays
		$kernel_list = array();
		$cloud_image_select_arr = '';
		$cloud_virtualization_select_arr = '';
		$virtualization_list_select = array();
		$cloud_memory_select_arr = '';
		$cloud_cpu_select_arr = '';
		$cloud_disk_select_arr = '';
		$cloud_network_select_arr = '';
		$cloud_ha_select_arr = '';
		$cloud_application_select_arr = array();
		$product_application_description_arr = array();
		$ip_mgmt_list_per_user_arr = array();

		// global limits
		$max_resources_per_cr = 1;

		// big switch ##############################################################
		//  : either show what is provided in the cloudselector
		//  : or show what is available
		// check if cloud_selector feature is enabled
		$cloud_selector_enabled = $this->cloudconfig->get_value_by_key('cloud_selector');	// cloud_selector
		$virt_types = array();
		if (!strcmp($cloud_selector_enabled, "true")) {
			// show what is provided by the cloudselectors
			// cpus
			$product_array = $this->cloudselector->display_overview_per_type("cpu");
			$available_cpunumber = array();
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_cpu = $cloudproduct["quantity"];
					if ($cs_cpu <= $this->cloud_limits->free('cpu')) {
						$available_cpunumber[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
					}
				}
			}

			// disk size
			$disk_size_select = array();
			$product_array = $this->cloudselector->display_overview_per_type("disk");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_disk = $cloudproduct["quantity"];
					if ($cs_disk <= $this->cloud_limits->free('disk')) {
						$disk_size_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
					}
				}
			}

			// kernel
			$product_array = $this->cloudselector->display_overview_per_type("kernel");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$kernel_list[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
				}
			}

			// memory sizes
			$available_memtotal = array();
			$product_array = $this->cloudselector->display_overview_per_type("memory");

			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_memory = $cloudproduct["quantity"];

					// if ($cs_memory <= $this->cloud_limits->free('memory')) {
					 		$labelo = $this->tosize($cloudproduct["quantity"]);
							$available_memtotal[] = array("value" => $cloudproduct["quantity"], "label" => $labelo);
					//}
				}
			}

			// network cards
			$max_network_interfaces_select = array();
			$product_array = $this->cloudselector->display_overview_per_type("network");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_metwork = $cloudproduct["quantity"];
					 if ($cs_metwork <= $this->cloud_limits->free('network')) {
						$max_network_interfaces_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
					}
				}
			}

			// application classes
			// check if to show application
			$show_puppet_groups = $this->cloudconfig->get_value_by_key('show_puppet_groups');	// show_puppet_groups
			if (!strcmp($show_puppet_groups, "true")) {
				$product_array = $this->cloudselector->display_overview_per_type("application");
				foreach ($product_array as $index => $cloudproduct) {
					// is product enabled ?
					if ($cloudproduct["state"] == 1) {
						$application_product_name = $cloudproduct["description"];
						
						$application_product_name = str_replace('via puppet', '', $application_product_name);
						$application_product_name = trim($application_product_name);
						switch($application_product_name) {

							case 'basic-server':
								$img = 'servericon.png';
							break;

							case 'database-server':
								$img = 'databaseicon.png';
							break;

							case 'docker':
								$img = 'dockericon.png';
							break;

							case 'lamp':
								$img = 'lampicon.png';
							break;

							case 'openstack':
								$img = 'openstackicon.png';
							break;

							case 'webmin':
								$img = 'webminicon.png';
							break;

							case 'webserver':
								$img = 'webservericon.png';
							break;						



							default:
								$img = 'av1.png';
							break;
						}

						//var_dump($application_product_name); die();

						$imgrow = '<div class="imgrow"><img src="/cloud-fortis/img/'.$img.'"></img></div>';
						$application_product_name = $imgrow.$application_product_name;
						$application_class_name = $cloudproduct["quantity"];
						$cloud_application_select_arr[] = array("value" => $application_class_name, "label" => $application_product_name);
						$product_application_description_arr[$application_product_name] = $cloudproduct["name"];
					}
				}
			}

			// virtualization types
			$product_array = $this->cloudselector->display_overview_per_type("resource");
			$virt = $this->htvcenter->virtualization();
			foreach ($product_array as $key => $product) {
				// is product enabled ?
				if ($product["state"] == 1) {
					$virt->get_instance_by_id($product["quantity"]);
					$tmp = $virt->type;
					// tag network vms
					if(strstr($tmp, "-net")) {
						$tmp = 'vm-net';
					}
					// store virtualization type
					$virt_types[$tmp] = $virt->get_plugin_name();
					$str = $this->__get_virt_tag($tmp, $virt_types);
					$virtualization_list_select[] = array("value" => $product["quantity"], "label" => $product["description"].'  '.$str);
					$js_resources[] = array($product["quantity"], $product["description"], $str, $tmp);
				}
			}
		} else {
			// show what is available in htvcenter
			$kernel = $this->htvcenter->kernel();
			$kernel_list = array();
			$kernel_list = $kernel->get_list();
			// remove the htvcenter kernelfrom the list
			array_shift($kernel_list);

			// virtualization types
			$virt = $this->htvcenter->virtualization();
			$virtualization_list_select = array();
			$virt_list = $virt->get_list();
			// check if to show physical system type
			$cc_request_physical_systems = $this->cloudconfig->get_value_by_key('request_physical_systems');	// request_physical_systems
			if (!strcmp($cc_request_physical_systems, "false")) {
				array_shift($virt_list);
			}
			// filter out the virtualization hosts
			foreach ($virt_list as $id => $product) {
				if (!strstr($product['label'], "Host")) {
					$virt->get_instance_by_id($product['value']);
					$tmp = $virt->type;
					// tag network vms
					if(strstr($tmp, "-net")) {
						$tmp = 'vm-net';
					}
					// store virtualization type
					$virt_types[$tmp] = $virt->get_plugin_name();
					$str = $this->__get_virt_tag($tmp, $virt_types);
					$js_resources[] =  array($product['value'], $product['label'], $str, $tmp);
					$virtualization_list_select[] = array("value" => $product['value'], "label" => $product['label'].' '.$str);
				}
			}

			// prepare the array for the network-interface select
			$max_network_interfaces_select = array();
			$max_network_interfaces = $this->cloudconfig->get_value_by_key('max_network_interfaces');	// max_network_interfaces
			for ($mnet = 1; $mnet <= $max_network_interfaces; $mnet++) {
				$max_network_interfaces_select[] = array("value" => $mnet, "label" => $mnet);
			}

# TODO
# better cpu and memory generation

			// get list of available resource parameters
			$resource_p = $this->htvcenter->resource();
			$resource_p_array = $resource_p->get_list();
			// remove htvcenter resource
			array_shift($resource_p_array);
			// gather all available values in arrays
			$available_cpunumber_uniq = array();
			$available_cpunumber = array();
			$available_cpunumber[] = array("value" => "0", "label" => "Auto");
			$available_memtotal_uniq = array();
			$available_memtotal = array();
			$available_memtotal[] = array("value" => "0", "label" => "Auto");
			
			foreach($resource_p_array as $res) {
				$res_id = $res['resource_id'];
				$tres = $this->htvcenter->resource();
				$tres->get_instance_by_id($res_id);
				if (strlen($tres->cpunumber) && intval($tres->cpunumber) !== 0  && !in_array($tres->cpunumber, $available_cpunumber_uniq)) {
					$available_cpunumber[] = array("value" => $tres->cpunumber, "label" => $tres->cpunumber);
					$available_cpunumber_uniq[] .= $tres->cpunumber;
				}
				if (strlen($tres->memtotal) && !in_array($tres->memtotal, $available_memtotal_uniq)) {
					if($tres->memtotal < 1000) {
						$size = $tres->memtotal." MB";
					} else {
						$size = ($tres->memtotal/1000)." GB";
					}
					$available_memtotal[] = array("value" => $tres->memtotal, "label" => $size);
					$available_memtotal_uniq[] .= $tres->memtotal;
				}
			}

			// disk size select
			$disk_size_select = array();
			$max_disk_size = $this->cloud_limits->free('disk');
			if (1000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 1000, "label" => '1 GB');
			}
			if (2000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 2000, "label" => '2 GB');
			}
			if (3000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 3000, "label" => '3 GB');
			}
			if (4000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 4000, "label" => '4 GB');
			}
			if (5000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 5000, "label" => '5 GB');
			}
			if (10000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 10000, "label" => '10 GB');
			}
			if (20000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 20000, "label" => '20 GB');
			}
			if (50000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 50000, "label" => '50 GB');
			}
			if (100000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 100000, "label" => '100 GB');
			}

			// check if to show puppet
			$show_puppet_groups = $this->cloudconfig->get_value_by_key('show_puppet_groups'); // show_puppet_groups
			if (!strcmp($show_puppet_groups, "true")) {
				// is puppet enabled ?
				if (file_exists($this->rootdir."/plugins/puppet/.running")) {
					require_once $this->rootdir."/plugins/puppet/class/puppet.class.php";
					$puppet_group_dir = $this->rootdir."/plugins/puppet/puppet/manifests/groups";
					global $puppet_group_dir;
					$puppet_group_array = array();
					$puppet = new puppet();
					$puppet_group_array = $puppet->get_available_groups();
					foreach ($puppet_group_array as $index => $puppet_g) {
						$puid=$index+1;
						$puppet_info = $puppet->get_group_info($puppet_g);
						$cloud_application_select_arr[] = array("value" => "puppet/".$puppet_g, "label" => $puppet_g);
						$product_application_description_arr[$puppet_g] = $puppet_info;
					}
				}
			}
		}

		// show available images or private images which are enabled
		$img = $this->htvcenter->image();
		$image_list = array();
		$image_list_tmp = array();
		$image_list_tmp = $img->get_list();
		// remove the htvcenter + idle image from the list
		array_shift($image_list_tmp);
		array_shift($image_list_tmp);
		// check if private image feature is enabled
		$show_private_image = $this->cloudconfig->get_value_by_key('show_private_image');	// show_private_image
		if (!strcmp($show_private_image, "true")) {
			// private image feature enabled
			$private_image_list = $this->cloudprivateimage->get_all_ids();
			foreach ($private_image_list as $index => $cpi) {
				$cpi_id = $cpi["co_id"];
				$this->cloudprivateimage->get_instance_by_id($cpi_id);
				if ($this->clouduser->id == $this->cloudprivateimage->cu_id) {
					$img = $this->htvcenter->image();
					$img->get_instance_by_id($this->cloudprivateimage->image_id);
					// do not show active images
					if ($img->isactive == 1) {
						continue;
					}
					// only show the non-shared image to the user if it is not attached to a resource
					// because we don't want users to assign the same image to two appliances
					$priv_cloud_im = new cloudimage();
					$priv_cloud_im->get_instance_by_image_id($this->cloudprivateimage->image_id);
					if($priv_cloud_im->resource_id == 0 || $priv_cloud_im->resource_id == -1) {
						// get virtualization tag
						$str = $this->__get_virt_tag($img, $virt_types);
						// only show images for available virtualizations
						if($str !== '') {
							$image_list[] = array("value" => $img->id, "label" => $img->name.' '.$str);
							$js_images[] = array($img->id, $img->name, $str);
						}
					}
				} else if ($this->cloudprivateimage->cu_id == 0) {
					$img = $this->htvcenter->image();
					$img->get_instance_by_id($this->cloudprivateimage->image_id);
					if ($img->isactive == 1) {
						continue;
					}
					// get virtualization tag
					$str = $this->__get_virt_tag($img, $virt_types);
					// only show images for available virtualizations
					if($str !== '') {
						$image_list[] = array("value" => $img->id, "label" => $img->name.' '.$str);
						$js_images[] = array($img->id, $img->name, $str);
					}
				}
			}
		} else {
			// private image feature is not enabled
			// do not show the image-clones from other requests
			foreach($image_list_tmp as $list) {
				$iname = $list['label'];
				$iid = $list['value'];
				$img = $this->htvcenter->image();
				$img->get_instance_by_id($iid);
				// do not show active images
				if ($img->isactive == 1) {
					continue;
				}
				if (!strstr($iname, ".cloud_")) {
					// get virtualization tag
					$str = $this->__get_virt_tag($img, $virt_types);
					// only show images for available virtualizations
					if($str !== '') {
						$image_list[] = array("value" => $iid, "label" => $iname.' '.$str);
						$js_images[] = array($img->id, $img->name, $str);
					}
				}
			}
		}

		// check ip-mgmt
		$show_ip_mgmt = $this->cloudconfig->get_value_by_key('ip-management'); // ip-mgmt enabled ?
		$ip_mgmt_select = '';
		$ip_mgmt_title = '';
		#$ip_mgmt_list_per_user_arr[] = array("value" => -2, "label" => "Auto");
		#$ip_mgmt_list_per_user_arr[] = array("value" => -1, "label" => "None");
		if (!strcmp($show_ip_mgmt, "true")) {
			if (file_exists($this->rootdir."/plugins/ip-mgmt/.running")) {
				require_once $this->rootdir."/plugins/ip-mgmt/class/ip-mgmt.class.php";
				$ip_mgmt = new ip_mgmt();
				$ip_mgmt_list_per_user = $ip_mgmt->get_list_by_user($this->clouduser->cg_id);
				array_pop($ip_mgmt_list_per_user);
				foreach($ip_mgmt_list_per_user as $list) {
					$ip_mgmt_id = $list['ip_mgmt_id'];
					$ip_mgmt_name = trim($list['ip_mgmt_name']);
					$ip_mgmt_address = trim($list['ip_mgmt_address']);
					$ip_mgmt_list_per_user_arr[] = array("value" => $ip_mgmt_id, "label" => $ip_mgmt_address.' ('.$ip_mgmt_name.')');
				}
			}
		}

		// check if cloud_selector feature is enabled
		$cloud_appliance_hostname = '';
		$cloud_appliance_hostname_input = '';
		$cloud_appliance_hostname_help = '';
		$cloud_appliance_hostname_enabled = $this->cloudconfig->get_value_by_key('appliance_hostname');	// appliance_hostname
		if (!strcmp($cloud_appliance_hostname_enabled, "true")) {
			$cloud_appliance_hostname = 'Hostname setup';
			$cloud_appliance_hostname_help = '<small>Multiple appliances get the postfix <b>_[#no]</b></small>';
		}

		$cloud_memory_select_arr = array();
		if(isset($available_memtotal)) {
			$cloud_memory_select_arr = $available_memtotal;
		}
		$cloud_disk_select_arr = array();
		if(isset($disk_size_select)) {
			$cloud_disk_select_arr = $disk_size_select;
		}



		// Sort Image List
		if(count($image_list) > 0) {
			foreach ($image_list as $key => $row) {
				$label[$key] = strtolower($row['label']);
			}
			array_multisort($label, SORT_ASC, SORT_STRING, $image_list);
		}

		$cloud_image_select_arr = $image_list;
		$cloud_virtualization_select_arr = $virtualization_list_select;
		$cloud_cpu_select_arr = $available_cpunumber;
		$cloud_network_select_arr = $max_network_interfaces_select;

		#$cloud_ha_select_arr = $show_ha;
		$cloud_kernel_select_arr = $kernel_list;

		$d = array();

		$d['cloud_virtualization_select']['label']                       = $this->lang['type'];
		$d['cloud_virtualization_select']['required']                    = true;
		$d['cloud_virtualization_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_virtualization_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_virtualization_select']['object']['attrib']['id']      = 'cloud_virtualization_select';
		$d['cloud_virtualization_select']['object']['attrib']['name']    = 'cloud_virtualization_select';
		$d['cloud_virtualization_select']['object']['attrib']['options'] = $cloud_virtualization_select_arr;

		// select volume backend data :
		
		$query = "SELECT * FROM `cloud_selector` WHERE `type` = 'disk'";
		$res = mysql_query($query);

		$volumeselect = '<select id="volumeselect">';
		$rowarr = array();
		while($row = mysql_fetch_assoc($res)) {
			$volumeselect .= '<option value="'.$row['quantity'].'" ccu="'.$row['price'].'" >'.$row['name'].'</option>';
			$volumeselecteditdata .= '<option value="'.$row['quantity'].'" ccu="'.$row['price'].'" >'.$row['name'].'</option>';
		}
		$volumeselect .=  '</select>';


		$volumeselectedit = '<select id="sizeeditvolumeselect">';
		$volumeselectedit .= $volumeselecteditdata;
		$volumeselectedit .= '</select>';


		$d['volumeselectedit'] = $volumeselectedit;
		$d['volumeselect'] = $volumeselect;

		// --- end volume select backend data ---

		if(isset($this->cloudprofile->resource_type_req)) {
			$d['cloud_virtualization_select']['object']['attrib']['selected'] = array($this->cloudprofile->resource_type_req);
		}

		$d['cloud_kernel_select']['label']                       = $this->lang['kernel'];
		$d['cloud_kernel_select']['required']                    = true;
		$d['cloud_kernel_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_kernel_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_kernel_select']['object']['attrib']['id']      = 'cloud_kernel_select';
		$d['cloud_kernel_select']['object']['attrib']['name']    = 'cloud_kernel_select';
		$d['cloud_kernel_select']['object']['attrib']['options'] = $cloud_kernel_select_arr;
		if(isset($this->cloudprofile->kernel_id)) {
			$d['cloud_kernel_select']['object']['attrib']['selected'] = array($this->cloudprofile->kernel_id);
		}

		$d['cloud_image_select']['label']                       = $this->lang['image'];
		$d['cloud_image_select']['required']                    = true;
		$d['cloud_image_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_image_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_image_select']['object']['attrib']['id']      = 'cloud_image_select';
		$d['cloud_image_select']['object']['attrib']['name']    = 'cloud_image_select';
		$d['cloud_image_select']['object']['attrib']['options'] = $cloud_image_select_arr;
		if(isset($this->cloudprofile->image_id)) {
			$d['cloud_image_select']['object']['attrib']['selected'] = array($this->cloudprofile->image_id);
		}

		$d['cloud_memory_select']['label']                       = $this->lang['ram'];
		$d['cloud_memory_select']['required']                    = true;
		$d['cloud_memory_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_memory_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_memory_select']['object']['attrib']['id']      = 'cloud_memory_select';
		$d['cloud_memory_select']['object']['attrib']['name']    = 'cloud_memory_select';
		//var_dump($cloud_memory_select_arr); die();
		$d['cloud_memory_select']['object']['attrib']['options'] = $cloud_memory_select_arr;
		if(isset($this->cloudprofile->ram_req)) {
			$d['cloud_memory_select']['object']['attrib']['selected'] = array($this->cloudprofile->ram_req);
		}

		$d['cloud_cpu_select']['label']                       = $this->lang['cpu'];
		$d['cloud_cpu_select']['required']                    = true;
		$d['cloud_cpu_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_cpu_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_cpu_select']['object']['attrib']['id']      = 'cloud_cpu_select';
		$d['cloud_cpu_select']['object']['attrib']['name']    = 'cloud_cpu_select';
		$d['cloud_cpu_select']['object']['attrib']['options'] = $cloud_cpu_select_arr;
		if(isset($this->cloudprofile->cpu_req)) {
			$d['cloud_cpu_select']['object']['attrib']['selected'] = array($this->cloudprofile->cpu_req);
		}

		$d['cloud_disk_select']['label']                       = $this->lang['disk'];
		$d['cloud_disk_select']['required']                    = true;
		$d['cloud_disk_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_disk_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_disk_select']['object']['attrib']['id']      = 'cloud_disk_select';
		$d['cloud_disk_select']['object']['attrib']['name']    = 'cloud_disk_select';
		$d['cloud_disk_select']['object']['attrib']['options'] = $cloud_disk_select_arr;
		if(isset($this->cloudprofile->disk_req)) {
			$d['cloud_disk_select']['object']['attrib']['selected'] = array($this->cloudprofile->disk_req);
		}

		$d['cloud_network_select']['label']                       = $this->lang['network'];
		$d['cloud_network_select']['required']                    = true;
		$d['cloud_network_select']['object']['type']              = 'htmlobject_select';
		$d['cloud_network_select']['object']['attrib']['index']   = array('value', 'label');
		$d['cloud_network_select']['object']['attrib']['id']      = 'cloud_network_select';
		$d['cloud_network_select']['object']['attrib']['name']    = 'cloud_network_select';
		$d['cloud_network_select']['object']['attrib']['options'] = $cloud_network_select_arr;
		$query = "SELECT `cc_value` FROM `cloud_config` WHERE `cc_key` = 'max_network_interfaces'";
		$resq = mysql_query($query);
		while ($rez = mysql_fetch_assoc($resq)) {
			$maxval = $rez['cc_value'];
		}
		
		$d['maxmaxmax'] = $maxval;
		if(isset($this->cloudprofile->network_req)) {
			$d['cloud_network_select']['object']['attrib']['selected'] = array($this->cloudprofile->network_req);
		}

		// ips
		$ip_loop = 0;
		if (count($ip_mgmt_list_per_user_arr) > 0) {
			$max = 0;
			foreach($cloud_network_select_arr as $v) {
				if($v['value'] > $max) {
					$max = $v['value'];
				}
			}
			for($i = 0; $i < $max; $i++) {
				$nic_no = $ip_loop + 1;
				//$d['cloud_ip_select_'.$ip_loop]['label']                       = 'IP '.$nic_no;
				$d['cloud_ip_select_'.$ip_loop]['object']['type']              = 'htmlobject_select';
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['index']   = array('value', 'label');
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['id']      = 'cloud_ip_select_'.$ip_loop;
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['name']    = 'cloud_ip_select_'.$ip_loop;
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['options'] = $ip_mgmt_list_per_user_arr;
				if($i === $max-1) {
					$d['cloud_ip_select_'.$ip_loop]['css'] = 'last';
				}
				$ip_loop++;
			}
			for ($f = $ip_loop; $f < 4; $f++) {
				$d['cloud_ip_select_'.$f] = ' ';
			}
		} else {
			for ($f = $ip_loop; $f < 4; $f++) {
				$d['cloud_ip_select_'.$f] = ' ';
			}
		}

		// application
		$apps_selected = explode(',', $this->cloudprofile->puppet_groups);
		$product_loop = 0;
		if (count($cloud_application_select_arr) > 0) {
			foreach($cloud_application_select_arr as $application) {
				$product_name = $application['label'];
				$product_description = $application['value'];
				$d['cloud_application_select_'.$product_loop]['label']                     = $product_name;
				$d['cloud_application_select_'.$product_loop]['object']['type']            = 'htmlobject_input';
				$d['cloud_application_select_'.$product_loop]['object']['attrib']['type']  = 'checkbox';
				$d['cloud_application_select_'.$product_loop]['object']['attrib']['id']    = 'cloud_application_select'.$product_loop;
				$d['cloud_application_select_'.$product_loop]['object']['attrib']['name']  = 'cloud_application_select_'.$product_loop;
				$d['cloud_application_select_'.$product_loop]['object']['attrib']['value'] = $product_description;
				$d['cloud_application_select_'.$product_loop]['object']['attrib']['title'] = $product_application_description_arr[$product_name];
				if(in_array($product_description, $apps_selected)) {
					$d['cloud_application_select_'.$product_loop]['object']['attrib']['checked'] = true;
				}
				$product_loop++;
			}
			for ($f = $product_loop; $f < $this->cloud_max_applications; $f++) {
				$d['cloud_application_select_'.$f] = ' ';
			}
		} else {
			for ($f = $product_loop; $f < $this->cloud_max_applications; $f++) {
				$d['cloud_application_select_'.$f] = ' ';
			}
		}

		// ha
		$ha = false;
		$d['cloud_ha_select'] = '';
		if ($this->cloudconfig->get_value_by_key('cloud_selector') === 'true') {
			if(count($this->cloudselector->display_overview_per_type("ha")) > 0) {
				$ha = true;
			}
		}
		else if ($this->cloudconfig->get_value_by_key('show_ha_checkbox') === 'true') {
			$ha = true;
		}
		if($ha === true) {
			$d['cloud_ha_select']['label']                     = '<div class="imgrow"><img src="/cloud-fortis/img/haicon.png"></img></div>'.$this->lang['ha'];
			$d['cloud_ha_select']['object']['type']            = 'htmlobject_input';
			$d['cloud_ha_select']['object']['attrib']['type']  = 'checkbox';
			$d['cloud_ha_select']['object']['attrib']['id']    = 'cloud_ha_select';
			$d['cloud_ha_select']['object']['attrib']['name']  = 'cloud_ha_select';
			$d['cloud_ha_select']['object']['attrib']['value'] = 'ha';
			if($this->cloudprofile->ha_req === '1') {
				$d['cloud_ha_select']['object']['attrib']['checked'] = true;
			}
		}

		// capabilities
		$d['cloud_appliance_capabilities']['label']                         = $this->lang['capabilities'];
		$d['cloud_appliance_capabilities']['object']['type']                = 'htmlobject_textarea';
		$d['cloud_appliance_capabilities']['object']['attrib']['id']        = 'cloud_appliance_capabilities';
		$d['cloud_appliance_capabilities']['object']['attrib']['name']      = 'cloud_appliance_capabilities';
		$d['cloud_appliance_capabilities']['object']['attrib']['maxlength'] = 1000;
		$d['cloud_appliance_capabilities']['object']['attrib']['value']     = $this->cloudprofile->appliance_capabilities;

		// check if user are allowed to set the hostname
		$d['cloud_hostname_input'] = '';
		if ($this->cloudconfig->get_value_by_key('appliance_hostname') === "true") {
			$d['cloud_hostname_input']['label']                         = $this->lang['hostname'];
			$d['cloud_hostname_input']['validate']['regex']             = $this->htvcenter->get('regex', 'hostname');
			$d['cloud_hostname_input']['validate']['errormsg']          = 'Hostname must be '.$this->htvcenter->get('regex', 'hostname').' only';
			$d['cloud_hostname_input']['object']['type']                = 'htmlobject_input';
			$d['cloud_hostname_input']['object']['attrib']['type']      = 'text';
			$d['cloud_hostname_input']['object']['attrib']['id']        = 'cloud_hostname_input';
			$d['cloud_hostname_input']['object']['attrib']['name']      = 'cloud_hostname_input';
			$d['cloud_hostname_input']['object']['attrib']['maxlength'] = 255;
			$d['cloud_hostname_input']['object']['attrib']['value']     = $this->cloudprofile->appliance_hostname;
		}

		// save as profile
		$d['cloud_profile_name']['label']                         = $this->lang['save_as_profile'];
		$d['cloud_profile_name']['required']                      = false;
		$d['cloud_profile_name']['validate']['regex']             = '~^[a-z0-9]+$~i';
		$d['cloud_profile_name']['validate']['errormsg']          = sprintf($this->lang['error_save_as_profile'],'[a-z0-9]');
		$d['cloud_profile_name']['object']['type']                = 'htmlobject_input';
		$d['cloud_profile_name']['object']['attrib']['type']      = 'text';
		$d['cloud_profile_name']['object']['attrib']['id']        = 'cloud_profile_name';
		$d['cloud_profile_name']['object']['attrib']['name']      = 'cloud_profile_name';
		$d['cloud_profile_name']['object']['attrib']['maxlength'] = 15;

		$str  = 'var formbuilder = {'."\n";
		$str .= 'resources:['."\n";
		$i = 1;
		if(isset($js_resources)) {
			foreach ($js_resources as $k => $v) {
				$str .= '['.$v[0].',"'.$v[1].'","'.$v[2].'","'.$v[3].'"]';
				if($i < count($js_resources)) {
					$str .= ','."\n";
				}
				$i++;
			}
		}
		$str .= '],'."\n";
		$str .= 'images:['."\n";
		$i = 1;
		if(isset($js_images)) {
			// Sort Image List
			if(count($js_images) > 0) {
				$label = array();
				foreach ($js_images as $key => $row) {
					$label[$key] = $row[1];
				}
				array_multisort($label, SORT_ASC, SORT_STRING, $js_images);
			}
			// build json
			foreach ($js_images as $k => $v) {
				$str .= '['.$v[0].',"'.$v[1].'","'.$v[2].'"]';
				if($i < count($js_images)) {
					$str .= ','."\n";
				}
				$i++;
			}
		}
		$str .= ']'."\n";
		$str .= '};'."\n";
		$this->js_resources = $str;

		$form->add($d);

		// check profiles
		$profile = $this->response->html->request()->get('profile');
		if($profile !== '') {
			$profile = $this->cloudprofile->name;
			if(isset($this->cloudprofile->resource_type_req)) {
				$p = false;
				foreach($cloud_virtualization_select_arr as $v) {
					if($v['value'] === $this->cloudprofile->resource_type_req) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_virtualization_select', sprintf($this->lang['error_profile'], $this->lang['type'], $profile));
				}
			}
			if(isset($this->cloudprofile->image_id)) {
				$p = false;
				foreach($cloud_image_select_arr as $v) {
					if($v['value'] === $this->cloudprofile->image_id) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_image_select',  sprintf($this->lang['error_profile'], $this->lang['image'], $profile));
				}
			}
			if(isset($this->cloudprofile->kernel_id)) {
				$p = false;
				foreach($cloud_kernel_select_arr as $v) {
					if($v['value'] === $this->cloudprofile->kernel_id) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_kernel_select',  sprintf($this->lang['error_profile'], $this->lang['kernel'], $profile));
				}
			}
			if(isset($this->cloudprofile->disk_req)) {
				$p = false;
				foreach($cloud_disk_select_arr as $v) {
					if($v['value'] == $this->cloudprofile->disk_req) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_disk_select',  sprintf($this->lang['error_profile'], $this->lang['disk'].' ('.$this->cloudprofile->disk_req.' MB)', $profile));
				}
			}
			if(isset($this->cloudprofile->ram_req)) {
				$p = false;
				foreach($cloud_memory_select_arr as $v) {
					if($v['value'] == $this->cloudprofile->ram_req) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_memory_select',  sprintf($this->lang['error_profile'], $this->lang['ram'].' ('.$this->cloudprofile->ram_req.' MB)', $profile));
				}
			}
			if(isset($this->cloudprofile->cpu_req)) {
				$p = false;
				foreach($cloud_cpu_select_arr as $v) {
					if($v['value'] == $this->cloudprofile->cpu_req) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_cpu_select',  sprintf($this->lang['error_profile'], $this->lang['cpu'].' ('.$this->cloudprofile->cpu_req.')', $profile));
				}
			}
			if(isset($this->cloudprofile->network_req)) {
				$p = false;
				foreach($cloud_network_select_arr as $v) {
					if($v['value'] == $this->cloudprofile->network_req) {
						$p = true;
						break;
					}
				}
				if($p === false) {
					$form->set_error('cloud_network_select',  sprintf($this->lang['error_profile'], $this->lang['network'].' ('.$this->cloudprofile->network_req.')', $profile));
				}
			}
		}

		$form->display_errors = false;
		//var_dump($this->rootdir.'/class/db.class.php'); die();
		/*require_once($this->rootdir.'/class/db.class.php');
		var_dump($this->htvcenter);
		$datab = new db($this->htvcenter);
		var_dump($this->htvcenter->get('config', 'DATABASE_NAME')); die();
		*/
	
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Match virtualization with image
	 *
	 * @access protected
	 * @param string|object $img
	 * @param array $virttypes
	 * @return string
	 */
	//--------------------------------------------
	function __get_virt_tag($img, $virttypes) {
		$str = '';
		$tag = '';
		if(is_string($img)) {
			$tag = $img;
		}
		else if (is_object($img)) {
			if(isset($img->type) && $img->type !== '') {
				$deployment = $this->htvcenter->deployment();
				$deployment->get_instance_by_name($img->type);
				$tag  = '';
				if($img->is_network_deployment() === true) {
					$tag = 'vm-net';
				} else {
					$tag = $deployment->storagetype.'-vm-local';
				}
			}
		}
		$mark = array_search($tag, array_keys($virttypes));
		if(is_integer($mark)) {
			$str = '*';
			for($i=0;$i<$mark;$i++) {
				$str .= '*';
			}
		}
		return $str;
	}


	function tosize($memsize)
    {
        if ($memsize >= 1073741824)
        {
            $memsize = number_format($memsize / 1048576, 2);
            $memsize = round($memsize);
	        //$memsize = (int) $memsize; 
	        $memsize = $memsize .' TB';
        }
		elseif ($memsize >= 1024)
        {
            $memsize = number_format($memsize / 1024, 2);
            $memsize = round($memsize);
	        //$memsize = int $memsize; 
	        $memsize = $memsize .' GB';
        }
        

        return $memsize;
}

}
?>
