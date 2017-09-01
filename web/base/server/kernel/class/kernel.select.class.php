<?php
/**
 * Kernel Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kernel_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kernel_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "kernel_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kernel_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kernel_identifier';
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/kernel-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();

		$h = array();
/*
		$h['kernel_icon']['title'] ='&#160;';
		$h['kernel_icon']['sortable'] = false;
*/
		$h['kernel_id']['title'] = $this->lang['table_id'];
		$h['kernel_name']['title'] = $this->lang['table_name'];
		$h['kernel_version']['title'] = $this->lang['table_version'];
		$h['kernel_comment']['title'] = $this->lang['table_comment'];
		$h['kernel_comment']['sortable'] = false;
		$h['edit']['title'] ='&#160;';
		$h['edit']['sortable'] = false;
		$h['default']['title'] ='&#160;';
		$h['default']['sortable'] = false;

		$kernel = new kernel();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		$table = $this->response->html->tablebuilder('kernel', $params);
		$table->offset = 0;
		$table->sort = 'kernel_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max = $kernel->get_count();

		$table->init();

		// handle table params
		$tps = $table->get_params();
		$tp = '';
		foreach($tps['kernel'] as $k => $v) {
			$tp .= '&kernel['.$k.']='.$v;
		}

		$kernel_arr = $kernel->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$kernel_icon = "/htvcenter/base/img/kernel.png";
		foreach ($kernel_arr as $index => $kernel_db) {
			// prepare the values for the array
			$kernel = new kernel();
			$kernel->get_instance_by_id($kernel_db["kernel_id"]);
			$kernel_comment = $kernel_db["kernel_comment"];
			if (!strlen($kernel_comment)) {
				$kernel_comment = "&#160;";
			}
			$default = '';
			// setdefault
			if ($kernel_db["kernel_id"] != 1) {
				if ((strncmp($kernel->name, "resource", 7)) && (!strstr($kernel->capabilities, "local-server"))){
					$a = $this->response->html->a();
					$a->title   = sprintf($this->lang['action_setdefault'], $kernel_db["kernel_id"]);
					$a->handler = 'onclick="wait();"';
					$a->css     = 'default';
					$a->href    = $this->response->get_url($this->actions_name, 'setdefault').'&'.$this->identifier_name.'[]='.$kernel->id.''.$tp;
					$default    = $a->get_string();
				}
			}

			// edit
			$a = $this->response->html->a();
			$a->label   = $this->lang['action_edit'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, 'edit').'&'.$this->identifier_name.'='.$kernel->id.''.$tp;
			$edit = $a->get_string();


			if ( $kernel_comment != '' && strlen($kernel_comment) != 6) {
				$commento = '<b>Comment:</b> '.$kernel_comment;
			} else {
				unset($commento);
			}

			$b[] = array(
			//	'kernel_icon' => "<img width='24' height='24' src='".$kernel_icon."'>",
				'kernel_id' => '<div class="panel-heading">
									<h3 class="panel-title">'.$kernel_db["kernel_name"].'</h3>
									<div class="appliance_identifierz"><input type="checkbox" name="kernel_identifier[]" value="'.$kernel_db["kernel_id"].'" /></div>
								</div>
								<div class="panel-body">
								<b>Id:</b> '.$kernel_db["kernel_id"].'</div>',
				'kernel_name' => '<div class="panel-body"><b>Name:</b> '.$kernel_db["kernel_name"].'</div>',
				'kernel_version' => '<div class="panel-body"><b>Version:</b> '.$kernel_db["kernel_version"].'</div>',
				'kernel_comment' => '<div class="panel-body">'.$commento.'</div>',
				'edit' => '<div class="panel-body">'.$edit.'</div>',
				'default' => '<div class="panel-body">'.$default.'</div>',

			);

	
		}

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "add").''.$tp;
		$table->id = 'Tabellerr';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->max = $kernel->get_count() - 1;
		$table->head = $h;
		$table->body = $b;
		$table->form_action = $this->response->html->thisfile;
		$table->actions_name = $this->actions_name;
		$table->actions = array(array('remove' => $this->lang['action_remove']));
		$table->identifier = 'kernel_id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = array(0);
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['add']    = $add->get_string();
		$d['table']  = $table;
		return $d;
	}

}
?>
