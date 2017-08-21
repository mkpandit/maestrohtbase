<?php
/**
 * Hyper-V ds New volume
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_ds_create
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_ds_id';
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
	function __construct($htvcenter, $response, $controller) {
		$this->controller = $controller;
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
		$pool_id = $this->response->html->request()->get('volgroup');
		// set ENV
		$virtualization = new virtualization();
		$appliance	= new appliance();
		$resource	= new resource();
		$htvcenter_server	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$htvcenter_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->htvcenter_server		= $htvcenter_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->pool_id = $pool_id;
		require_once $this->rootdir.'/plugins/hyperv/class/hyperv-pool.class.php';
		$hyperv_pool = new hyperv_pool();
		$this->pool = $hyperv_pool;
		
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
		$response = $this->ds_create();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		
		
		$t = $this->response->html->template($this->tpldir.'/hyperv-ds-create.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['form_path'], 'lang_path');
		
		
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->response->html->request()->get('volgroup'), 'volgroup');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * volume Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds_create() {
		$response = $this->get_response();
		$form     = $response->form;
		$htvcenter_server = new htvcenter_server();

		if(!$form->get_errors() && $this->response->submit()) {

			$name			= $form->get_request('name');
			$comment		= $form->get_request('comment');
			$path			= $form->get_request('path');
			$size			= $form->get_request('size');
			$pool_id		= $form->get_request('pool');
			if (!strlen($comment)) {
				$comment = "Image Object for vhd ".$name;
			}
			
			if(!$form->get_errors()) {
				// checks if image exists already
				$image = new image();
				$image->get_instance_by_name($name);
				if (strlen($image->name)) {
					$error = sprintf($this->lang['error_exists'], $name);
				}
				if(isset($error)) {
					$response->error = $error;
				} else {
					// create image object for hyperv-vm-local deployment
					$deployment = new deployment();
					$deployment->get_instance_by_name('hyperv-deployment');
					$storage = new storage();
					$hyperv_id_list = $storage->get_ids_by_storage_type($deployment->id);
					$found_hyperv = false;
					$found_hyperv_id = -1;
					foreach ($hyperv_id_list as $list) {
						foreach ($list as $hyperv_id) {
							$storage->get_instance_by_id($hyperv_id);
							if ($storage->resource_id == $this->resource->id) {
								$found_hyperv = true;
								$found_hyperv_id = $storage->id;
								break;
							}
						}
					}
					if ($found_hyperv) {
						// physically create vhd on hyper-v host
						$command  = $this->htvcenter->get('basedir')."/plugins/hyperv/bin/htvcenter-hyperv-datastore create";
						$command .= " -i ".$this->resource->ip;
						$command .= " -n ".$name;
						$command .= " -l ".str_replace(" ", "@", $path);
						$command .= " -s ".$size;
						$command .= ' --htvcenter-ui-user '.$this->user->name;
						$command .= ' --htvcenter-cmd-mode fork';
						$htvcenter_server = new htvcenter_server();
						$htvcenter_server->send_command($command, NULL, true);

						// TODO: check that it was creaeted
					
						$image = new image();
						$image_fields = array();
						$vm_image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$image_fields["image_id"] = $vm_image_id;
						$image_fields['image_name'] = $name;
						$image_fields['image_type'] = 'hyperv-deployment';
						$image_fields['image_rootfstype'] = 'local';
						$image_fields['image_isactive']=0;
						$image_fields['image_size']=$size;
						$image_fields['image_storageid'] = $found_hyperv_id;
						$image_fields['image_comment'] = $comment;
						$image_fields['image_rootdevice'] = $pool_id.'%'.$path.'/'.$name.'.vhdx';
						$image_fields['image_capabilities'] = 'TYPE=hyperv-deployment';
						$image->add($image_fields);
						$image->get_instance_by_id($vm_image_id);
						
						// set id in response
						$response->resource_id = $vm_image_id;
						
					}
					$response->msg = sprintf($this->lang['msg_added'], $name);
				}
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
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
		$form = $response->get_form($this->actions_name, 'create');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');


		// if we come from the wizard suggest the server name
		$vm_name_suggestion = '';
		$image_path = $response->html->request()->get('image_path');
		
		// from wizard ?
		if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($this->user->wizard_id);
			$vm_name_suggestion = $appliance->name;
		}
		
		$pool_arr = array();
		$existing_pool_arr = $this->pool->get_all_ids();
		foreach ($existing_pool_arr as $iid_ar) {
			$pool_id = $iid_ar['hyperv_pool_id'];
			$this->pool->get_instance_by_id($pool_id);
			$pool_arr[] = array($this->pool->hyperv_pool_id, $this->pool->hyperv_pool_name);
		}
		

		$d['name']['label']							    = $this->lang['form_name'];
		$d['name']['required']						    = true;
		$d['name']['validate']['regex']				    = $this->htvcenter->get('regex', 'hostname');
		$d['name']['validate']['errormsg']			    = sprintf($this->lang['error_name'], $this->htvcenter->get('regex', 'hostname'));
		$d['name']['object']['type']				    = 'htmlobject_input';
		$d['name']['object']['attrib']['id']		    = 'name';
		$d['name']['object']['attrib']['name']		    = 'name';
		$d['name']['object']['attrib']['type']		    = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="vol" data-length="8"';
		$d['name']['object']['attrib']['value']		    = $vm_name_suggestion;
		$d['name']['object']['attrib']['maxlength']	    = 50;

		$d['comment']['label']							    = $this->lang['form_comment'];
		$d['comment']['required']						    = false;
		$d['comment']['object']['type']						= 'htmlobject_input';
		$d['comment']['object']['attrib']['id']				= 'comment';
		$d['comment']['object']['attrib']['name']		    = 'comment';
		$d['comment']['object']['attrib']['type']		    = 'text';
		$d['comment']['object']['attrib']['value']		    = '';
		$d['comment']['object']['attrib']['maxlength']	    = 255;

		$d['pool']['label']							    = $this->lang['form_datastore'];
		$d['pool']['required']						    = true;
		$d['pool']['object']['type']					= 'htmlobject_select';
		$d['pool']['object']['attrib']['index']			= array(0,1);		
		$d['pool']['object']['attrib']['id']			= 'pool';
		$d['pool']['object']['attrib']['name']		    = 'pool';
		$d['pool']['object']['attrib']['options']	    = $pool_arr;

		$d['size']['label']							    = $this->lang['form_size'];
		$d['size']['required']						    = true;
		$d['size']['object']['type']					= 'htmlobject_input';
		$d['size']['object']['attrib']['id']			= 'size';
		$d['size']['object']['attrib']['name']		    = 'size';
		$d['size']['object']['attrib']['type']		    = 'text';
		$d['size']['object']['attrib']['value']		    = '';
		$d['size']['object']['attrib']['maxlength']	    = 10;
		
		// boot from
		$d['path'] = '';

		$d['path']['label']                    = $this->lang['form_iso_path'];
		$d['path']['object']['type']           = 'htmlobject_input';
		$d['path']['object']['attrib']['type'] = 'text';
		$d['path']['object']['attrib']['id']   = 'path';
		$d['path']['object']['attrib']['name'] = 'path';
		$d['path']['object']['attrib']['value']	= $image_path;

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}



}
?>
