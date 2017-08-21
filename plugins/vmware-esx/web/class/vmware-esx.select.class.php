<?php
/**
 * Select ESX Hosts to manage
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
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
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
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

		$virtualization = new virtualization();
		$virtualization->get_instance_by_type('vmware-esx');
		$appliance = new appliance();
                
		$head['appliance_icon']['title'] = " ";
		$head['appliance_id']['title'] = $this->lang['table_id'];
		$head['appliance_name']['title'] = $this->lang['table_name'];
		$head['appliance_comment']['title'] = $this->lang['table_comment'];
		$head['appliance_action']['title'] = " ";

		$table = $this->response->html->tablebuilder('vmware_select', $this->response->get_array($this->actions_name, 'select'));
		$table->sort            = 'appliance_id';
		$table->limit           = 10;
		$table->offset          = 0;
		$table->order           = 'ASC';
		$table->max		= $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort        = false;
		$table->sort_link       = false;
		$table->init();

		$vmware_esx_array = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($vmware_esx_array as $index => $esx) {
			$esx_appliance_id = $esx["appliance_id"];
			$esx_state_icon = "<img src=/htvcenter/base/img/active.png>";
			$edit_img = '<img border=0 src="/htvcenter/base/img/edit.png">';

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = $this->lang['action_edit'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, "vm").'&appliance_id='.$esx_appliance_id;

			$ta[] = array(
				'appliance_icon' => $esx_state_icon,
				'appliance_id' => $esx["appliance_id"],
				'appliance_name' => $esx["appliance_name"],
				'appliance_comment' => $esx["appliance_comment"],
				'appliance_action' => $a->get_string(),
			);
		}
                
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->form_action	    = $this->response->html->thisfile;
		$table->identifier      = 'appliance_id';
		$table->identifier_name = 'appliance_id';
		$table->actions_name    = $this->actions_name;
		$table->actions         = array($this->lang['action_host_reboot'], $this->lang['action_host_shutdown']);

		$table->body = $ta;
		return $table->get_string();
	}




}
?>
