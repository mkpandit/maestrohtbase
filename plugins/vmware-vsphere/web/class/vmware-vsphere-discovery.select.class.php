<?php
/**
 * Lists discovered vSphere Hosts
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_discovery_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_discovery_id';
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

		$rescan = $this->response->html->a();
		$rescan->label = $this->lang['action_rescan'];
		$rescan->css   = 'add';
		$rescan->handler = 'onclick="wait();"';
		$rescan->href  = $this->response->get_url($this->actions_name, "rescan");

		$manual = $this->response->html->a();
		$manual->label = $this->lang['action_add_manual'];
		$manual->css   = 'add';
		$manual->handler = 'onclick="wait();"';
		$manual->href  = $this->response->get_url($this->actions_name, "add");
		
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-discovery-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($rescan, 'rescan');
		$t->add($manual, 'manual');
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

		$head['vmw_vsphere_ad_state']['title'] = "&#160;";
		$head['vmw_vsphere_ad_state']['sortable'] = false;
		$head['vmw_vsphere_ad_id']['title'] = $this->lang['table_id'];
		$head['vmw_vsphere_ad_id']['hidden'] = true;
		$head['vmw_vsphere_ad_ip']['title'] = $this->lang['table_ip'];
		$head['vmw_vsphere_ad_ip']['hidden'] = true;
		$head['vmw_vsphere_ad_mac']['title'] = $this->lang['table_mac'];
		$head['vmw_vsphere_ad_mac']['hidden'] = true;
		$head['vmw_vsphere_ad_hostname']['title'] = $this->lang['table_hostname'];
		$head['vmw_vsphere_ad_hostname']['hidden'] = true;
		$head['vmw_vsphere_ad_user']['title'] = $this->lang['table_user'];
		$head['vmw_vsphere_ad_user']['hidden'] = true;
		$head['data']['title'] = '&#160;';
		$head['data']['sortable'] = false;
		$head['vmw_vsphere_ad_comment']['title'] = $this->lang['table_comment'];
		$head['vmw_vsphere_ad_comment']['sortable'] = false;
		$head['action']['title'] = "&#160;";
		$head['action']['sortable'] = false;

		$table = $this->response->html->tablebuilder('vmware_discovery', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->sort            = 'vmw_vsphere_ad_id';
		$table->sort_link       = false;
		$table->autosort        = true;
		$table->max             = $this->discovery->get_count();
		$table->form_action	= $this->response->html->thisfile;
		$table->init();

		$b = array();
		$vmware_vsphere_discovery_array = $this->discovery->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$add_button = '';
		foreach ($vmware_vsphere_discovery_array as $index => $vsphere) {
			if (($vsphere["vmw_vsphere_ad_ip"] == '') && ($vsphere["vmw_vsphere_ad_mac"] == '')) {
				$this->discovery->remove($vsphere["vmw_vsphere_ad_id"]);
				continue;
			}
			
			if ($vsphere["vmw_vsphere_ad_is_integrated"] == 0) {
				$vsphere_state_icon = '<span class="pill inactive">unaligned</span>';
				$a = $this->response->html->a();
				$a->label = $this->lang['action_add'];
				$a->title = $this->lang['action_add'];
				$a->css   = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href  = $this->response->get_url($this->actions_name, "add").'&id='.$vsphere["vmw_vsphere_ad_id"];
				$add_button = $a->get_string();
			
			} else {
				$vsphere_state_icon = '<span class="pill active">active</span>';
			}

			$a = $this->response->html->a();
			$a->label = $this->lang['action_remove'];
			$a->title = $this->lang['action_remove_title'];
			$a->css   = 'remove';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "remove").'&id='.$vsphere["vmw_vsphere_ad_id"];
			$remove_button = $a->get_string();
			
			$vsphere_password_hidden = '';
			if (strlen($vsphere["vmw_vsphere_ad_password"])) {
				$vsphere_password_hidden = '*********';
			}

			$data  = '<b>'.$this->lang['table_id'].'</b>: '.$vsphere["vmw_vsphere_ad_id"].'<br>';
			$data .= '<b>'.$this->lang['table_ip'].'</b>: '.$vsphere["vmw_vsphere_ad_ip"].'<br>';
			$data .= '<b>'.$this->lang['table_mac'].'</b>: '. $vsphere["vmw_vsphere_ad_mac"].'<br>';
			$data .= '<b>'.$this->lang['table_hostname'].'</b>: '. $vsphere["vmw_vsphere_ad_hostname"].'<br>';
			$data .= '<b>'.$this->lang['table_user'].'</b>: '. $vsphere["vmw_vsphere_ad_user"].'<br>';
			$data .= '<b>'.$this->lang['table_password'].'</b>: '. $vsphere_password_hidden.'<br>';

			$b[] = array(
				'vmw_vsphere_ad_state' => $vsphere_state_icon,
				'vmw_vsphere_ad_id' => $vsphere["vmw_vsphere_ad_id"],
				'vmw_vsphere_ad_ip' => $vsphere["vmw_vsphere_ad_ip"],
				'vmw_vsphere_ad_mac' => $vsphere["vmw_vsphere_ad_mac"],
				'vmw_vsphere_ad_hostname' => $vsphere["vmw_vsphere_ad_hostname"],
				'vmw_vsphere_ad_user' => $vsphere["vmw_vsphere_ad_user"],
				'vmw_vsphere_ad_comment' => $vsphere["vmw_vsphere_ad_comment"],
				'data' => $data,
				'action' => $add_button.$remove_button,
			);
		}


		$table->body = $b;
		return $table;
	}




}
?>
