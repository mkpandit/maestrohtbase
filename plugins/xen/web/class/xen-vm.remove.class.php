<?php
/**
 * xen-vm Remove VM(s)
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_vm_remove
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
		$response = $this->remove();
		if(isset($response->msg)) {
			sleep(2);
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/xen-vm-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$response = $this->get_response();
		$vms  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $vms !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($vms as $ex) {
				$d['param_f'.$i]['label']                       = $ex;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$appliance_id = $this->response->html->request()->get('appliance_id');
				$appliance    = new appliance();
				$resource     = new resource();
				$errors       = array();
				$message      = array();
				foreach($vms as $key => $vm) {
					$appliance->get_instance_by_id($appliance_id);
					$resource->get_instance_by_id($appliance->resources);
					$file = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.vm_list';
					if($this->file->exists($file)) {					
						$lines = explode("\n", $this->file->get_contents($file));
						if(count($lines) >= 1) {
							foreach($lines as $line) {
								if($line !== '') {
									$line = explode('@', $line);
									if($vm === $line[1]) {
										$xen = new resource();
										$xen->get_instance_by_mac($line[2]);
										// check if it is still in use
										$appliances_using_resource = $appliance->get_ids_per_resource($xen->id);
										if (count($appliances_using_resource) > 0) {
											$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
											$errors[] = sprintf($this->lang['msg_vm_resource_still_in_use'], $vm, $xen->id, $appliances_using_resource_str);
										} else {
											$xen->remove($xen->id, $line[2]);
											$command  = $this->htvcenter->get('basedir').'/plugins/xen/bin/htvcenter-xen-vm remove -n '.$vm;
											$command    .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
											$command .= ' --htvcenter-ui-user '.$this->user->name;
											$command .= ' --htvcenter-cmd-mode background';
											$resource->send_command($resource->ip, $command);
											$form->remove($this->identifier_name.'['.$key.']');
											$message[] = sprintf($this->lang['msg_removed'], $vm);

											// stop remote console
											$port   = $line[5];
											$mac    = $line[2];
											$rid    = $xen->id;
											$host_ip = $resource->ip;
											$event   = new event();
											$plugin  = new plugin();
											$enabled = $plugin->enabled();
											foreach ($enabled as $index => $name) {
												$running = $this->htvcenter->get('webdir').'/plugins/'.$name.'/.running';
												$hook = $this->htvcenter->get('webdir').'/plugins/'.$name.'/htvcenter-'.$name.'-remote-console-hook.php';
												if (file_exists($hook)) {
													if (file_exists($running)) {
														$event->log("console", $_SERVER['REQUEST_TIME'], 5, "xen-vm.remove.class.php", 'Found plugin '.$name.' providing a remote console.', "", "", 0, 0, $xen->id);
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
								}
							}
						}
					}
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
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}
?>
