<?php
/**
 * TmpFs-Storage Select Storage
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class tmpfs_storage_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'tmpfs_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "tmpfs_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'tmpfs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'tmpfs_identifier';
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
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/tmpfs-storage-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function select() {
		// set ENV
		$deployment = new deployment();
		$storage    = new storage();
		$resource   = new resource();

		$deployment->get_instance_by_type("tmpfs-deployment");
		$table = $this->response->html->tablebuilder('tmpfs', $this->response->get_array($this->actions_name, 'select'));
		$table->sort      = 'storage_id';
		$table->limit     = 10;
		$table->offset    = 0;
		$table->order     = 'ASC';
		$table->max       = $storage->get_count_per_type($deployment->id);
		$table->autosort  = false;
		$table->sort_link = false;
		$table->init();
		$storages = $storage->display_overview_per_type($deployment->id, $table->offset, $table->limit, $table->sort, $table->order);

		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				$resource_icon_default="/img/resource.png";
				$storage_icon="/plugins/tmpfs-storage/img/plugin.png";
				$state_icon = $this->htvcenter->get('baseurl')."/img/".$resource->state.".png";
				if ($this->file->exists($this->htvcenter->get('webdir').$storage_icon)) {
					$resource_icon_default=$storage_icon;
				}
				$resource_icon_default = $this->htvcenter->get('baseurl').$resource_icon_default;

				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label   = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, "edit").'&storage_id='.$storage->id;

				$data  = '<b>'.$this->lang['table_recource'].':</b> '.$resource->id.' / '.$resource->ip.'<br>';
				$data .= '<b>'.$this->lang['table_type'].':</b> '.$deployment->type.'<br>';
				$data .= '<b>'.$this->lang['table_deployment'].':</b> '.$deployment->storagedescription.'<br>';

				$b[] = array(
					'state' => '<img width="24" height="24" src="'.$state_icon.'" alt="State">',
					'icon' => '<img width="24" height="24" src="'.$resource_icon_default.'" alt="Icon">',
					'storage_id' => $storage->id,
					'storage_name' => $storage->name,
					'storage_resource_id' => $storage->resource_id,
					'storage_data' => $data,
					'storage_comment' => '',
					'storage_edit' => $a->get_string(),
				);
			}

			$h = array();
			$h['state'] = array();
			$h['state']['title'] ='&#160;';
			$h['state']['sortable'] = false;
			$h['icon'] = array();
			$h['icon']['title'] ='&#160;';
			$h['icon']['sortable'] = false;
			$h['storage_id'] = array();
			$h['storage_id']['title'] = $this->lang['table_id'];
			$h['storage_name'] = array();
			$h['storage_name']['title'] = $this->lang['table_name'];
			$h['storage_resource_id'] = array();
			$h['storage_resource_id']['title'] = $this->lang['table_recource'];
			$h['storage_resource_id']['hidden'] = true;
			$h['storage_data'] = array();
			$h['storage_data']['title'] = '&#160;';
			$h['storage_data']['sortable'] = false;
			$h['storage_comment'] = array();
			$h['storage_comment']['title'] ='&#160;';
			$h['storage_comment']['sortable'] = false;
			$h['storage_edit'] = array();
			$h['storage_edit']['title'] = '&#160;';
			$h['storage_edit']['sortable'] = false;

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->form_action	= $this->response->html->thisfile;
			$table->head = $h;
			$table->body = $b;
			$table->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
			);
			return $table->get_string();
		} else {
			$a = $this->response->html->a();
			$a->title   = $this->lang['new_storage'];
			$a->label   = $this->lang['new_storage'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'add';
			$a->href    = $this->response->html->thisfile.'?base=storage&storage_action=add';

			$box = $this->response->html->div();
			$box->id = 'Tabelle';
			$box->css = 'htmlobject_box';
			$content  = $this->lang['error_no_storage'].'<br><br>';
			$content .= $a->get_string();
			$box->add($content);
			return $box->get_string();
		}
	}

}
?>
