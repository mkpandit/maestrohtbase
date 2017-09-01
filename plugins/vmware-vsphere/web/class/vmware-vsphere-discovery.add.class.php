<?php
/**
 * Add discovered vSphere Hosts to HyperTask
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_discovery_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_discovery_id';
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
		$this->user	    = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');

		$id = $this->response->html->request()->get('id');
		if ($id == '') {
 			$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$vmware_vsphere_discovery_fields['vmw_vsphere_ad_id'] = $id;
			$vmware_vsphere_discovery_fields['vmw_vsphere_ad_is_integrated '] = 0;
			require_once $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/web/class/vmware-vsphere-discovery.class.php";
			$this->discovery = new vmware_vsphere_discovery();
			$this->discovery->add($vmware_vsphere_discovery_fields);
		}		
		$this->response->add('id', $id);
		$this->id = $id;
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
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		$t = $response->html->template($this->tpldir.'/vmware-vsphere-discovery-add.tpl.php');
		$t->add($this->lang['label'], 'label');
		$t->add($response->form->get_elements());
		$t->add($response->html->thisfile, "thisfile");
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function add() {
		$response = $this->get_response("add");
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			// check if already integrated
			$this->discovery->get_instance_by_id($data['vmware_vsphere_ad_id']);
			if ($this->discovery->vmw_vsphere_ad_is_integrated > 0) {
				$response->msg = sprintf($this->lang['error_exists'], $this->discovery->vmw_vsphere_ad_id);
				return $response;
			}
	
			if(!$form->get_errors()) {
				$vsphere_ip = $data['vmw_vsphere_ad_ip'];
				$vsphere_mac = strtolower($data['vmw_vsphere_ad_mac']);
				$vsphere_hostname = $data['vmw_vsphere_ad_hostname'];
				$vsphere_user = $data['vmw_vsphere_ad_user'];
				$vsphere_password = $data['vmw_vsphere_ad_password'];
				$vsphere_comment = $data['vmw_vsphere_ad_comment'];

				// create the resource
				$vsphere_resource = new resource();
				// check if mac already exist
				$vsphere_resource->get_instance_by_mac($vsphere_mac);
				if ($vsphere_resource->id > 0) {
					$response->msg = sprintf($this->lang['error_exists'], $this->discovery->vmw_vsphere_ad_id);
					return $response;
				}
				// check if mac already exist
				$vsphere_resource->get_instance_by_ip($vsphere_ip);
				if ($vsphere_resource->id > 0) {
					$response->msg = sprintf($this->lang['error_exists'], $this->discovery->vmw_vsphere_ad_id);
					return $response;
				}

				// check if hostname is free for appliance and image name
				$storage = new storage();
				$storage->get_instance_by_name($vsphere_hostname);
				if ($storage->id > 0) {
					$response->msg = sprintf($this->lang['error_storage_exists'], $this->discovery->vmw_vsphere_ad_id);
					return $response;
				}
				$image = new image();
				$image->get_instance_by_name($vsphere_hostname);
				if ($image->id > 0) {
					$response->msg = sprintf($this->lang['error_image_exists'], $this->discovery->vmw_vsphere_ad_id);
					return $response;
				}
				$appliance = new appliance();
				$appliance->get_instance_by_name($vsphere_hostname);
				if ($appliance->id > 0) {
					$response->msg = sprintf($this->lang['error_server_exists'], $this->discovery->vmw_vsphere_ad_id);
					return $response;
				}
				
				// now we check if the given credentials work
				$htvcenter_server = new htvcenter_server();
				$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm configure -i ".$vsphere_ip." -eu ".$vsphere_user." -ep ".$vsphere_password." -eh ".$vsphere_hostname;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				$file = $this->rootdir."/plugins/vmware-vsphere/vmware-vsphere-stat/".$vsphere_ip.".integrated_successful";
				if(file_exists($file)) {
					unlink($file);
				}
				$htvcenter_server->send_command($command, NULL, true);
				while (!file_exists($file)) // check if the data file has been modified
				{
				  usleep(10000); // sleep 10ms to unload the CPU
				  clearstatcache();
				}
				// read discovery file
				if(file_exists($file)) {
					$line = file_get_contents($file);
					if (!strstr($line, "success")) {
						$response->error  = sprintf($this->lang['error_integrating'], $this->discovery->vmw_vsphere_ad_id);
						$response->error .= '<br>'.$line;
						// return to stop script
						return $response;
					}
				}
				unlink($file);

				// update discovery host to integrated = 1
				$discovery_vsphere_fields['vmw_vsphere_ad_ip']=$vsphere_ip;
				$discovery_vsphere_fields['vmw_vsphere_ad_mac']=$vsphere_mac;
				$discovery_vsphere_fields['vmw_vsphere_ad_hostname']=$vsphere_hostname;
				$discovery_vsphere_fields['vmw_vsphere_ad_user']=$vsphere_user;
				$discovery_vsphere_fields['vmw_vsphere_ad_password']=$vsphere_password;
				$discovery_vsphere_fields['vmw_vsphere_ad_comment']=$vsphere_comment;
				$discovery_vsphere_fields['vmw_vsphere_ad_is_integrated']=1;
				$discovery_vsphere_fields['vmw_vsphere_ad_hostname']=$vsphere_hostname;
				$this->discovery->update($data['vmware_vsphere_ad_id'], $discovery_vsphere_fields);

				// no resource yet, ready to create
				$vsphere_virtualization = new virtualization();
				$vsphere_virtualization->get_instance_by_type('vmware-vsphere');

				$vsphere_resource_fields["resource_ip"]=$vsphere_ip;
				$vsphere_resource_fields["resource_mac"]=$vsphere_mac;
				$vsphere_resource_fields["resource_localboot"]=1;
				$vsphere_resource_fields["resource_vtype"]=$vsphere_virtualization->id;
				$vsphere_resource_fields["resource_hostname"]=$vsphere_hostname;
				$vsphere_resource_fields["resource_capabilities"]='TYPE=local-server';
				$vsphere_resource_fields["resource_lastgood"]="-1";
				// get the new resource id from the db
				$new_resource_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$vsphere_resource_fields["resource_id"]=$new_resource_id;
				$vsphere_resource_fields["resource_vhostid"]=$new_resource_id;
				// pxe add function
				$htvcenter_server->send_command("htvcenter_server_add_resource ".$new_resource_id." ".$vsphere_mac." ".$vsphere_ip);
				$vsphere_resource->add($vsphere_resource_fields);

				// create storage server
				$storage_fields["storage_name"] = $vsphere_hostname;
				$storage_fields["storage_resource_id"] = $new_resource_id;
				$deployment = new deployment();
				$deployment->get_instance_by_type('vsphere-deployment');
				$storage_fields["storage_type"] = $deployment->id;
				$storage_fields["storage_comment"] = "vSphere Storage ".$vsphere_hostname;
				$storage_fields["storage_capabilities"] = 'TYPE=local-server';
				$storage_fields["storage_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$storage->add($storage_fields);

				// create image
				$image_deployment = new deployment();
				$image_deployment->get_instance_by_type('local-server');
				$image_fields["image_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields["image_name"] = $vsphere_hostname;
				$image_fields["image_type"] = $image_deployment->type;
				$image_fields["image_rootdevice"] = 'local disk';
				$image_fields["image_rootfstype"] = 'local disk';
				$image_fields["image_isactive"] = 1;
				$image_fields["image_storageid"] = $storage_fields["storage_id"];
				$image_fields["image_comment"] = "vSphere image ".$vsphere_hostname;
				$image_fields["image_capabilities"] = 'TYPE=local-server';
				$image->add($image_fields);

				// create appliance
				$next_appliance_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$appliance_fields["appliance_id"]=$next_appliance_id;
				$appliance_fields["appliance_name"]=$vsphere_hostname;
				// use default kernel in case the vSphere Host is stopped
				$appliance_fields["appliance_kernelid"]=1;
				$appliance_fields["appliance_imageid"]=$image_fields["image_id"];
				$appliance_fields["appliance_resources"]="$new_resource_id";
				$appliance_fields['appliance_virtualization']=$vsphere_virtualization->id;
				$appliance_fields["appliance_capabilities"]='TYPE=local-server';
				$appliance_fields["appliance_comment"]="vSphere Server ".$vsphere_hostname;
				// set start time, reset stoptime, set state
				$now=$_SERVER['REQUEST_TIME'];
				$appliance_fields["appliance_starttime"]=$now;
				$appliance_fields["appliance_stoptime"]=0;
				$appliance_fields['appliance_state']='active';
				$appliance->add($appliance_fields);

				// update resource fields with kernel + image
				$resource_fields["resource_kernel"]='default';
				$resource_fields["resource_kernelid"]=1;
				$image->get_instance_by_id($image_fields["image_id"]);
				$resource_fields["resource_image"]=$image->name;
				$resource_fields["resource_imageid"]=$image_fields["image_id"];
				$vsphere_resource->update_info($new_resource_id, $resource_fields);
			
				// add + start hook
				$appliance->get_instance_by_id($next_appliance_id);
				$now=$_SERVER['REQUEST_TIME'];
				$appliance_fields = array();
				$appliance_fields['appliance_starttime']=$now;
				$appliance_fields["appliance_stoptime"]=0;
				$appliance_fields['appliance_state']='active';
				// fill in the rest of the appliance info in the array for the plugin hook
				$appliance_fields["appliance_id"]=$next_appliance_id;
				$appliance_fields["appliance_name"]=$appliance->name;
				$appliance_fields["appliance_kernelid"]=$appliance->kernelid;
				$appliance_fields["appliance_imageid"]=$appliance->imageid;
				$appliance_fields["appliance_cpunumber"]=$appliance->cpunumber;
				$appliance_fields["appliance_cpuspeed"]=$appliance->cpuspeed;
				$appliance_fields["appliance_cpumodel"]=$appliance->cpumodel;
				$appliance_fields["appliance_memtotal"]=$appliance->memtotal;
				$appliance_fields["appliance_swaptotal"]=$appliance->swaptotal;
				$appliance_fields["appliance_nics"]=$appliance->nics;
				$appliance_fields["appliance_capabilities"]=$appliance->capabilities;
				$appliance_fields["appliance_cluster"]=$appliance->cluster;
				$appliance_fields["appliance_ssi"]=$appliance->ssi;
				$appliance_fields["appliance_resources"]=$appliance->resources;
				$appliance_fields["appliance_highavailable"]=$appliance->highavailable;
				$appliance_fields["appliance_virtual"]=$appliance->virtual;
				$appliance_fields["appliance_virtualization"]=$appliance->virtualization;
				$appliance_fields["appliance_virtualization_host"]=$appliance->virtualization_host;
				$appliance_fields["appliance_comment"]=$appliance->comment;
				$appliance_fields["appliance_event"]=$appliance->event;

				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_start_appliance_hook = $this->rootdir."/plugins/$plugin_name/htvcenter-$plugin_name-appliance-hook.php";
					if (file_exists($plugin_start_appliance_hook)) {
						require_once "$plugin_start_appliance_hook";
						$appliance_function="htvcenter_"."$plugin_name"."_appliance";
						$appliance_function=str_replace("-", "_", $appliance_function);
						// start
						$appliance_function("start", $appliance_fields);
					}
				}
				// set to started
				$active_appliance_fields['appliance_stoptime']='';
				$active_appliance_fields['appliance_starttime']=$now;
				$active_appliance_fields['appliance_state']='active';
				$appliance->update($next_appliance_id, $appliance_fields);

				// set image to active
				$image->get_instance_by_id($image_fields["image_id"]);
				$image->set_active(1);
				
				// success msg
				$response->msg = sprintf($this->lang['msg_added'], $this->discovery->vmw_vsphere_ad_id);
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
		$form = $response->get_form($this->actions_name, "add");

		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			$this->discovery->get_instance_by_id($id);
		}

		
		$d = array();

		$d['vmware_vsphere_ad_id']['label']                     = "Discovery ID";
		$d['vmware_vsphere_ad_id']['required']                  = true;
		$d['vmware_vsphere_ad_id']['validate']['regex']         = '~^[0-9]+$~i';
		$d['vmware_vsphere_ad_id']['validate']['errormsg']      = 'ID must be [0-9] only';
		$d['vmware_vsphere_ad_id']['object']['type']            = 'htmlobject_input';
		$d['vmware_vsphere_ad_id']['object']['attrib']['type']  = 'text';
		$d['vmware_vsphere_ad_id']['object']['attrib']['id']    = 'vmware_vsphere_ad_id';
		$d['vmware_vsphere_ad_id']['object']['attrib']['name']  = 'vmware_vsphere_ad_id';
		$d['vmware_vsphere_ad_id']['object']['attrib']['value']  = $this->id;

		$d['vmw_vsphere_ad_ip']['label']                     = $this->lang['ip_address'];
		$d['vmw_vsphere_ad_ip']['required']                  = true;
//		$d['vmw_vsphere_ad_ip']['validate']['regex']         = '~^[0-9]+$~i';
		$d['vmw_vsphere_ad_ip']['validate']['errormsg']      = 'ID must be [0-9] only';
		$d['vmw_vsphere_ad_ip']['object']['type']            = 'htmlobject_input';
		$d['vmw_vsphere_ad_ip']['object']['attrib']['type']  = 'text';
		$d['vmw_vsphere_ad_ip']['object']['attrib']['id']    = 'vmw_vsphere_ad_ip';
		$d['vmw_vsphere_ad_ip']['object']['attrib']['name']  = 'vmw_vsphere_ad_ip';
		$d['vmw_vsphere_ad_ip']['object']['attrib']['value']  = $this->discovery->vmw_vsphere_ad_ip;

		$d['vmw_vsphere_ad_mac']['label']                     = $this->lang['mac_address'];
		$d['vmw_vsphere_ad_mac']['required']                  = true;
//		$d['vmw_vsphere_ad_mac']['validate']['regex']         = '~^[0-9]+$~i';
		$d['vmw_vsphere_ad_mac']['validate']['errormsg']      = 'MAC must be [0-9] only';
		$d['vmw_vsphere_ad_mac']['object']['type']            = 'htmlobject_input';
		$d['vmw_vsphere_ad_mac']['object']['attrib']['type']  = 'text';
		$d['vmw_vsphere_ad_mac']['object']['attrib']['id']    = 'vmw_vsphere_ad_mac';
		$d['vmw_vsphere_ad_mac']['object']['attrib']['name']  = 'vmw_vsphere_ad_mac';
		$d['vmw_vsphere_ad_mac']['object']['attrib']['value']  = $this->discovery->vmw_vsphere_ad_mac;

		$d['vmw_vsphere_ad_hostname']['label']                     = $this->lang['hostname'];
		$d['vmw_vsphere_ad_hostname']['required']                  = true;
//		$d['vmw_vsphere_ad_hostname']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['vmw_vsphere_ad_hostname']['validate']['errormsg']      = 'Hostname must be [a-z0-9] only';
		$d['vmw_vsphere_ad_hostname']['object']['type']            = 'htmlobject_input';
		$d['vmw_vsphere_ad_hostname']['object']['attrib']['type']  = 'text';
		$d['vmw_vsphere_ad_hostname']['object']['attrib']['id']    = 'vmw_vsphere_ad_hostname';
		$d['vmw_vsphere_ad_hostname']['object']['attrib']['name']  = 'vmw_vsphere_ad_hostname';
		$d['vmw_vsphere_ad_hostname']['object']['attrib']['value']  = $this->discovery->vmw_vsphere_ad_hostname;

		$d['vmw_vsphere_ad_user']['label']                     = $this->lang['user'];
		$d['vmw_vsphere_ad_user']['required']                  = true;
//		$d['vmw_vsphere_ad_user']['validate']['regex']         =  '~^[a-z0-9]+$~i';
		$d['vmw_vsphere_ad_user']['validate']['errormsg']      = 'User must be [a-z0-9] only';
		$d['vmw_vsphere_ad_user']['object']['type']            = 'htmlobject_input';
		$d['vmw_vsphere_ad_user']['object']['attrib']['type']  = 'text';
		$d['vmw_vsphere_ad_user']['object']['attrib']['id']    = 'vmw_vsphere_ad_user';
		$d['vmw_vsphere_ad_user']['object']['attrib']['name']  = 'vmw_vsphere_ad_user';
		$d['vmw_vsphere_ad_user']['object']['attrib']['value']  = $this->discovery->vmw_vsphere_ad_user;

		$d['vmw_vsphere_ad_password']['label']                     = $this->lang['password'];
		$d['vmw_vsphere_ad_password']['required']                  = true;
//		$d['vmw_vsphere_ad_password']['validate']['regex']         =  '~^[a-z0-9]+$~i';
		$d['vmw_vsphere_ad_password']['validate']['errormsg']      = 'Password must be [a-z0-9] only';
		$d['vmw_vsphere_ad_password']['object']['type']            = 'htmlobject_input';
		$d['vmw_vsphere_ad_password']['object']['attrib']['type']  = 'password';
		$d['vmw_vsphere_ad_password']['object']['attrib']['id']    = 'vmw_vsphere_ad_password';
		$d['vmw_vsphere_ad_password']['object']['attrib']['name']  = 'vmw_vsphere_ad_password';
		$d['vmw_vsphere_ad_password']['object']['attrib']['value']  = $this->discovery->vmw_vsphere_ad_password;

		$d['vmw_vsphere_ad_comment']['label']                     = $this->lang['comment'];
		$d['vmw_vsphere_ad_comment']['required']                  = true;
		$d['vmw_vsphere_ad_comment']['validate']['regex']         =  '~^[a-z0-9- ]+$~i';
		$d['vmw_vsphere_ad_comment']['validate']['errormsg']      = 'Comment must be [a-z0-9] only';
		$d['vmw_vsphere_ad_comment']['object']['type']            = 'htmlobject_input';
		$d['vmw_vsphere_ad_comment']['object']['attrib']['type']  = 'text';
		$d['vmw_vsphere_ad_comment']['object']['attrib']['id']    = 'vmw_vsphere_ad_comment';
		$d['vmw_vsphere_ad_comment']['object']['attrib']['name']  = 'vmw_vsphere_ad_comment';
		$d['vmw_vsphere_ad_comment']['object']['attrib']['value']  = $this->discovery->vmw_vsphere_ad_comment;

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$form->add($d);
		$response->form = $form;
		return $response;
	}



}
?>
