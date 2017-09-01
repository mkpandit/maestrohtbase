<?php
/**
 * vmware_vsphere_ds_clone
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_ds_clone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_ds_id';
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
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;
		$this->user = $htvcenter->user();

		// set ENV
		$id = $this->response->html->request()->get('appliance_id');
		$appliance = $this->htvcenter->appliance();
		$appliance->get_instance_by_id($id);

		$resource  = $this->htvcenter->resource();
		$resource->get_instance_by_id($appliance->resources);

		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->htvcenter->get('webdir').'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vmdk_list';

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));
		$this->response->add('vmdk', $this->response->html->request()->get('vmdk'));
		$this->clone_timeout = 1800;

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
		$response = $this->duplicate();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-ds-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'],$this->response->html->request()->get('vmdk'), $this->response->html->request()->get('volgroup'), $this->appliance->name), 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function duplicate() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$this->resource->generate_mac();
			$name        = $form->get_request('name');
			$datastore   = $this->response->html->request()->get('volgroup');
			$command     = $this->htvcenter->get('basedir').'/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datastore clone_vmdk';
			$command    .= ' -i '.$this->resource->ip;
			$command    .= ' -n '.$datastore;
			$command    .= ' -f '.$this->response->html->request()->get('vmdk');
			$command    .= ' -c '.$name;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode background';
			if ($this->file->exists($this->statfile)) {
				$lines = explode("\n", $this->file->get_contents($this->statfile));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$check = basename($line);
							$check = str_replace(".vmdk", "", $check);
							if($name === $check) {
								$error = sprintf($this->lang['error_exists'], $name);
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {

				if(file_exists($this->statfile)) {
					unlink($this->statfile);
				}
				// send command
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, $this->clone_timeout, true);
				while (!file_exists($this->statfile)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				

				// find storage
				$deployment = new deployment();
				$deployment->get_instance_by_name('vsphere-deployment');
				$storage = new storage();
				$vmware_vsphere_id_list = $storage->get_ids_by_storage_type($deployment->id);
				$found_vmware_vsphere = false;
				$found_vmware_vsphere_id = -1;
				foreach ($vmware_vsphere_id_list as $list) {
					foreach ($list as $vmware_vsphere_id) {
						$storage->get_instance_by_id($vmware_vsphere_id);
						if ($storage->resource_id == $this->appliance->resources) {
							$found_vmware_vsphere = true;
							$found_vmware_vsphere_id = $storage->id;
							break;
						}
					}
				}
				if ($found_vmware_vsphere) {
					$image = new image();
					$image_fields = array();
					$image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$image_fields["image_id"] = $image_id;
					$image_fields['image_name'] = $name;
					$image_fields['image_type'] = 'vsphere-deployment';
					$image_fields['image_rootfstype'] = 'local';
					$image_fields['image_isactive']=0;
					$image_fields['image_storageid'] = $found_vmware_vsphere_id;
					$image_fields['image_comment'] = "Image Object for vmdk $name";
					$image_fields['image_rootdevice'] = $datastore.':'.$name.'/'.$name.'.vmdk';
					$image->add($image_fields);
				}
				$response->msg = sprintf($this->lang['msg_cloned'], $this->response->html->request()->get('vmdk'), $name);
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
		$form = $response->get_form($this->actions_name, 'clone');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="vmdk" data-length="8"';
		$d['name']['object']['attrib']['value']         = $this->response->html->request()->get('vmdk').'c';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
