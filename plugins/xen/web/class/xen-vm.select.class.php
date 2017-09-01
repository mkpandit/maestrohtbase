<?php
/**
 * xen-vm Select Storage
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_vm_select
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
		$t = $this->response->html->template($this->tpldir.'/xen-vm-select.tpl.php');
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
		#$deployment = new deployment();
		#$storage    = new storage();
		$resource   = new resource();
		$virtualization = new virtualization();
		$virtualization->get_instance_by_type("xen");
		$appliance = new appliance();

		$table = $this->response->html->tablebuilder('xen_vm', $this->response->get_array($this->actions_name, 'select'));
		$table->sort      = 'appliance_id';
		$table->limit     = 10;
		$table->offset    = 0;
		$table->order     = 'ASC';
		$table->max       = $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort  = false;
		$table->sort_link = false;
		$table->init();

		$servers = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);

		if(count($servers) >= 1) {
			foreach($servers as $k => $v) {
				$resource->get_instance_by_id($v["appliance_resources"]);
				$resource_icon_default = "/img/resource.png";
				$storage_icon = "/plugins/xen/img/plugin.png";
				$state_icon = $this->htvcenter->get('baseurl')."/img/".$resource->state.".png";
				if ($this->file->exists($this->htvcenter->get('webdir').$storage_icon)) {
					$resource_icon_default = $storage_icon;
				}
				$resource_icon_default = $this->htvcenter->get('baseurl').$resource_icon_default;
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label   = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, "edit").'&appliance_id='.$v['appliance_id'];

				$data  = $resource->id.' / '.$resource->ip;

				switch($resource->state) {
					case 'active':
						$icon = 'fa fa-long-arrow-right fabelle';
					break;
					case 'inactive':
						$icon = 'fa fa-close';
					break;
					default:
						$icon = 'fa fa-globe';
					break;
				}
				$b[] = array(
					'state' => '<i class="'.$icon.'"></i>',
					//'icon' => '<i class="'.$icon.'"></i>',
					'appliance_id' => $v['appliance_id'],
					'appliance_name' => $v['appliance_name'],
					'appliance_resources' => $resource->id,
					'data' => $data,
					'comment' => $v['appliance_comment'],
					'edit' => $a->get_string(),
				);
			}

			$h = array();
			$h['state'] = array();
			$h['state']['title'] ='&#160;';
			$h['state']['sortable'] = false;
			$h['icon'] = array();
			$h['icon']['title'] ='&#160;';
			$h['icon']['sortable'] = false;
			$h['appliance_id'] = array();
			$h['appliance_id']['title'] = $this->lang['table_id'];
			$h['appliance_name'] = array();
			$h['appliance_name']['title'] = $this->lang['table_name'];
			$h['appliance_resources'] = array();
			$h['appliance_resources']['title'] = $this->lang['table_recource'];
			$h['appliance_resources']['hidden'] = true;
			$h['data'] = array();
			$h['data']['title'] = $this->lang['table_recource'];
			$h['data']['sortable'] = false;
			$h['comment'] = array();
			$h['comment']['title'] ='&#160;';
			$h['comment']['sortable'] = false;
			$h['edit'] = array();
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;

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
			$box = $this->response->html->div();
			$box->id = 'htmlobject_box_add_storage';
			$box->css = 'htmlobject_box';
			$box_content  = $this->lang['error_no_host'].'<br><br>';
			$box_content .= '<a href="'.$this->response->html->thisfile.'?base=appliance&appliance_action=step1">'.$this->lang['new'].'</a>';
			$box->add($box_content);
			return $box->get_string();
		}
	}

}
?>
