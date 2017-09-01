<?php
/**
 * Lists discovered ESX Hosts
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_discovery_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_discovery_id';
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
		
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-discovery-select.tpl.php');
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

		$head['vmw_esx_ad_state']['title'] = "&#160;";
		$head['vmw_esx_ad_state']['sortable'] = false;
		$head['vmw_esx_ad_id']['title'] = $this->lang['table_id'];
		$head['vmw_esx_ad_id']['hidden'] = true;
		$head['vmw_esx_ad_ip']['title'] = $this->lang['table_ip'];
		$head['vmw_esx_ad_ip']['hidden'] = true;
		$head['vmw_esx_ad_mac']['title'] = $this->lang['table_mac'];
		$head['vmw_esx_ad_mac']['hidden'] = true;
		$head['vmw_esx_ad_hostname']['title'] = $this->lang['table_hostname'];
		$head['vmw_esx_ad_hostname']['hidden'] = true;
		$head['vmw_esx_ad_user']['title'] = $this->lang['table_user'];
		$head['vmw_esx_ad_user']['hidden'] = true;
		$head['data']['title'] = '&#160;';
		$head['data']['sortable'] = false;
		$head['vmw_esx_ad_comment']['title'] = $this->lang['table_comment'];
		$head['vmw_esx_ad_comment']['sortable'] = false;
		$head['action']['title'] = "&#160;";
		$head['action']['sortable'] = false;

		$table = $this->response->html->tablebuilder('vmware_discovery', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabellerr';
		$table->head            = $head;
		$table->sort            = 'vmw_esx_ad_id';
		$table->sort_link       = false;
		$table->autosort        = true;
		$table->max             = $this->discovery->get_count();
		$table->form_action	= $this->response->html->thisfile;
		$table->init();

		$b = array();
		$vmware_esx_discovery_array = $this->discovery->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$add_button = '';
		foreach ($vmware_esx_discovery_array as $index => $esx) {
			if (($esx["vmw_esx_ad_ip"] == '') && ($esx["vmw_esx_ad_mac"] == '')) {
				$this->discovery->remove($esx["vmw_esx_ad_id"]);
				continue;
			}
			
			if ($esx["vmw_esx_ad_is_integrated"] == 0) {

				$esx_state_icon = '<div class="appnamer panel-heading" ><span class="pill active">active</span></div>';
				$a = $this->response->html->a();
				$a->label = $this->lang['action_add'];
				$a->title = $this->lang['action_add'];
				$a->css   = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href  = $this->response->get_url($this->actions_name, "add").'&id='.$esx["vmw_esx_ad_id"];
				$add_button = $a->get_string();
			
			} else {
				$esx_state_icon = '<div class="appnamer panel-heading" ><span class="pill active">active</span></div>';
				
			}

			$a = $this->response->html->a();
			$a->label = $this->lang['action_remove'];
			$a->title = $this->lang['action_remove_title'];
			$a->css   = 'remove';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "remove").'&id='.$esx["vmw_esx_ad_id"];
			$remove_button = $a->get_string();
			
			$esx_password_hidden = '';
			if (strlen($esx["vmw_esx_ad_password"])) {
				$esx_password_hidden = '*********';
			}

			$data  = '<b>'.$this->lang['table_id'].'</b>: '.$esx["vmw_esx_ad_id"].'<br>';
			$data .= '<b>'.$this->lang['table_ip'].'</b>: '.$esx["vmw_esx_ad_ip"].'<br>';
			$data .= '<b>'.$this->lang['table_mac'].'</b>: '. $esx["vmw_esx_ad_mac"].'<br>';
			$data .= '<b>'.$this->lang['table_hostname'].'</b>: '. $esx["vmw_esx_ad_hostname"].'<br>';
			$data .= '<b>'.$this->lang['table_user'].'</b>: '. $esx["vmw_esx_ad_user"].'<br>';
			$data .= '<b>'.$this->lang['table_password'].'</b>: '. $esx_password_hidden.'<br>';

			$b[] = array(
				'vmw_esx_ad_state' => $esx_state_icon,
				'vmw_esx_ad_id' => $esx["vmw_esx_ad_id"],
				'vmw_esx_ad_ip' => $esx["vmw_esx_ad_ip"],
				'vmw_esx_ad_mac' => $esx["vmw_esx_ad_mac"],
				'vmw_esx_ad_hostname' => $esx["vmw_esx_ad_hostname"],
				'vmw_esx_ad_user' => $esx["vmw_esx_ad_user"],
				'vmw_esx_ad_comment' => $esx["vmw_esx_ad_comment"],
				'data' => $data,
				'action' => $add_button.$remove_button,
			);
		}


		$table->body = $b;
		return $table;
	}




}
?>
