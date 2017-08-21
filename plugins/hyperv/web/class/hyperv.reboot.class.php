<?php
/**
 * Hyper-V Hosts remove VM
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_reboot
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_msg";
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
		$appliance	= new appliance();
		$resource	= new resource();
		$this->resource		= $resource;
		$this->appliance	= $appliance;
		$this->statfile_vm = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.net_config';
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
		$response = $this->reboot();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hyperv-vm-reboot.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Reboot Hyper-V Host
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function reboot() {
		$response = $this->get_response();
		$data  = $response->html->request()->get('appliance_id');
		$form     = $response->form;
		if( $data !== '' ) {
			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');
			
			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			$hyperv_host_appliance = new appliance();
			foreach($data as $ex) {
				$hyperv_host_appliance->get_instance_by_id($ex);
				$d['param_f'.$i]['label']                       = $hyperv_host_appliance->name;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors     = array();
				$message    = array();
				foreach($data as $key => $hyperv) {
					// get the hyperv appliance
					$hyperv_host_appliance->get_instance_by_id($ex);
					$hyperv_host_resource = new resource();
					$hyperv_host_resource->get_instance_by_id($hyperv_host_appliance->resources);
					$command  = $this->htvcenter->get('basedir')."/plugins/hyperv/bin/htvcenter-hyperv-vm host_reboot -i ".$hyperv_host_resource->ip;
					$command .= ' --htvcenter-ui-user '.$this->user->name;
					$command .= ' --htvcenter-cmd-mode fork';
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command($command, NULL, true);
					$form->remove($this->identifier_name.'['.$key.']');
					$message[] = sprintf($this->lang['msg_rebooted'], $hyperv_host_appliance->name);
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		} else {
			$response->msg = '';
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
		$form = $response->get_form($this->actions_name, 'reboot');
		$response->form = $form;
		return $response;
	}


}
?>
