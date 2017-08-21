<?php
/**
 * Hybrid-cloud Instance add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hybrid_cloud_vm_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_vm_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_vm_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_vm_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_vm_tab';
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
		$this->response   = $response;
		$this->file       = $htvcenter->file();
		$this->htvcenter    = $htvcenter;
		$this->user       = $htvcenter->user();
		$this->id         = $this->response->html->request()->get('hybrid_cloud_id');
		$this->response->add('hybrid_cloud_id', $this->id);
		$this->region     = $this->response->html->request()->get('region');
		$this->statfile = $this->htvcenter->get('basedir').'/plugins/hybrid-cloud/web/hybrid-cloud-stat/'.$this->id.'.describe_configuration.log';
		require_once($this->htvcenter->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$hc = new hybrid_cloud();
		$hc->get_instance_by_id($this->id);
		$this->hc = $hc;
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
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&resource_id='.$response->resource_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-vm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->add($this->lang['form_endpoint_configuration'], 'endpoint_configuration');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->actions_name, 'actions_name');
		if ($this->hc->account_type == 'lc-azure') {
			$t->add($this->lang['lang_notice_azure'], 'lang_notice');
		} else {
			$t->add($this->lang['lang_notice'], 'lang_notice');
		}
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
		$response = $this->get_response();
		$form     = $response->form;
		$errors = array();
		if(!$form->get_errors() && $this->response->submit()) {
			
			$image_id = $form->get_request('ami_image_id');
			$image = new image();
			if(isset($image_id) && $image_id !== '') {
				$image->get_instance_by_id($image_id);
			} else {
				$errors[] = $this->lang['error_boot'];
			}
			
			if(count($errors) > 0 || $form->get_errors()) {
				$response->error = join('<br>', $errors);
			} else {
				$tables = $this->htvcenter->get('table');
				$custom_script_parameter = '';
				$custom_script = $form->get_request('custom_script');
				if (strlen($custom_script)) {
					$custom_script_parameter = ' -ic '.$custom_script;
				}
				$custom_name = $form->get_request('name');
				$resource = new resource();
				$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				if (strlen($custom_name)) {
					$name = $custom_name;
				} else {
					$name   = $this->hc->account_type.$id;
				}
				$keypair_parameter = '';
				$keypair = $form->get_request('keypair');
				if (strlen($keypair)) {
					$keypair_parameter = ' -ik '.$keypair;
				}
				$subnet_parameter = '';
				$group_parameter = '';
				if ($this->hc->account_type == 'aws') {
					$subnet = $form->get_request('subnet');
					if (strlen($subnet)) {
						$subnet_parameter = ' -subnet '.$subnet;
					}
				} else {
					$group = $form->get_request('group');
					if (strlen($group)) {
						$group_parameter = ' -ig '.$group;
					}
				}
				// for azure
				$compute_service_parameter = '';
				$compute_service = $form->get_request('compute_service');
				if (strlen($compute_service)) {
					$compute_service_parameter = ' --service-name '.$compute_service;
				}
				$location_parameter = '';
				$location = $form->get_request('location');
				if (strlen($location)) {
					$location_parameter = ' -ir '.str_replace(" ", "@", $location);
				}
				$administrator_parameter = '';
				$administrator = $form->get_request('administrator');
				if (strlen($administrator)) {
					$administrator_parameter = ' -u '.$administrator;
				}
				$administrator_password_parameter = '';
				$administrator_password = $form->get_request('administrator_password');
				if (strlen($administrator_password)) {
					$administrator_password_parameter = ' -p '.$administrator_password;
				}
				$type_parameter = '';
				$type = $form->get_request('type');
				if (strlen($type)) {
					if ($this->hc->account_type == 'lc-azure') {
						$type_parameter = ' -it '.str_replace(" ", "@", $type);
					} else {
						$type_parameter = ' -it '.$type;
					}
				}

				
				$endpoint_parameter_str = "";
				$endpoint_http = $form->get_request('endpoint_http');
				if ($endpoint_http == "enabled") {
					$endpoint_parameter_str .= "http,";
				}
				$endpoint_rdp = $form->get_request('endpoint_rdp');
				if ($endpoint_rdp == "enabled") {
					$endpoint_parameter_str .= "rdp,";
				}
				$endpoint_ssh = $form->get_request('endpoint_ssh');
				if ($endpoint_ssh == "enabled") {
					$endpoint_parameter_str .= "ssh,";
				}
				if (strlen($endpoint_parameter_str)) {
					$endpoint_parameter_str = rtrim($endpoint_parameter_str, ',');
					$endpoint_parameter_str = " -e ".$endpoint_parameter_str;
				}
				
				// set a local ip to not depend on dhcpd just for getting a private ip
				$ip = "127.0.0.0";
				$resource->generate_mac();
				$mac = $resource->mac;
				// send command to the htvcenter-server
				$htvcenter = new htvcenter_server();
				$htvcenter->send_command('htvcenter_server_add_resource '.$id.' '.$mac.' '.$ip);
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("hybrid-cloud-vm-local");
				// add to htvcenter database
				$fields["resource_id"] = $id;
				$fields["resource_ip"] = $ip;
				$fields["resource_mac"] = $mac;
				$fields["resource_hostname"] = $name;
				$fields["resource_localboot"] = 0;
				$fields["resource_vtype"] = $virtualization->id;
				$fields["resource_vhostid"] = 0;
				$fields["resource_image"] = $image->name;
				$fields["resource_imageid"] = $image->id;
				$rfields["resource_kernel"] = 'default';
				$rfields["resource_kernelid"] = 1;
				$resource->add($fields);
				$resource->get_instance_by_mac($mac);
				// set account id in resource capabilities
				$resource->set_resource_capabilities("HCACL", $this->hc->id);

				$hc_authentication = '';
				if (($this->hc->account_type == 'aws') || ($this->hc->account_type == 'euca')) {
					$hc_authentication .= ' -O '.$this->hc->access_key;
					$hc_authentication .= ' -W '.$this->hc->secret_key;
					$hc_authentication .= ' -ir '.$this->region;
					$hc_authentication .= ' -iz '.$form->get_request('availability_zone');
				}
				if ($this->hc->account_type == 'lc-openstack') {
					$hc_authentication .= ' -u '.$this->hc->username;
					$hc_authentication .= ' -p '.$this->hc->password;
					$hc_authentication .= ' -q '.$this->hc->host;
					$hc_authentication .= ' -x '.$this->hc->port;
					$hc_authentication .= ' -g '.$this->hc->tenant;
					$hc_authentication .= ' -e '.$this->hc->endpoint;
				}
				if ($this->hc->account_type == 'lc-azure') {
					$hc_authentication .= ' -s '.$this->hc->subscription_id;
					$hc_keyfile = $this->hc->keyfile;
					$account_file_dir = $this->htvcenter->get('basedir').'/plugins/hybrid-cloud/etc/acl';
					$random_file_name  = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$filename = $account_file_dir."/".$random_file_name;
					file_put_contents($filename, $hc_keyfile);
					$hc_authentication .= ' -k '.$filename;
				}

				$command  = $this->htvcenter->get('basedir').'/plugins/hybrid-cloud/bin/htvcenter-hybrid-cloud-vm create';
				$command .= ' -i '.$this->hc->id;
				$command .= ' -n '.$this->hc->account_name;
				$command .= ' -t '.$this->hc->account_type;
				$command .= $hc_authentication;
				$command .= ' -in '.$name;
				$command .= ' -im '.$mac;
				$command .= ' -a '.$image->name;
				$command .= $keypair_parameter;
				$command .= $subnet_parameter;
				$command .= $group_parameter;
				$command .= $custom_script_parameter;
				$command .= $compute_service_parameter;
				$command .= $location_parameter;
				$command .= $administrator_parameter;
				$command .= $type_parameter;
				$command .= $administrator_password_parameter;
				$command .= $endpoint_parameter_str;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				$htvcenter->send_command($command, NULL, true);

				// check to have a ip from the dhcpd-resource hook
				if ($this->hc->account_type != 'lc-azure') {
					while ($resource->ip == "0.0.0.0") {
						sleep(1);
						clearstatcache();
						$resource->get_instance_by_mac($mac);
					}
				}
				// save the mgmt ip in the resource network field
				$rufields["resource_network"] = $resource->ip;
				$resource->update_info($resource->id, $rufields);

				$response->resource_id = $id;
				$response->msg = sprintf($this->lang['msg_added'], $name);

				// auto create the appliance for this VM if we are not coming from the wizard
				if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
					$now = 1;
				} else {
					$now=$_SERVER['REQUEST_TIME'];
					$appliance = new appliance();
					$new_appliance_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$afields['appliance_id'] = $new_appliance_id;
					$afields['appliance_name'] = $name;
					$afields['appliance_resources'] = $id;
					$afields['appliance_kernelid'] = '1';
					$afields['appliance_imageid'] = $image->id;
					$afields["appliance_virtual"]= 0;
					$afields["appliance_virtualization"]=$virtualization->id;
					$afields['appliance_wizard'] = '';
					$afields['appliance_comment'] = 'Cloud VM Server for Resource '.$id;
					$appliance->add($afields);
					// update state/start+stoptime
					$aufields['appliance_stoptime']=$now;
					$aufields['appliance_starttime']='';
					$aufields['appliance_state']='stopped';
					$appliance->update($new_appliance_id, $aufields);
					// set image active
					$image_fields["image_id"] = $image->id;
					$image_fields['image_isactive']=1;
					$image->update($image->id, $image_fields);
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
		$size_select_arr = array();
		$availability_zones_select_arr = array();
		$security_group_select_arr = array();
		$subnet_select_arr = array();
		$keypair_select_arr = array();
		$compute_service_select_arr = array();
		$region_select_arr = array();
		// get the datastore and vswitchlist for the selects
		if (file_exists($this->statfile)) {
			$lines = explode("\n", file_get_contents($this->statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						switch ($line[0]) {
							case 'SIZES':
								if ($this->hc->account_type == 'lc-azure') {
									$size_select_arr[] = array(str_replace("|", " ", $line[1]),str_replace("|", " ", $line[2]));
								} else {
									$size_select_arr[] = array($line[1],$line[1]);
								}
								break;
							case 'KEYPAIR':
								$keypair_select_arr[] = array($line[1],$line[1]);
								break;
							case 'GROUP':
								$security_group_select_arr[] = array($line[1],$line[1]);
								break;
							case 'ZONES':
								$availability_zones_select_arr[] = array($line[1],$line[1]);
								break;
							case 'SUBNET':
								$subnet_select_arr[] = array($line[1],$line[2]." (".$line[3].")");
								break;
							case 'SERVICE':
								$compute_service_select_arr[] = array($line[1],$line[2]." (".$line[3].")");
								break;
							case 'REGION':
								$region_select_arr[] = array(str_replace("|", " ", $line[1]),str_replace("|", " ", $line[2]));
								break;

						}
					}
				}
			}
		}

		if (($this->hc->account_type == 'aws') || ($this->hc->account_type == 'euca')) {
			$instance_types[] = array("t1.micro", "t1.micro");
			$instance_types[] = array("m1.small", "m1.small");
			$instance_types[] = array("m1.medium", "m1.medium");
			$instance_types[] = array("m1.large", "m1.large");
			$instance_types[] = array("m1.xlarge", "m1.xlarge");
			$instance_types[] = array("m3.xlarge", "m3.xlarge");
			$instance_types[] = array("m3.2xlarge", "m3.2xlarge");
			$instance_types[] = array("c1.medium", "c1.medium");
			$instance_types[] = array("c1.xlarge", "c1.xlarge");
			$instance_types[] = array("m2.xlarge", "m2.xlarge");
			$instance_types[] = array("m2.2xlarge", "m2.2xlarge");
			$instance_types[] = array("m2.4xlarge", "m2.4xlarge");
			$instance_types[] = array("cr1.8xlarge", "cr1.8xlarge");
			$instance_types[] = array("hi1.4xlarge", "hi1.4xlarge");
			$instance_types[] = array("hs1.8xlarge", "hs1.8xlarge");
			$instance_types[] = array("cc1.4xlarge", "cc1.4xlarge");
			$instance_types[] = array("cc2.8xlarge", "cc2.8xlarge");
			$instance_types[] = array("cg1.4xlarge", "cg1.4xlarge");
			$instance_type_selected = "t1.micro";
		}
		if ($this->hc->account_type == 'lc-openstack') {
			$instance_types = $size_select_arr;
			$instance_type_selected = "m1.nano";
		}
		if ($this->hc->account_type == 'lc-azure') {
			$instance_types = $size_select_arr;
			$instance_type_selected = "ExtraSmall";
			
			$compute_services = $compute_service_select_arr;
			$compute_service_selected = $this->lang['form_compute_service_auto_create'];
			$compute_service_select_arr[] = array("autocreate", $compute_service_selected);
			
			$regions = $region_select_arr;
			$region_selected = "West Europe";
			
			$security_group_select_arr[] = array('production', 'Production');
			$security_group_select_arr[] = array('staging', 'Staging');
		}
		
		// AMIs
		$ami_select_arr = array();
		$image = new image();
		$image_id_list = $image->get_ids();
		foreach ($image_id_list as $id => $ikey) {
			$image_tmp = new image();
			$image_tmp->get_instance_by_id($ikey['image_id']);
			if ($image_tmp->type === "ami-deployment") {
				$ami_select_arr[] = array($image_tmp->id, $image_tmp->comment);
			}
		}

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		if (($this->hc->account_type == 'lc-openstack') || ($this->hc->account_type == 'lc-azure')) {
			$d['name']['label']                         = $this->lang['form_name'];
			$d['name']['required']                      = false;
			$d['name']['validate']['regex']             = '/^[a-z0-9._:\/-]+$/i';
			$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._:\/-');
			$d['name']['object']['type']                = 'htmlobject_input';
			$d['name']['object']['attrib']['id']        = 'name';
			$d['name']['object']['attrib']['name']      = 'name';
			$d['name']['object']['attrib']['type']      = 'text';
			$d['name']['object']['attrib']['value']     = '';
			$d['name']['object']['attrib']['maxlength'] = 255;
		}
		if (($this->hc->account_type == 'aws') || ($this->hc->account_type == 'euca')) {
			$d['name'] = '';
		}

		$d['instance_type']['label']                       = $this->lang['form_instance_type'];
		$d['instance_type']['required']                    = true;
		$d['instance_type']['object']['type']              = 'htmlobject_select';
		$d['instance_type']['object']['attrib']['name']    = 'type';
		$d['instance_type']['object']['attrib']['index']   = array(0,1);
		$d['instance_type']['object']['attrib']['options'] = $instance_types;
		$d['instance_type']['object']['attrib']['selected'] = array($instance_type_selected);

		$d['ami']['label']                       = $this->lang['form_ami'];
		$d['ami']['required']                    = true;
		$d['ami']['object']['type']              = 'htmlobject_select';
		$d['ami']['object']['attrib']['name']    = 'ami_image_id';
		$d['ami']['object']['attrib']['index']   = array(0,1);
		$d['ami']['object']['attrib']['options'] = $ami_select_arr;
		
		$a = $this->response->html->a();
		$a->label   = $this->lang['form_add_volume'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = 'index.php?plugin=hybrid-cloud&controller=hybrid-cloud-ami&hybrid_cloud_id='.$this->id;
		$d['add_image']   = $a->get_string();

		if (($this->hc->account_type == 'aws') || ($this->hc->account_type == 'euca')) {
			$d['availability_zone']['label']                       = $this->lang['form_availability_zone'];
			$d['availability_zone']['required']                    = true;
			$d['availability_zone']['object']['type']              = 'htmlobject_select';
			$d['availability_zone']['object']['attrib']['name']    = 'availability_zone';
			$d['availability_zone']['object']['attrib']['index']   = array(0,1);
			$d['availability_zone']['object']['attrib']['options'] = $availability_zones_select_arr;
		}
		if (($this->hc->account_type == 'lc-openstack') || ($this->hc->account_type == 'lc-azure')) {
			$d['availability_zone'] = '';
		}

		if (($this->hc->account_type == 'aws')) {
			$d['group'] = '';

			$d['subnet']['label']                       = $this->lang['form_subnet'];
			$d['subnet']['required']                    = true;
			$d['subnet']['object']['type']              = 'htmlobject_select';
			$d['subnet']['object']['attrib']['name']    = 'subnet';
			$d['subnet']['object']['attrib']['index']   = array(0,1);
			$d['subnet']['object']['attrib']['options'] = $subnet_select_arr;
		} else {
			$d['subnet'] = '';
			$d['group']['label']                       = $this->lang['form_security_group'];
			$d['group']['required']                    = true;
			$d['group']['object']['type']              = 'htmlobject_select';
			$d['group']['object']['attrib']['name']    = 'group';
			$d['group']['object']['attrib']['index']   = array(0,1);
			$d['group']['object']['attrib']['options'] = $security_group_select_arr;
			$d['group']['object']['attrib']['selected'] = array('default');
		}

		if ($this->hc->account_type == 'lc-azure') {
			$d['keypair'] = '';
			$d['custom_script'] = '';

			$d['compute_service']['label']                       = $this->lang['form_compute_service'];
			$d['compute_service']['required']                    = true;
			$d['compute_service']['object']['type']              = 'htmlobject_select';
			$d['compute_service']['object']['attrib']['name']    = 'compute_service';
			$d['compute_service']['object']['attrib']['index']   = array(0,1);
			$d['compute_service']['object']['attrib']['options'] = $compute_service_select_arr;
			
			$d['location']['label']                       = $this->lang['form_region'];
			$d['location']['required']                    = true;
			$d['location']['object']['type']              = 'htmlobject_select';
			$d['location']['object']['attrib']['name']    = 'location';
			$d['location']['object']['attrib']['index']   = array(0,1);
			$d['location']['object']['attrib']['options'] = $region_select_arr;
			$d['location']['object']['attrib']['selected'] = array($region_selected);
			
			$d['administrator']['label']                         = $this->lang['form_administrator'];
			$d['administrator']['required']                      = false;
			$d['administrator']['validate']['regex']             = '/^[a-z0-9._:-]+$/i';
			$d['administrator']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._:-');
			$d['administrator']['object']['type']                = 'htmlobject_input';
			$d['administrator']['object']['attrib']['id']        = 'administrator';
			$d['administrator']['object']['attrib']['name']      = 'administrator';
			$d['administrator']['object']['attrib']['type']      = 'text';
			$d['administrator']['object']['attrib']['value']     = 'azureuser';
			$d['administrator']['object']['attrib']['maxlength'] = 255;

			$d['administrator_password']['label']                         = $this->lang['form_administrator_password'];
			$d['administrator_password']['required']                      = false;
			$d['administrator_password']['validate']['regex']             = '/^[a-z0-9._:-]+$/i';
			$d['administrator_password']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._:-');
			$d['administrator_password']['object']['type']                = 'htmlobject_input';
			$d['administrator_password']['object']['attrib']['id']        = 'administrator_password';
			$d['administrator_password']['object']['attrib']['name']      = 'administrator_password';
			$d['administrator_password']['object']['attrib']['type']      = 'password';
			$d['administrator_password']['object']['attrib']['value']     = '';
			$d['administrator_password']['object']['attrib']['maxlength'] = 255;

			$d['endpoint_http']['label']                     = $this->lang['form_endpoint_http'];
			$d['endpoint_http']['object']['type']            = 'htmlobject_input';
			$d['endpoint_http']['object']['attrib']['type']  = 'checkbox';
			$d['endpoint_http']['object']['attrib']['name']  = 'endpoint_http';
			$d['endpoint_http']['object']['attrib']['value'] = 'enabled';

			$d['endpoint_rdp']['label']                     = $this->lang['form_endpoint_rdp'];
			$d['endpoint_rdp']['object']['type']            = 'htmlobject_input';
			$d['endpoint_rdp']['object']['attrib']['type']  = 'checkbox';
			$d['endpoint_rdp']['object']['attrib']['name']  = 'endpoint_rdp';
			$d['endpoint_rdp']['object']['attrib']['value'] = 'enabled';

			$d['endpoint_ssh']['label']                     = $this->lang['form_endpoint_ssh'];
			$d['endpoint_ssh']['object']['type']            = 'htmlobject_input';
			$d['endpoint_ssh']['object']['attrib']['type']  = 'checkbox';
			$d['endpoint_ssh']['object']['attrib']['name']  = 'endpoint_ssh';
			$d['endpoint_ssh']['object']['attrib']['value'] = 'enabled';

		} else {	
			
			$d['compute_service'] = '';
			$d['location'] = '';
			$d['administrator'] = '';
			$d['administrator_password'] = '';
			$d['endpoint_http'] = '';
			$d['endpoint_rdp'] = '';
			$d['endpoint_ssh'] = '';
			
			$d['keypair']['label']                       = $this->lang['form_keypair'];
			$d['keypair']['required']                    = true;
			$d['keypair']['object']['type']              = 'htmlobject_select';
			$d['keypair']['object']['attrib']['name']    = 'keypair';
			$d['keypair']['object']['attrib']['index']   = array(0,1);
			$d['keypair']['object']['attrib']['options'] = $keypair_select_arr;

			$d['custom_script']['label']                         = $this->lang['form_custom_script'];
			$d['custom_script']['required']                      = false;
			$d['custom_script']['validate']['regex']             = '/^[a-z0-9._:\/-]+$/i';
			$d['custom_script']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._:\/-');
			$d['custom_script']['object']['type']                = 'htmlobject_input';
			$d['custom_script']['object']['attrib']['id']        = 'custom_script';
			$d['custom_script']['object']['attrib']['name']      = 'custom_script';
			$d['custom_script']['object']['attrib']['type']      = 'text';
			$d['custom_script']['object']['attrib']['value']     = '';
			$d['custom_script']['object']['attrib']['title']     = $this->lang['form_custom_script_title'];
			$d['custom_script']['object']['attrib']['maxlength'] = 255;
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
