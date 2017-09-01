<?php
/**
 * ESX Hosts remove iSCSI DataStore
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_ds_remove_iscsi
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_ds_id';
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
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));
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
		$ds_name = $this->response->html->request()->get('volgroup');
		if($ds_name === '') {
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
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
		$this->vmware_mac_base = "00:50:56";
		$this->ds_name = $ds_name;
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
		$response = $this->ds_remove_iscsi();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-ds-remove-iscsi.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove NAS DataStore
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function ds_remove_iscsi() {
		$response	= $this->get_response();
		$form		= $response->form;

		if( $this->ds_name !== '' ) {
			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			$d['param_f'.$i]['label']                       = $this->ds_name;
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
			$d['param_f'.$i]['object']['attrib']['name']    = 'name';
			$d['param_f'.$i]['object']['attrib']['value']   = $this->ds_name;
			$d['param_f'.$i]['object']['attrib']['checked'] = true;
			$form->add($d);
			$i = 1;
			$d['param_f'.$i]['label']                       = "Target ".$this->lang['form_name'];
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'text';
			$d['param_f'.$i]['object']['attrib']['name']    = 'target';
			$d['param_f'.$i]['object']['attrib']['value']   = '';
			$form->add($d);
			$i = 2;
			$d['param_f'.$i]['label']                       = "Target ".$this->lang['form_ip'];
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'text';
			$d['param_f'.$i]['object']['attrib']['name']    = 'ip';
			$d['param_f'.$i]['object']['attrib']['value']   = '';
			$form->add($d);

			if(!$form->get_errors() && $response->submit()) {
				$error = sprintf($this->lang['error_not_exists'], $this->ds_name);
				if (file_exists($this->statfile_ds)) {
					$lines = explode("\n", file_get_contents($this->statfile_ds));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								if($this->ds_name === $line[0]) {
									unset($error);
								}
							}
						}
					}
				}
				
				$target				= $form->get_request('target');	
				$ip					= $form->get_request('ip');	
				if($target === '') {
					$error = $this->lang['error_no_target']."<br>";
				}
				if($ip === '') {
					$error = $this->lang['error_no_ip']."<br>";
				}

				if(isset($error)) {
					$response->error = $error;
				} else {
					if(file_exists($this->statfile_ds)) {
						unlink($this->statfile_ds);
					}

					$command     = $this->htvcenter->get('basedir')."/plugins/vmware-esx/bin/htvcenter-vmware-esx-datastore remove_iscsi -i ".$this->resource->ip." -n ".$this->ds_name." -t ".$target." -q ".$ip;
					$command .= ' --htvcenter-ui-user '.$this->user->name;
					$command .= ' --htvcenter-cmd-mode background';

					// send command to remove the iscsi
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command($command, NULL, true);
					while (!file_exists($this->statfile_ds)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					$response->msg = sprintf($this->lang['msg_removed'], $this->ds_name);
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
		$form = $response->get_form($this->actions_name, 'remove_iscsi');
		$response->form = $form;
		return $response;
	}


}
?>
