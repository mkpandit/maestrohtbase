<?php
/**
 * vSphere Hosts Add VSwitch
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vs_add
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
		$host = $this->response->html->request()->get('esxhost');
		if($host === '') {
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
		$this->host = $host;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.ds_list';
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg).'&vs_name='.$response->form->get_request('name')
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vs-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->host), 'label');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Network add VSwitch
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function add() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name     = $form->get_request('name');
			$pnic    = $form->get_request('pnic');
			$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-network add_vs";
			$command .= " -i ".$this->resource->ip;
			$command .= " -e ".$this->host;
			$command .= " -n ".$name;
			$command .= " -l ".$pnic;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode background';

			if (file_exists($this->statfile_ne)) {
				$lines = explode("\n", file_get_contents($this->statfile_ne));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							if($name === $line['name']) {
								$error = sprintf($this->lang['error_exists'], $name);
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

				// send command to add the nas
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, NULL, true);
				while (!file_exists($this->statfile_ne)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$response->msg = sprintf($this->lang['msg_added'], $name);
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
		
		$pnic_select_arr = array();
		$pnic_select_arr[] = array('None', 'None');
		
		if (file_exists($this->statfile_ne)) {
			$lines = explode("\n", file_get_contents($this->statfile_ne));
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
							
							foreach ($pnic_arr as $pnic) {
								$pnic_select_arr[] = array($pnic,$pnic);
							}
//							print_r($pnic_used_arr);
//							print_r($pnic_avail_arr);
//							print_r($pnic_select_arr);
							
						}
					}
				}
			}
		}
		
		

		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 50;

		
		$d['pnic']['label']							= $this->lang['form_pnic'];
		$d['pnic']['object']['type']				= 'htmlobject_select';
		$d['pnic']['object']['attrib']['index']		= array(0,0);
		$d['pnic']['object']['attrib']['id']		= 'pnic';
		$d['pnic']['object']['attrib']['name']		= 'pnic';
		$d['pnic']['object']['attrib']['options']	= $pnic_select_arr;
		$d['pnic']['object']['attrib']['selected']	= array('None');
		
		
/*		$spa[] = array('8');
		$spa[] = array('24');
		$spa[] = array('56');
		$spa[] = array('120');
		$spa[] = array('248');
		$spa[] = array('504');
		$spa[] = array('1016');
		$spa[] = array('2040');
		$spa[] = array('4088');

		$d['ports']['label']						= $this->lang['form_ports'];
		$d['ports']['object']['type']				= 'htmlobject_select';
		$d['ports']['object']['attrib']['index']	= array(0,0);
		$d['ports']['object']['attrib']['id']		= 'ports';
		$d['ports']['object']['attrib']['name']		= 'ports';
		$d['ports']['object']['attrib']['options']	= $spa;
 * 
 */

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}
	
}
?>
