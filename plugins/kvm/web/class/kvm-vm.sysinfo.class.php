<?php
/**
 * KVM Adds/Removes an Image from a Volume
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kvm_vm_sysinfo
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
		$this->response   = $response;
		$this->htvcenter    = $htvcenter;
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
		$response = $this->sysinfo();
		return $response;
	}

	//--------------------------------------------
	/**
	 * Sysinfo
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function sysinfo() {

		$appliance = $this->htvcenter->appliance();
		$appliance->get_instance_by_id($this->response->html->request()->get('appliance_id'));
		$resource = $this->htvcenter->resource();
		$resource->get_instance_by_id($appliance->resources);

		$filename = $resource->id.'.sysinfo';
		$file = $this->htvcenter->get('basedir').'/plugins/kvm/web/kvm-stat/'.$filename;

		$command  = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm-sysinfo';
		$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
		$command .= ' --file-name '.$filename;
		#$command .= ' --htvcenter-cmd-mode background';
		
		if($this->htvcenter->file()->exists($file)) {
			$this->htvcenter->file()->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->htvcenter->file()->exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}

		$d = $this->response->html->div();
		$d->id = "kvm-sysinfo";
		$d->add($this->htvcenter->file()->get_contents($file));

		$this->htvcenter->file()->remove($file);

		return $d;

	}

}
?>
