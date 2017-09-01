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



class ip_mgmt_details
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
		$form = $this->details();
		$t = $this->response->html->template($this->tpldir."/ip-mgmt-details.tpl.php");
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add(sprintf($this->lang['label_details'], $this->response->html->request()->get($this->identifier_name)), 'label');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * details
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function details($netname=false) {
		// $this->response->html->debug();
		$form = $this->response->get_form($this->actions_name, 'details');
		
		if ($netname == false) {
			$name = $this->response->html->request()->get($this->identifier_name);
		} else {
			$name = $netname;
		}
		
		if ($netname != false) {
			require_once('/usr/share/htvcenter/plugins/ip-mgmt/web/class/ip-mgmt.class.php');
			$this->ip_mgmt = new ip_mgmt();
		}
		$data = $this->ip_mgmt->get_list($name);

		$data[$name]['first']['ip_mgmt_network_1'] = $data[$name]['first']['ip_mgmt_address'];
		$data[$name]['first']['ip_mgmt_network_2'] = $data[$name]['last']['ip_mgmt_address'];
		$detailnet['range']['first'] = $data[$name]['first']['ip_mgmt_address'];
		$detailnet['range']['last'] = $data[$name]['last']['ip_mgmt_address'];
		unset($data[$name]['first']['ip_mgmt_address']);

		$detailnet['details'] = $data[$name]['first'];
		foreach($data[$name]['first'] as $key => $value) {
			$box        = $this->response->html->box();
			$box->label = $this->lang[$key];
			$box->add("<b>".$value."</b>");
			$form->add($box, $key);
		}

		// ip list per name
		$ids = $this->ip_mgmt->get_ips_by_name($name);

		$head = array();
		$head['ip_mgmt_id']['title'] = $this->lang['ip_mgmt_id'];
		$head['ip_mgmt_address']['title'] = $this->lang['ip_mgmt_address'];
		$head['ip_mgmt_appliance_id']['title'] = $this->lang['ip_mgmt_appliance_id'];
		$head['ip_mgmt_nic_id']['title'] = $this->lang['ip_mgmt_nic_id'];
		$head['ip_mgmt_user_id']['title'] = $this->lang['ip_mgmt_user_id'];
		$head['ip_mgmt_state']['title'] = $this->lang['ip_mgmt_state'];

		$body = array();
		foreach($ids as $key => $value) {
			$data = $this->ip_mgmt->get_instance('id', $value);
			$state = '&#160;';
			if(isset($data['ip_mgmt_state']) && $data['ip_mgmt_state'] == 1) {
				$sate = '<span class="pill active">active</span>';
			}
			$body[] = array(
				'ip_mgmt_id'           => $data['ip_mgmt_id'],
				'ip_mgmt_address'      => $data['ip_mgmt_address'],
				'ip_mgmt_appliance_id' => (isset($data['ip_mgmt_appliance_id']))? $data['ip_mgmt_appliance_id']: '',
				'ip_mgmt_nic_id'       => (isset($data['ip_mgmt_nic_id']))? $data['ip_mgmt_nic_id'] : '',
				'ip_mgmt_user_id'      => (isset($data['ip_mgmt_user_id']))? $data['ip_mgmt_user_id'] : '',
				'ip_mgmt_state'        => $state,
			);
		}

		$detailnet['ips'] = $body;

		$table = $this->response->html->tablebuilder( 'ipmgmt_details', $this->response->get_array($this->actions_name, 'details'));
		$table->sort      = 'ip_mgmt_id';
		$table->css       = 'htmlobject_table';
		$table->head      = $head;
		$table->body      = $body;
		$table->autosort  = true;
		$table->max       = count($body);
		$table->sort_form = false;

		$input = $this->response->html->input();
		$input->name = 'name';
		$input->value = $name;
		$input->type = 'hidden';

		$form->add($input, 'name');
		$form->add($table, 'table');

		if ($netname == false) {
			return $form;
		} else {
			return $detailnet;
		}
	}


}
?>
