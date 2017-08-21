<?php
/**
 * Select vSphere Hosts to manage
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vm_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_id';
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
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vm-select.tpl.php');
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
		$virtualization->get_instance_by_type('vmware-vsphere');
		$appliance = new appliance();

		$head['appliance_icon']['title'] = " ";
		$head['appliance_icon']['sortable'] = false;
		$head['appliance_id']['title'] = $this->lang['table_id'];
		$head['appliance_name']['title'] = $this->lang['table_name'];
		$head['appliance_comment']['title'] = $this->lang['table_comment'];
		$head['appliance_comment']['sortable'] = false;
		$head['appliance_action']['title'] = " ";
		$head['appliance_action']['sortable'] = false;

		$table = $this->response->html->tablebuilder('vmware_vm_select', $this->response->get_array($this->actions_name, 'select'));
		$table->sort            = 'appliance_id';
		$table->limit           = 10;
		$table->offset          = 0;
		$table->order           = 'ASC';
		$table->max		= $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort        = false;
		$table->sort_link       = false;
		$table->init();

		// handle tab in tab
		if($this->response->html->request()->get('iplugin') !== '') {
			$strControler = 'icontroller';
		}
		else if($this->response->html->request()->get('rplugin') !== '') {
			$strControler = 'rcontroller';
		}
		else if($this->response->html->request()->get('aplugin') !== '') {
			$strControler = 'acontroller';
		} else {
			$strControler = 'controller';
		}

		$vmware_vsphere_array = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($vmware_vsphere_array as $index => $vsphere) {
			$vsphere_appliance_id = $vsphere["appliance_id"];

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'Datacenter';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-vsphere-dc").'&vmware_vsphere_ds_action=edit&appliance_id='.$vsphere_appliance_id;
			$links = $a->get_string();
			
			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'ESX Host';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-vsphere-host").'&vmware_vsphere_host_action=select&appliance_id='.$vsphere_appliance_id;
			$links .= $a->get_string();

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'VMs';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-vsphere-vm").'&vmware_vsphere_vm_action=edit&appliance_id='.$vsphere_appliance_id;
			$links .= $a->get_string();

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'ResourcePool';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-vsphere-rp").'&vmware_vsphere_rp_action=edit&appliance_id='.$vsphere_appliance_id;
			$links .= $a->get_string();
			
			$ta[] = array(
				'appliance_icon' => '<span class="pill active">active</span>',
				'appliance_id' => $vsphere["appliance_id"],
				'appliance_name' => $vsphere["appliance_name"],
				'appliance_comment' => $vsphere["appliance_comment"],
				'appliance_action' => $links,
			);
		}
                
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->form_action	    = $this->response->html->thisfile;
		#$table->identifier      = 'appliance_id';
		#$table->identifier_name = 'appliance_id';
		#$table->actions_name    = $this->actions_name;
		#$table->actions         = array(array('remove' => $this->lang['action_host_reboot']), array('$this->lang['action_host_shutdown']);

		$table->body = $ta;
		return $table->get_string();
	}




}
?>
