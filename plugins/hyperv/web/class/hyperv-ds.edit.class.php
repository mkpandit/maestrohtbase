<?php
/**
 * Hyper-V Hosts DataStore Manager
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_ds_edit
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
		// set ENV
		$virtualization = new virtualization();
		$appliance    = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource   = $resource;
		$this->appliance    = $appliance;
		$this->virtualization = $virtualization;
		$this->statfile = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.ds_list';
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
		$data = $this->ds();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/hyperv-ds-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
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
	 * DataStore Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds() {

		if($this->virtualization->type === 'hyperv') {

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_pool'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_pool");
			$d['ds_add_pool'] = $a->get_string();

		
			$head['hyperv_pool_icon']['title'] = " ";
			$head['hyperv_pool_icon']['sortable'] = false;
			$head['hyperv_pool_id']['title'] = $this->lang['table_id'];
			$head['hyperv_pool_name']['title'] = $this->lang['table_name'];
			$head['hyperv_pool_path']['title'] = $this->lang['table_location'];
			$head['hyperv_pool_comment']['title'] = '';
			$head['hyperv_pool_comment']['sortable'] = false;
			$head['hyperv_pool_action']['title'] = " ";
			$head['hyperv_pool_action']['sortable'] = false;

			$table = $this->response->html->tablebuilder('hyperv_ds_edit', $this->response->get_array($this->actions_name, 'select'));
			$table->sort            = 'hyperv_pool_name';
			$table->limit           = 10;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max				= $this->pool->get_count();
			$table->autosort        = false;
			$table->sort_link       = false;
			$table->init();

			$hyperv_array = $this->pool->display_overview($table->offset, $table->limit, $table->sort, $table->order);
			$ta = '';
			foreach ($hyperv_array as $index => $hyperv) {
				$hyperv_pool_id = $hyperv["hyperv_pool_id"];
				$hyperv_state_icon = "<img src=/htvcenter/base/img/active.png>";
				$edit_img = '<img border=0 src="/htvcenter/base/img/edit.png">';
				$actions = '';
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label   = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, "volgroup")."&volgroup=".$hyperv_pool_id;
				$actions .= $a->get_string();

				$l = $this->response->html->a();
				$l->label = $this->lang['action_ds_remove'];
				$l->title = $this->lang['action_ds_remove'];
				$l->css   = 'remove';
				$l->handler = 'onclick="wait();"';
				$l->href  = $this->response->get_url($this->actions_name, 'remove_pool')."&volgroup=".$hyperv_pool_id;

				$actions .= $l->get_string();
				
				$ta[] = array(
					'hyperv_pool_icon' => $hyperv_state_icon,
					'hyperv_pool_id' => $hyperv["hyperv_pool_id"],
					'hyperv_pool_name' => $hyperv["hyperv_pool_name"],
					'hyperv_pool_path' => $hyperv["hyperv_pool_path"],
					'hyperv_pool_comment' => $hyperv["hyperv_pool_comment"],
					'hyperv_pool_action' => $actions
				);
			}

			$table->css             = 'htmlobject_table';
			$table->border          = 0;
			$table->id              = 'Tabelle';
			$table->head            = $head;
			$table->form_action	    = $this->response->html->thisfile;
//			$table->identifier      = 'hyperv_pool_id';
//			$table->identifier_name = $this->identifier_name;
//			$table->actions_name    = $this->actions_name;
//			$table->actions         = array('remove_pool');

			$table->body = $ta;
			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}
			

}
?>
