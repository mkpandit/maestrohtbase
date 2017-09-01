<?php
/**
 * hyperv_ds_clone
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_ds_clone
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
		$this->rootdir = $this->htvcenter->get('webdir');
		$this->user = $htvcenter->user();

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));
		$this->response->add('vhdx', $this->response->html->request()->get('vhdx'));

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
		$this->init();
		$response = $this->duplicate();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hyperv-ds-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'],$this->response->html->request()->get('vhdx'), $this->response->html->request()->get('volgroup'), $this->appliance->name), 'label');
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
			$name        = $form->get_request('name');
			$datastore   = $this->response->html->request()->get('volgroup');
			$this->pool->get_instance_by_id($datastore);
			$origin_image = $this->response->html->request()->get('vhdx');
			$image = new image();
			$image->get_instance_by_name($origin_image);
			$image_root_device_arr = explode('%', $image->rootdevice);
			$image_pool_id = $image_root_device_arr[0];
			$image_path = str_replace(" ", "@", $image_root_device_arr[1]);
			$image_clone_path = dirname($image_path);
			$image_file_name = basename($image_path);

			$command     = $this->htvcenter->get('basedir').'/plugins/hyperv/bin/htvcenter-hyperv-datastore clone';
			$command    .= ' -i '.$this->resource->ip;
			$command    .= ' -l '.$image_clone_path;
			$command    .= ' -n '.str_replace(".vhdx", "", $image_file_name);
			$command    .= ' -c '.$name;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode fork';
			$check_image = new image();
			$check_image->get_instance_by_name($name);
			if (strlen($check_image->id)) {
				$error = sprintf($this->lang['error_exists'], $name);
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				// send command
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, $this->clone_timeout, true);

				// find storage
				$deployment = new deployment();
				$deployment->get_instance_by_name('hyperv-deployment');
				$storage = new storage();
				$hyperv_id_list = $storage->get_ids_by_storage_type($deployment->id);
				$found_hyperv = false;
				$found_hyperv_id = -1;
				foreach ($hyperv_id_list as $list) {
					foreach ($list as $hyperv_id) {
						$storage->get_instance_by_id($hyperv_id);
						if ($storage->resource_id == $this->appliance->resources) {
							$found_hyperv = true;
							$found_hyperv_id = $storage->id;
							break;
						}
					}
				}
				if ($found_hyperv) {
					$image = new image();
					$image_fields = array();
					$image_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$image_fields["image_id"] = $image_id;
					$image_fields['image_name'] = $name;
					$image_fields['image_type'] = 'hyperv-deployment';
					$image_fields['image_rootfstype'] = 'local';
					$image_fields['image_isactive']=0;
					$image_fields['image_storageid'] = $found_hyperv_id;
					$image_fields['image_comment'] = "Image Object for vhd $name";
					$image_fields['image_rootdevice'] = $datastore.'%'.str_replace("@", " ", $image_clone_path).'/'.$name.'.vhdx';
					$image_fields['image_size']=0;
					$image->add($image_fields);
				}
				$response->msg = sprintf($this->lang['msg_cloned'], $this->response->html->request()->get('vhdx'), $name);
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
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="vhdx" data-length="8"';
		$d['name']['object']['attrib']['value']         = $this->response->html->request()->get('vhdx').'c';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
