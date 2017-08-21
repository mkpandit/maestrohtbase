<?php
/**
 * vSphere Hosts remove VMDK
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_dc_remove_cluster
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

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('cluster', $this->response->html->request()->get('cluster'));
		$this->response->params[$this->identifier_name] = $this->response->html->request()->get($this->identifier_name);

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
		$datacenter = $this->response->html->request()->get($this->identifier_name);
		if($datacenter === '') {
			return false;
		}
		$this->datacenter = $datacenter;
		
		$cluster = $this->response->html->request()->get('cluster');
		if($cluster === '') {
			return false;
		}
		$this->cluster = $cluster;

		// set ENV
		$appliance = new appliance();
		$resource = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource = $resource;
		$this->appliance = $appliance;

		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.dc_list';
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
		$response = $this->remove_cluster();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-dc-remove_cluster.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'],$this->response->html->request()->get('edit'), $this->appliance->name), 'label');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove Cluster
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove_cluster() {
		$response = $this->get_response();
		$datacenter  = $response->html->request()->get($this->identifier_name);
		$cluster  = $response->html->request()->get('cluster');
		
		$form     = $response->form;
		if( $cluster !== '' ) {
			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			$d['param_f'.$i]['label']                       = $cluster;
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
			$d['param_f'.$i]['object']['attrib']['name']    = 'cluster';
			$d['param_f'.$i]['object']['attrib']['value']   = $cluster;
			$d['param_f'.$i]['object']['attrib']['checked'] = true;

			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors     = array();
				$message    = array();
				
				$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datacenter removecluster";
				$command .= " -i ".$this->resource->ip;
				$command .= " -n ".$this->datacenter;
				$command .= " -c ".$cluster;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				
				# end remove loop
				if(file_exists($this->statfile)) {
					unlink($this->statfile);
				}
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, NULL, true);
				while (!file_exists($this->statfile)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$form->remove($this->identifier_name);
				$message[] = sprintf($this->lang['msg_removed'], $datacenter);

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
		$form = $response->get_form($this->actions_name, 'remove_cluster');
		$response->form = $form;
		return $response;
	}


}
?>
