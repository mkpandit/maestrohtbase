<?php
/**
 * vSphere Hosts Add NAS DataStore
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_dc_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_dc_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_dc_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_dc_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_dc_id';
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
		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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
		$this->statfile_dc = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.dc_list';
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
		$response = $this->ds_add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-dc-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
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
	function ds_add() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name			= $form->get_request('name');
			$command     = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datacenter create -i ".$this->resource->ip." -n ".$name;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode background';

			if (file_exists($this->statfile_dc)) {
				$lines = explode("\n", file_get_contents($this->statfile_dc));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
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
				if(file_exists($this->statfile_dc)) {
					unlink($this->statfile_dc);
				}

				// send command to add the nas
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, NULL, true);
				while (!file_exists($this->statfile_dc)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$response->msg = sprintf($this->lang['msg_added'], $name);
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
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}
?>
