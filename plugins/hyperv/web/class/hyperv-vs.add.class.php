<?php
/**
 * Hyper-V Hosts Add VSwitch
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_vs_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_vs_id';
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
		$this->statfile_vm = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.net_config';
		$this->statfile_ne_adapters = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.net_adapters';
		

		
		$this->statfile_ds = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.ds_list';
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'add_pg', $this->message_param, $response->msg).'&vs_name='.$response->form->get_request('name')
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hyperv-vs-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
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
			$ports    = $form->get_request('ports');
			$ports_arr = explode("/", $ports);
			$adapater = $ports_arr[0];
			$command  = $this->htvcenter->get('basedir')."/plugins/hyperv/bin/htvcenter-hyperv-network add_vs";
			$command .= " -i ".$this->resource->ip;
			$command .= " -n ".$name;
			$command .= " -p ".$adapater;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode fork';

			if (file_exists($this->statfile_ne)) {
				$lines = explode("\n", file_get_contents($this->statfile_ne));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->string_to_array(trim($line), '|', '=');
							if($name === $line['Name']) {
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

		$adapter_select_arr =  array();
		// get the datastore and vswitchlist for the selects
		if (file_exists($this->statfile_ne_adapters)) {
			$lines = explode("\n", file_get_contents($this->statfile_ne_adapters));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = $this->string_to_array($line, '|', '=');
						$sw_name = str_replace('"', '', $line['Name']);
						if (!strstr($sw_name, 'vEthernet')) {
							$sw_desc= str_replace('"', '', $line['ifDesc']);
							$adapter_select_arr[] = array($sw_name."/".$sw_desc,$sw_name."/".$sw_desc);
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

		$d['ports']['label']						= $this->lang['form_ports'];
		$d['ports']['object']['type']				= 'htmlobject_select';
		$d['ports']['object']['attrib']['index']	= array(0,0);
		$d['ports']['object']['attrib']['id']		= 'ports';
		$d['ports']['object']['attrib']['name']		= 'ports';
		$d['ports']['object']['attrib']['options']	= $adapter_select_arr;

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

	function string_to_array($string, $element_delimiter = '|', $value_delimiter = '=') {
		$results = array();
		$array = explode($element_delimiter, $string);
		foreach ($array as $result) {
			$element = explode($value_delimiter, $result);
			if (isset($element[1])) {
				$results[$element[0]] = $element[1];
			}
		}
		return $results;
	}
	
}
?>
