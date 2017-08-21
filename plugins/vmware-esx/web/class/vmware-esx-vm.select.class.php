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

class vmware_esx_vm_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_id';
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
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm-select.tpl.php');
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

		$vmware_esx_array = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($vmware_esx_array as $index => $esx) {
			$esx_appliance_id = $esx["appliance_id"];

			$a = $this->response->html->a();
			$a->title   = 'Edit Virtual Machine';
			$a->label   = '<i class="fa fa-hdd-o icon-lg grayicon"></i>';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'btn btn-default btn-icon btn-hover-primary  icon-lg add-tooltip';
			$a->href    = $this->response->get_url($strControler, "vmware-esx-vm").'&vmware_esx_vm_action=edit&appliance_id='.$esx_appliance_id;
			$links = $a->get_string();

			$a = $this->response->html->a();
			$a->title   = 'Edit Storage';
			$a->label   = '<i class="fa fa-database icon-lg grayicon"></i>';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'btn btn-default btn-icon btn-hover-primary  icon-lg add-tooltip';
			$a->href    = $this->response->get_url($strControler, "vmware-esx-ds").'&vmware_esx_ds_action=edit&appliance_id='.$esx_appliance_id;
			$links .= $a->get_string();

			$a = $this->response->html->a();
			$a->title   = 'Edit Network';
			$a->label   = '<i class="fa fa-globe icon-lg grayicon"></i>';;
			$a->handler = 'onclick="wait();"';
			$a->css     = 'btn btn-default btn-icon btn-hover-primary  icon-lg add-tooltip';
			$a->href    = $this->response->get_url($strControler, "vmware-esx-vs").'&vmware_esx_vs_action=edit&appliance_id='.$esx_appliance_id;
			$links .= $a->get_string();
				$data = '<div class="widget-body text-center"><img src="/htvcenter/base/img/vmware.png" class="widget-img img-circle img-border" alt="Profile Picture">';
					$data .= '<span class="pill active">active</span>';
					$data .= '<div class="text-left">';
					$data .= '<b>Id:</b> '.$esx["appliance_id"].'<br>';
					$data .= '<b>Name:</b> '.$esx["appliance_name"].'<br>';
					$data .= '<b>Comment:</b> '.$esx["appliance_comment"].'<br>';
					$data .= '<div class="pad-ver text-center">
					'.$links.$plugins.'
					</div>';
					$data .='</div></div>';

			$ta[] = array(
				'appliance_icon' => '<div class="widget-header "></div>',
				'appliance_id' => $data,
				//'appliance_name' => $esx["appliance_name"],
				//'appliance_comment' => $esx["appliance_comment"],
				//'appliance_action' => $links,
				
			);
		}
                
		$table->css             = 'htmlobject_table hosterr';
		$table->border          = 0;
		$table->id              = 'Tabellerr';
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
