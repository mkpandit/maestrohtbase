<?php
/**
 * kvm-vm Reboot VM(s)
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kvm_vm_reboot
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_vm_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
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
		$this->file                     = $htvcenter->file();
		$this->htvcenter                  = $htvcenter;
		$this->user						= $htvcenter->user();
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
		$response = $this->reboot();
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response)
		);
	}

	//--------------------------------------------
	/**
	 * Stop
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function reboot() {
		$response = '';
		$vms = $this->response->html->request()->get($this->identifier_name);
		if( $vms !== '' ) {
			$appliance = $this->htvcenter->appliance();
			$appliance->get_instance_by_id($this->response->html->request()->get('appliance_id'));
			$server = $this->htvcenter->resource();
			$server->get_instance_by_id($appliance->resources);

			$resource = $this->htvcenter->resource();
			$virttype = $this->htvcenter->virtualization();
			$errors   = array();
			$message  = array();
			foreach($vms as $key => $vm) {
				$resource->get_instance_id_by_hostname($vm);
				$resource->get_instance_by_id($resource->id);
				$virttype->get_instance_by_id($resource->vtype);

				$file = $this->htvcenter->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
				$command  = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm-vm reboot -n '.$vm;
				$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' -y '.$virttype->type;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';

				$server->send_command($resource->ip, $command);
				$message[] = sprintf($this->lang['msg_rebooted'], $vm);
			}
			if(count($errors) === 0) {
				$response = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response = join('<br>', $msg);
			}
		} else {
			$response = '';
		}
		return $response;
	}

}
?>
