<?php

/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/



class ip_mgmt_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ip_mgmt';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ip_mgmt_msg';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'ip_mgmt_id';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ip_mgmt_tab';
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
var $lang;



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
		$this->htvcenter = $htvcenter;
		$this->response = $response;
		$this->thisfile = $this->response->html->thisfile;
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$table = $this->select();

		$add = $this->response->html->a();
		$add->title   = $this->lang['ip_mgmt_add'];
		$add->label   = $this->lang['ip_mgmt_add'];
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "insert");

		$t = $this->response->html->template($this->tpldir."/ip-mgmt-select.tpl.php");
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($table, 'table');
		$t->add($add, 'add');
		$t->add($this->lang['ip_mgmt_manager'], 'label');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select($forapi=false) {
		// $this->response->html->debug();

		$head['name']['title'] = $this->lang['ip_mgmt_name'];
		$head['adresses']['title'] = $this->lang['ip_mgmt_details'];

		$head['details']['title'] = ' &#160;';
		$head['details']['sortable'] = false;

		$head['edit']['title'] = '&#160;';
		$head['edit']['sortable'] = false;

		$table = $this->response->html->tablebuilder( 'ipmgmt_select', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->sort            = 'name';
		$table->autosort        = true;
		$table->sort_link       = false;
		$table->identifier      = 'name';
		$table->identifier_name = $this->identifier_name;
		$table->actions         = array(array('delete' => $this->lang['ip_mgmt_delete']));
		$table->actions_name    = $this->actions_name;
		$table->form_action		= $this->response->html->thisfile;
		if ($forapi == true) {
			require_once('/usr/share/htvcenter/plugins/ip-mgmt/web/class/ip-mgmt.class.php');
			$this->ip_mgmt = new ip_mgmt();
		}
		$body  = $this->ip_mgmt->get_list();
		if($body) {
			foreach ($body as $key => $value) {

				$adress  = $value['first']['ip_mgmt_address'] .' - ';
				$adress .= $value['last']['ip_mgmt_address'] .'<br>';
				// details link
				$href = $this->response->html->a();
				$href->label = $this->lang['ip_mgmt_details'];
				$href->css = 'details';
				$href->title = $this->lang['ip_mgmt_details'];
				$href->href = $this->response->get_url($this->actions_name, "details").'&'.$this->identifier_name.'='.$value['first']['ip_mgmt_name'];
				$details = $href->get_string();

				// updatelink
				$href_update = $this->response->html->a();
				$href_update->css = 'edit';
				$href_update->label = $this->lang['ip_mgmt_update'];
				$href_update->title = $this->lang['ip_mgmt_update'];
				$href_update->href = $this->response->get_url($this->actions_name, "update").'&'.$this->identifier_name.'='.$value['first']['ip_mgmt_name'];
				$update = $href_update->get_string();

				$ta[] = array(
					'name' => $value['first']['ip_mgmt_name'],
					'adresses' => $adress,
					'details' => $details,
					'edit' => $update,
				);

				if ($forapi == true) {
					$networks['names'][] = $value['first']['ip_mgmt_name'];
				}
			}
			$table->body = $ta;
			$table->max = count($body);

		} else {
			$table = $this->response->html->div();
			$table->add('No Ip Groups setup yet');
		}
		
		if ($forapi == true) {
			return $networks;
		} else {
			return $table;
		}
	}





}

?>


