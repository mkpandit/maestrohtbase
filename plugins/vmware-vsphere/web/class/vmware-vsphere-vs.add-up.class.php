<?php
/**
 * vSphere Hosts Add Uplink to PortGroup
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vs_add_up
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_vs_id';
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
		$this->response->add('vs_name', $this->response->html->request()->get('vs_name'));
		$this->response->add('pg_name', $this->response->html->request()->get('pg_name'));
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
		$vs_name = $this->response->html->request()->get('vs_name');
		if($vs_name === '') {
			return false;
		}
		$host = $this->response->html->request()->get('esxhost');
		if($host === '') {
			return false;
		}
		
		$pg_name = $this->response->html->request()->get('pg_name');

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
		$this->host = $host;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.ds_list';
		$this->vmware_mac_base = "00:50:56";
		$this->vs_name = $vs_name;
		$this->pg_name = $pg_name;
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
		$response = $this->ne_add_pg_up();
		if(isset($response->msg)) {
			if($this->response->html->request()->get('pg_name') !== '') {
				$action = 'update';
			} else {
				$action = 'edit';
			}
			$this->response->redirect(
				$this->response->get_url($this->actions_name, $action, $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vs-add-up.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		if($this->pg_name !== '') {
			$t->add(sprintf($this->lang['label_portgroup'], $this->pg_name, $this->vs_name), 'label');
		} else {
			$t->add(sprintf($this->lang['label'], $this->vs_name), 'label');
		}
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Network add Uplink to PortGroup
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ne_add_pg_up() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$uplink			= $form->get_request('uplink');
			$vs_name		= $response->html->request()->get('vs_name');
			$pg_name		= $response->html->request()->get('pg_name');
			$pg_name  = str_replace(" ", "@", $pg_name);
			// handle command
			if($pg_name !== '') {
				$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-network add_pg_up";
				$command .= " -i ".$this->resource->ip;
				$command .= " -e ".$this->host;
				$command .= " -n ".$vs_name;
				$command .= " -g ".$pg_name;
				$command .= " -l ".$uplink;
			} else {
				$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-network add_vs_up";
				$command .= " -i ".$this->resource->ip;
				$command .= " -e ".$this->host;
				$command .= " -n ".$vs_name;
				$command .= " -l ".$uplink;
			}
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode background';

			// check vswitch exists
			$error = sprintf($this->lang['error_not_exists'], $vs_name);
			if (file_exists($this->statfile_ne)) {
				$lines = explode("\n", file_get_contents($this->statfile_ne));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							if ($line['type'] === 'vs') {
								if($vs_name === $line['name']) {
									unset($error);
									break;
								}
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_ne)) {
					unlink($this->statfile_ne);
				}
				// send command
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, NULL, true);
				while (!file_exists($this->statfile_ne)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$response->msg = sprintf($this->lang['msg_added'], $vs_name);
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
		$form = $response->get_form($this->actions_name, 'add_up');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$uplink_arr = array();
		if ($this->file->exists($this->statfile_ne)) {
			$lines = explode("\n", $this->file->get_contents($this->statfile_ne));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = $this->htvcenter->string_to_array($line, '|', '=');
						
						if($line['type'] == 'pnic') {

							$pnic_used_str = $line['pnic_used'];
							$pnic_avail_str = $line['pnic_avail'];
							$pnic_used_arr = explode(",", $pnic_used_str);
							$pnic_avail_arr = explode(",", $pnic_avail_str);
							$pnic_arr = array_diff($pnic_used_arr, $pnic_avail_arr);
							
							$pnic_arr = $pnic_avail_arr;
							foreach ($pnic_arr as $pnic) {
								$uplink_arr[] = array($pnic,$pnic);
							}
						}
					}
				}
			}
		}

		$d['uplink']['label']						= $this->lang['form_uplink'];
		$d['uplink']['object']['type']				= 'htmlobject_select';
		$d['uplink']['object']['attrib']['index']	= array(0,0);
		$d['uplink']['object']['attrib']['id']		= 'uplink';
		$d['uplink']['object']['attrib']['name']	= 'uplink';
		$d['uplink']['object']['attrib']['options']	= $uplink_arr;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
