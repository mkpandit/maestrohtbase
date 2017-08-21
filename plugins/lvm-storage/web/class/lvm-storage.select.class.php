<?php
/**
 * lvm-Storage Select Storage
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class lvm_storage_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lvm_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lvm_identifier';
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
		$t = $this->response->html->template($this->tpldir.'/lvm-storage-select.tpl.php');
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

		$type = $this->response->html->request()->get('storage_type');
		if($type === '') {
			$type = 'lvm-lvm-deployment';
		}
		$deployment->get_instance_by_type($type);
		$storages = $storage->display_overview(0, $storage->get_count(), 'storage_id', 'ASC');
		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				if($deployment->storagetype === 'lvm-storage') {
					$resource_icon_default="/img/resource.png";
					$storage_icon="/plugins/lvm-storage/img/plugin.png";
					$state_icon = '<div class="widget-header "></div>';
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


					$data = '<div class="widget-body text-center"><i class="widget-img img-circle img-border fa fa-hdd-o fa-2x iconlogo"></i>';
					$data .= '<span class="pill '.$resource->state.'">'.$resource->state.'</span>';
					$data .= '<div class="text-left">';
					$data .= '<strong>Id:</strong> '.$storage->id.'<br>';
					$data .= '<strong>Name:</strong> '.$storage->name.'<br>';
					$data .= '<strong>'.$this->lang['table_recource'].':</strong> '.$resource->id.' / '.$resource->ip.'<br>';
					$data .= '<strong>'.$this->lang['table_type'].':</strong> '.$deployment->storagetype.'<br>';
					$data .= '<strong>'.$this->lang['table_deployment'].':</strong> '.$deployment->storagedescription.'<br>';
					$data .= '<strong>Description:</strong> '.$deployment->storagedescription.'<br>';
					$data .='<div class="pad-ver text-center diskaside">';
					$data .= '<a href="'.$a->href.'" onclick="wait();" title="" class="btn btn-default btn-icon btn-hover-primary  icon-lg add-tooltip" data-original-title="'.$a->label.'"><i class="fa fa-pencil icon-lg grayicon"></i></a>';
				
					
					
				$data .='</div></div>';

					$b[] = array(
						
						'state' => $state_icon,
						'storage_data' => $data,
					//	'icon' => '<img width="24" height="24" src="'.$resource_icon_default.'" alt="Icon">',
						
					);
				}
			}

			if(isset($b) && is_array($b) && count($b) >= 1) {
				$h = array();
				$h['state'] = array();
				$h['state']['title'] ='&#160;';
				$h['state']['sortable'] = false;
/*
				$h['icon'] = array();
				$h['icon']['title'] ='&#160;';
				$h['icon']['sortable'] = false;
*/
				$h['storage_id'] = array();
				$h['storage_id']['title'] = $this->lang['table_id'];
				$h['name'] = array();
				$h['name']['title'] = $this->lang['table_name'];
				$h['storage_resource_id'] = array();
				$h['storage_resource_id']['title'] = $this->lang['table_recource'];
				$h['storage_resource_id']['hidden'] = true;
				$h['storage_data'] = array();
				$h['storage_data']['title'] = '&#160;';
				$h['storage_data']['sortable'] = false;
				$h['deployment'] = array();
				$h['deployment']['title'] = $this->lang['table_deployment'];
				$h['deployment']['hidden'] = true;
				$h['storage_comment'] = array();
				$h['storage_comment']['title'] ='&#160;';
				$h['storage_comment']['sortable'] = false;
				$h['edit'] = array();
				$h['edit']['title'] = '&#160;';
				$h['edit']['sortable'] = false;

				$table = $this->response->html->tablebuilder('lvm', $this->response->get_array($this->actions_name, 'select'));
				$table->sort      = 'storage_id';
				$table->limit     = 10;
				$table->order     = 'ASC';
				$table->max       = count($b);
				$table->autosort  = false;
				$table->sort_link = false;
				$table->autosort  = true;
				$table->id = 'Tabellerr';
				$table->css = 'htmlobject_table hosterr';
				$table->border = 1;
				$table->cellspacing = 0;
				$table->cellpadding = 3;
				$table->form_action = $this->response->html->thisfile;
				$table->head = $h;
				$table->body = $b;
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
