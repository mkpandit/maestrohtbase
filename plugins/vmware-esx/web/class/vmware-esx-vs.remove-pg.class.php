<?php
/**
 * ESX Hosts remove PortGroup from VSwitch
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_vs_remove_pg
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_vs_id';
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
		$this->response->add($this->identifier_name.'[]', '');
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
		$this->vs_name = $vs_name;
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
		$response = $this->ne_remove_vs_pg();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'update', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-vs-remove-pg.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->vs_name), 'label');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove PortGroup from VSwitch
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function ne_remove_vs_pg() {
		$response = $this->get_response();
		$portgroup_arr  = $response->html->request()->get($this->identifier_name);
		$vs_name = $response->html->request()->get('vs_name');
		$form     = $response->form;
		if( $portgroup_arr !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($portgroup_arr as $pg) {
				$d['param_f'.$i]['label']                       = $pg;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $pg;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$i++;
			// add vs_name
			$d['param_f'.$i]['label']                       = ' ';
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'hidden';
			$d['param_f'.$i]['object']['attrib']['name']    = 'vs_name';
			$d['param_f'.$i]['object']['attrib']['value']   = $vs_name;
			
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors     = array();
				$message    = array();
				foreach($portgroup_arr as $key => $pg) {

					if ($vs === 'vSwitch0') {
						$errors[] = $this->lang['msg_not_removing'];
						$form->remove($this->identifier_name.'['.$key.']');
						continue;
					}
					$pg  = str_replace(" ", "@", $pg);
					$command  = $this->htvcenter->get('basedir')."/plugins/vmware-esx/bin/htvcenter-vmware-esx-network remove_pg -i ".$this->resource->ip." -n ".$vs_name." -g ".$pg;
					$command .= ' --htvcenter-ui-user '.$this->user->name;
					$command .= ' --htvcenter-cmd-mode background';
					if(file_exists($this->statfile_ne)) {
						unlink($this->statfile_ne);
					}
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command($command, NULL, true);
					while (!file_exists($this->statfile_ne)) {
		  				usleep(10000); // sleep 10ms to unload the CPU
		  				clearstatcache();
					}
					$form->remove($this->identifier_name.'['.$key.']');
					$message[] = sprintf($this->lang['msg_removed'], $pg);
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
		$form = $response->get_form($this->actions_name, 'remove_pg');
		$response->form = $form;
		return $response;
	}

}
?>
