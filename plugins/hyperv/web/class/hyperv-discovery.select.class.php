<?php
/**
 * Lists discovered Hyper-V Hosts
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_discovery_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_discovery_id';
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
		
		$t = $this->response->html->template($this->tpldir.'/hyperv-discovery-select.tpl.php');
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

		$head['hyperv_ad_state']['title'] = "&#160;";
		$head['hyperv_ad_state']['sortable'] = false;
		$head['hyperv_ad_id']['title'] = $this->lang['table_id'];
		$head['hyperv_ad_id']['hidden'] = true;
		$head['hyperv_ad_ip']['title'] = $this->lang['table_ip'];
		$head['hyperv_ad_ip']['hidden'] = true;
		$head['hyperv_ad_mac']['title'] = $this->lang['table_mac'];
		$head['hyperv_ad_mac']['hidden'] = true;
		$head['hyperv_ad_hostname']['title'] = $this->lang['table_hostname'];
		$head['hyperv_ad_hostname']['hidden'] = true;
		$head['hyperv_ad_user']['title'] = $this->lang['table_user'];
		$head['hyperv_ad_user']['hidden'] = true;
		$head['data']['title'] = '&#160;';
		$head['data']['sortable'] = false;
		$head['hyperv_ad_comment']['title'] = $this->lang['table_comment'];
		$head['hyperv_ad_comment']['sortable'] = false;
		$head['action']['title'] = "&#160;";
		$head['action']['sortable'] = false;

		$table = $this->response->html->tablebuilder('hyperv_discovery', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->sort            = 'hyperv_ad_id';
		$table->sort_link       = false;
		$table->autosort        = true;
		$table->max             = $this->discovery->get_count();
		$table->form_action	= $this->response->html->thisfile;
		$table->init();

		$b = array();
		$hyperv_discovery_array = $this->discovery->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$add_button = '';
		foreach ($hyperv_discovery_array as $index => $hyperv) {
			if (($hyperv["hyperv_ad_ip"] == '') && ($hyperv["hyperv_ad_mac"] == '')) {
				$this->discovery->remove($hyperv["hyperv_ad_id"]);
				continue;
			}
			
			if ($hyperv["hyperv_ad_is_integrated"] == 0) {
				$hyperv_state_icon = '<span class="pill inactive">unaligned</span>';
				$a = $this->response->html->a();
				$a->label = $this->lang['action_add'];
				$a->title = $this->lang['action_add'];
				$a->css   = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href  = $this->response->get_url($this->actions_name, "add").'&id='.$hyperv["hyperv_ad_id"];
				$add_button = $a->get_string();
			
			} else {
				$hyperv_state_icon = '<span class="pill active">active</span>';
			}

			$a = $this->response->html->a();
			$a->label = $this->lang['action_remove'];
			$a->title = $this->lang['action_remove_title'];
			$a->css   = 'remove';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "remove").'&id='.$hyperv["hyperv_ad_id"];
			$remove_button = $a->get_string();
			
			$hyperv_password_hidden = '';
			if (strlen($hyperv["hyperv_ad_password"])) {
				$hyperv_password_hidden = '*********';
			}

			$data  = '<b>'.$this->lang['table_id'].'</b>: '.$hyperv["hyperv_ad_id"].'<br>';
			$data .= '<b>'.$this->lang['table_ip'].'</b>: '.$hyperv["hyperv_ad_ip"].'<br>';
			$data .= '<b>'.$this->lang['table_mac'].'</b>: '. $hyperv["hyperv_ad_mac"].'<br>';
			$data .= '<b>'.$this->lang['table_hostname'].'</b>: '. $hyperv["hyperv_ad_hostname"].'<br>';
			$data .= '<b>'.$this->lang['table_user'].'</b>: '. $hyperv["hyperv_ad_user"].'<br>';
			$data .= '<b>'.$this->lang['table_password'].'</b>: '. $hyperv_password_hidden.'<br>';

			$b[] = array(
				'hyperv_ad_state' => $hyperv_state_icon,
				'hyperv_ad_id' => $hyperv["hyperv_ad_id"],
				'hyperv_ad_ip' => $hyperv["hyperv_ad_ip"],
				'hyperv_ad_mac' => $hyperv["hyperv_ad_mac"],
				'hyperv_ad_hostname' => $hyperv["hyperv_ad_hostname"],
				'hyperv_ad_user' => $hyperv["hyperv_ad_user"],
				'hyperv_ad_comment' => $hyperv["hyperv_ad_comment"],
				'data' => $data,
				'action' => $add_button.$remove_button,
			);
		}


		$table->body = $b;
		return $table;
	}




}
?>
