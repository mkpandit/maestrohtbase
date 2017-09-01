<?php
/**
 * Removes discovered ESX Hosts
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_discovery_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_discovery_id';
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
		$this->user	    = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');

		$this->response->add('id', $this->response->html->request()->get('id'));
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
		if ($this->response->html->request()->get('id') === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}
		$response = $this->delete();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-discovery-remove.tpl.php');
		$t->add($response->form->get_elements());
		$t->add($response->html->thisfile, "thisfile");
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['label'], 'label');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function delete() {

		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$id = $form->get_request('id');

			$errors  = array();
			$message = array();

			// get name before delete
			$this->discovery->get_instance_by_id($id);
			// appliance exist ?
			$appliance = new appliance();
			$appliance->get_instance_by_name($this->discovery->vmw_esx_ad_hostname);
			if ($appliance->id > 0) {

				// fill array for stop hook
				$appliance_fields = array();
				$appliance_fields["appliance_id"]=$appliance->id;
				$appliance_fields["appliance_name"]=$appliance->name;
				$appliance_fields["appliance_kernelid"]=$appliance->kernelid;
				$appliance_fields["appliance_imageid"]=$appliance->imageid;
				$appliance_fields["appliance_cpunumber"]=$appliance->cpunumber;
				$appliance_fields["appliance_cpuspeed"]=$appliance->cpuspeed;
				$appliance_fields["appliance_cpumodel"]=$appliance->cpumodel;
				$appliance_fields["appliance_memtotal"]=$appliance->memtotal;
				$appliance_fields["appliance_swaptotal"]=$appliance->swaptotal;
				$appliance_fields["appliance_nics"]=$appliance->nics;
				$appliance_fields["appliance_capabilities"]=$appliance->capabilities;
				$appliance_fields["appliance_cluster"]=$appliance->cluster;
				$appliance_fields["appliance_ssi"]=$appliance->ssi;
				$appliance_fields["appliance_resources"]=$appliance->resources;
				$appliance_fields["appliance_highavailable"]=$appliance->highavailable;
				$appliance_fields["appliance_virtual"]=$appliance->virtual;
				$appliance_fields["appliance_virtualization"]=$appliance->virtualization;
				$appliance_fields["appliance_virtualization_host"]=$appliance->virtualization_host;
				$appliance_fields["appliance_comment"]=$appliance->comment;
				$appliance_fields["appliance_event"]=$appliance->event;
				// run appliance stop hook before remove
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_start_appliance_hook = $this->rootdir."/plugins/$plugin_name/htvcenter-$plugin_name-appliance-hook.php";
					if (file_exists($plugin_start_appliance_hook)) {
						require_once "$plugin_start_appliance_hook";
						$appliance_function="htvcenter_"."$plugin_name"."_appliance";
						$appliance_function=str_replace("-", "_", $appliance_function);
						// start
						$appliance_function("stop", $appliance_fields);
					}
				}
				sleep(4);
				
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				if ($resource->id > 0) {
					// image exist ?
					$image = new image();
					$image->get_instance_by_name($appliance->name);
					if ($image->id > 1) {
						$image->remove($image->id);
					}
					// local storage exists ?
					$local_storage = new storage();
					$local_storage->get_instance_by_name($appliance->name);
					if ($local_storage->id > 0) {
						$local_storage->remove($local_storage->id);
					}
					$resource->remove($resource->id, $resource->mac);
				}
				$appliance->remove($appliance->id);
			}
			//delete here;
			$error = $this->discovery->remove($id);
			$message[] = sprintf($this->lang['msg_removed'], $id);

			if(count($errors) === 0) {
				$response->msg = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response->error = join('<br>', $msg);
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
		$id       = $this->response->html->request()->get('id');
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'remove');
		$d        = array();
		if( $id !== '' ) {
			$this->discovery->get_instance_by_id($id);
			$d['param_f']['label']                       = "IP:".$this->discovery->vmw_esx_ad_ip." Mac:".$this->discovery->vmw_esx_ad_mac;
			$d['param_f']['object']['type']              = 'htmlobject_input';
			$d['param_f']['object']['attrib']['type']    = 'checkbox';
			$d['param_f']['object']['attrib']['name']    = 'id';
			$d['param_f']['object']['attrib']['id']      = 'id';
			$d['param_f']['object']['attrib']['value']   = $id;
			$d['param_f']['object']['attrib']['checked'] = true;
		}
		$form->add($d);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$response->form = $form;
		return $response;
	}



}
?>
