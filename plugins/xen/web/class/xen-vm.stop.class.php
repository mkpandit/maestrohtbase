<?php
/**
 * xen-vm Stop VM(s)
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_vm_stop
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'xen_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'xen_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xen_vm_identifier';
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
		$this->user	    = $htvcenter->user();
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
		$response = $this->stop();
		sleep(2);
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
	function stop() {
		$response = '';
		$vms = $this->response->html->request()->get($this->identifier_name);
		if( $vms !== '' ) {
			$appliance_id = $this->response->html->request()->get('appliance_id');
			$appliance    = new appliance();
			$resource     = new resource();
			$errors       = array();
			$message      = array();
			foreach($vms as $key => $vm) {
				$appliance->get_instance_by_id($appliance_id);
				$resource->get_instance_by_id($appliance->resources);
				$file = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.vm_list';
				$command  = $this->htvcenter->get('basedir').'/plugins/xen/bin/htvcenter-xen-vm stop -n '.$vm;
				$command    .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				$resource->send_command($resource->ip, $command);
				$message[] = sprintf($this->lang['msg_stoped'], $vm);

				// stop remote console
				if($this->file->exists($file)) {
					$lines   = explode("\n", $this->file->get_contents($file));
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($line[1] === $vm) {
								$port   = $line[5];
								$mac    = $line[2];
								$xenvm = new resource();
								$xenvm->get_instance_by_mac($mac);
								$rid    = $xenvm->id;
								$host_ip = $resource->ip;
							}
						}
					}
					$event   = new event();
					$plugin  = new plugin();
					$enabled = $plugin->enabled();
					foreach ($enabled as $index => $name) {
						$running = $this->htvcenter->get('webdir').'/plugins/'.$name.'/.running';
						$hook = $this->htvcenter->get('webdir').'/plugins/'.$name.'/htvcenter-'.$name.'-remote-console-hook.php';
						if (file_exists($hook)) {
							if (file_exists($running)) {
								$event->log("console", $_SERVER['REQUEST_TIME'], 5, "xen-vm.stop.class.php", 'Found plugin '.$name.' providing a remote console.', "", "", 0, 0, $xenvm->id);
								require_once($hook);
								$console_function = 'htvcenter_'.$name.'_disable_remote_console';
								$console_function = str_replace("-", "_", $console_function);
								echo "$console_function($host_ip, $port, $rid, $mac, $vm)";
								$console_function($host_ip, $port, $rid, $mac, $vm);
							}
						}
					}
				}
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
