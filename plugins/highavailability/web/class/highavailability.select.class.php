<?php
/**
 * highavailability Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class highavailability_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'highavailability_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'highavailability_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "highavailability_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'highavailability_tab';
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
		$this->user     = $htvcenter->user();
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
		$t = $this->response->html->template($this->tpldir.'/highavailability-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
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

		$h = array();
		$h['appliance_state']['title'] ='&#160;';
		$h['appliance_state']['sortable'] = false;
		$h['appliance_icon']['title'] ='&#160;';
		$h['appliance_icon']['sortable'] = false;
		$h['appliance_id']['title'] = $this->lang['table_id'];
		$h['appliance_name']['title'] = $this->lang['table_name'];
		$h['appliance_values']['title'] = '&#160;';
		$h['appliance_values']['sortable'] = false;
		$h['ha']['title'] ='&#160;';
		$h['ha']['sortable'] = false;
		$h['edit']['title'] ='&#160;';
		$h['edit']['sortable'] = false;

		$appliance = new appliance();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		$table = $this->response->html->tablebuilder('ha', $params);
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->init();

		$appliances = $appliance->display_overview($table->offset, 100000, $table->sort, $table->order);
		foreach ($appliances as $index => $appliance_db) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);

			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);

			// hide htvcenter server and local-server
			if($resource->id != '0' && strpos($resource->capabilities, 'TYPE=local-server') === false) {

				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
				$image = new image();
				$image->get_instance_by_id($appliance_db["appliance_imageid"]);
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);

				$resource_icon_default='<i class="fa fa-globe fatab"></i>';
				$active_state_icon='<i class="fa fa-power-on fatab"></i>';
				$inactive_state_icon='<i class="fa fa-close fatab"></i>';
				if ($appliance->stoptime == 0 || $appliance->resources == 0)  {
					$state_icon=$active_state_icon;
				} else {
					$state_icon=$inactive_state_icon;
				}

				$str = '<b>Kernel:</b> '.$kernel->name.'<br>
						<b>Image:</b> '.$image->name.'<br>
						<b>Resource:</b> '.$resource->id.'<br>
						<b>Type:</b> '.$virtualization->name;

				// highavailable?
				$edit = '&#160;';
				if($appliance->highavailable != '1') {
					$a = $this->response->html->a();
					$a->title   = $this->lang['action_enable_title'];
					$a->label   = $this->lang['action_enable'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'enable';
					$a->href    = $this->response->get_url($this->actions_name, 'enable').'&'.$this->identifier_name.'[]='.$appliance->id;
					$ha         = $a->get_string();
				}			
				else if($appliance->highavailable == '1') {
					$v = $resource->get_resource_capabilities('HA');
					if(!isset($v) || $v === '') {
						$v = 240;
					}				
					$v = $v / 60;
					$a = $this->response->html->a();
					$a->title   = sprintf($this->lang['action_disable_title'], $v);
					$a->label   = $this->lang['action_disable'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'disable';
					$a->href    = $this->response->get_url($this->actions_name, 'disable').'&'.$this->identifier_name.'[]='.$appliance->id;
					$ha         = $a->get_string();

					$a = $this->response->html->a();
					$a->title   = $this->lang['action_edit'];
					$a->label   = $this->lang['action_edit'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'edit';
					$a->href    = $this->response->get_url($this->actions_name, 'edit').'&appliance_id='.$appliance->id;
					$edit       = $a->get_string();
				}

				$b[] = array(
					'appliance_state' => $state_icon,
					'appliance_icon' => $resource_icon_default,
					'appliance_id' => $appliance->id,
					'appliance_name' => $appliance->name,
					'appliance_values' => $str,
					'ha' => $ha,
					'edit' => $edit,
				);
			}

		}

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action	= $this->response->html->thisfile;
		$table->autosort = true;
		$table->sort_link = false;
		$table->max = count($b);
		$table->head = $h;
		$table->body = $b;

		$table->actions_name = $this->actions_name;
		$table->actions = array(array('enable' => $this->lang['action_enable']), array('disable' => $this->lang['action_disable']));
		$table->identifier = 'appliance_id';
		$table->identifier_name = $this->identifier_name;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d = array();
		$d['table']  = $table;
		return $d;
	}

}
?>
