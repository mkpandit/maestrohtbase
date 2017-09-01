<?php
/**
 * Hyper-V Hosts Pool Manager
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_ds_volgroup
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
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
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
		if($pool_id === '') {
			return false;
		}
		// set ENV
		$virtualization = new virtualization();
		$appliance    = new appliance();
		$resource   = new resource();
		$deployment = new deployment();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource   = $resource;
		$this->appliance    = $appliance;
		$this->virtualization = $virtualization;
		$this->pool_id = $pool_id;
		$this->deployment = $deployment;
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
		$data = $this->ds();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/hyperv-ds-volgroup.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'],$this->response->html->request()->get('volgroup'), $this->appliance->name), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_hyperv'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Pool Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds() {

		if($this->virtualization->type === 'hyperv') {
			
			$hyperv_deployment = $this->deployment->get_instance_by_type('hyperv-deployment');
			
			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['ds_add'] = $a->get_string();
			
			$body = array();
			$identifier_disabled = array();
			$image = new image();
			$image_arr = $image->get_ids();
			foreach($image_arr as $image_list) {
				$image_id = $image_list['image_id'];
				$image->get_instance_by_id($image_id);
				if ($image->id > 2) {
					$image_deployment = new deployment();
					$image_deployment->get_instance_by_type($image->type);
					if ($image_deployment->id == $hyperv_deployment->id) {
						$image_root_device_arr = explode('%', $image->rootdevice);
						$image_pool_id = $image_root_device_arr[0];
						$image_path = $image_root_device_arr[1];
						if ($image_pool_id != $this->pool_id) {
							continue;
						}
						$clone_action_enabled = false;
						if ($image->isactive == 1) {
							$state_icon = '<span class="pill active">active</span>';
							$identifier_disabled[] = $name;
						} else if ($image->isactive == 0) {
							$state_icon = '<span class="pill idle">idle</span>';
							$clone_action_enabled = true;
						}
						$image_description = $image->comment;
						$name = $image->name;
						$state_icon_img = $state_icon;

						$clone_action = '';
						if ($clone_action_enabled) {
							$a_clone = $this->response->html->a();
							$a_clone->label   = $this->lang['action_clone'];
							$a_clone->title   = $this->lang['action_clone'];
							$a_clone->css     = 'clone';
							$a_clone->handler = 'onclick="wait();"';
							$a_clone->href    = $this->response->get_url($this->actions_name, "clone")."&vhdx=".$name;
							$clone_action = $a_clone->get_string();
						}

						$body[] = array(
							'state' => $state_icon_img,
							'name'   => $name,
							'comment' => $image_description,
							'action'  => $clone_action,
						);
					}
				}
			}

			$h['state'] = array();
			$h['state']['title'] = '&#160;';
			$h['state']['sortable'] = false;
			$h['name']['title'] = $this->lang['table_name'];
			$h['comment']['title'] = '&#160;';
			$h['comment']['sortable'] = false;
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;

			$table = $this->response->html->tablebuilder('ds_list', $this->response->get_array($this->actions_name, 'volgroup'));
			$table->sort            = 'name';
			$table->limit           = 20;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action	    = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'name';
			$table->identifier_name = $this->identifier_name;
			$table->actions_name    = $this->actions_name;
			$table->identifier_disabled = $identifier_disabled;
			$table->actions         = array(array('remove' => $this->lang['action_remove']));

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
