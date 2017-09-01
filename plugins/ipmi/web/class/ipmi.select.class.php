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



class ipmi_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ipmi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ipmi_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ipmi_tab';
/**
* id for tabs
* @access public
* @var string
*/
var $identifier_name = 'ipmi_ident';
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
		$response = $this->select();
		$template = $this->response->html->template($this->tpldir.'/ipmi-select.tpl.php');
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['ipmi_title'], "ipmi_title");
		$template->add($response->table, 'table');
		return $template;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		// $this->response->html->help($this->response);

		$arHead['resource_state']['title'] =' ';
		$arHead['resource_state']['sortable'] = false;

		$arHead['resource_icon']['title'] =' ';
		$arHead['resource_icon']['sortable'] = false;

		$arHead['resource_id']['title'] = $this->lang['ipmi_id'];

		$arHead['resource_mac']['title'] = $this->lang['ipmi_mac'];

		$arHead['resource_ip']['title'] = $this->lang['ipmi_ip'];

		$arHead['resource_ipmi_user']['title'] = $this->lang['ipmi_user'];
		$arHead['resource_ipmi_user']['sortable'] = false;

		$arHead['resource_ipmi_pass']['title'] = $this->lang['ipmi_password'];
		$arHead['resource_ipmi_pass']['sortable'] = false;

		$arHead['resource_ipmi_comment']['title'] = $this->lang['ipmi_comment'];
		$arHead['resource_ipmi_comment']['sortable'] = false;

		$arHead['resource_ipmi']['title'] ='IPMI';
		$arHead['resource_ipmi']['sortable'] = false;

		$arHead['resource_action']['title'] = $this->lang['ipmi_actions'];
		$arHead['resource_action']['sortable'] = false;

		$resource_tmp = new resource();

		$table = $this->response->html->tablebuilder( 'ipmi_table', $this->response->get_array($this->actions_name, 'select'));
		#$table->lang            = $this->locale->get_lang( 'tablebuilder.ini' );
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->max             = $resource_tmp->get_count("all");
		$table->id              = 'Tabelle';
		$table->head            = $arHead;
		$table->sort            = 'resource_id';
		$table->autosort        = false;
		$table->sort_link       = false;
		$table->identifier      = 'resource_id';
		$table->identifier_name = $this->identifier_name;
		$table->actions         = array('enable', 'disable', 'update', 'wakeup', 'sleep');
		$table->actions_name    = $this->actions_name;
		$table->form_action		= $this->response->html->thisfile;

		$table->init();

		// here we construct the resource table
		$arBody = array();
		$resource_array = $resource_tmp->display_overview(0, 10000, $table->sort, $table->order);

		if(count($resource_array) > 0) {

			foreach ($resource_array as $index => $resource_db) {
				if($resource_db["resource_id"] === "0") {
					continue;
				}
				$resource_action = "";
				$resource = new resource();
				$resource->get_instance_by_id($resource_db["resource_id"]);
				// state
				$resource_icon_default='<i class="fa fa-globe fabelle"></i>';

				if ($resource->state == 'active') {
					$state_icon = '<i class="fa fa-long-arrow-right fabelle"></i>';
				}
				//$state_icon="/htvcenter/base/img/$resource->state.png";
				if (!strlen($resource->state)) {
					$state_icon="/htvcenter/base/img/transition.png";
				}
				// idle ?
				if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
					$state_icon='<i class="fa fa-close fabelle"></i>';
				}

				# htvcenter special resource
				if ($resource_db["resource_id"] == 0) {
					$resource_mac = "x:x:x:x:x:x";
					$resource_virtualization_type = "htvcenter";
					$resource_icon_default="/htvcenter/base/img/logo.png";
					$resource_action = "";
					$resource_ipmi_ip = "";
					$resource_ipmi_user = "";
					$resource_ipmi_pass = "";
					$resource_ipmi_comment = "";
					$resource_ipmi = "";

				} else {
					$resource_mac = $resource_db["resource_mac"];
					// type
					$virtualization = new virtualization();
					$virtualization->get_instance_by_id($resource->vtype);
					if ($virtualization->id != 1) {
						$pos=strpos($virtualization->name, "Host");
						if ($pos === false) {
							continue;
						}
					}
					$resource_virtualization_type=$virtualization->name;

					// enable/disable
					$ipmi_state = $resource->get_resource_capabilities("SFO");
					if ($ipmi_state == 1) {
						$resource_ipmi = '<a href="'.$this->thisfile.'?plugin=ipmi&ipmi_action=disable&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['ipmi_disable'].'</strong></a>';

						// get info from the resources ipmi object
						$ipmi = new ipmi();
						$ipmi->get_instance_by_resource_id($resource_db["resource_id"]);
						if (strlen($ipmi->resource_ipmi_ip)) {
							$resource_ipmi_ip_db = $ipmi->resource_ipmi_ip;
						} else {
							$resource_ipmi_ip_db = '';
						}
						if (strlen($ipmi->user)) {
							$resource_ipmi_user_db = $ipmi->user;
						} else {
							$resource_ipmi_user_db = '';
						}
						if (strlen($ipmi->pass)) {
							$resource_ipmi_pass_db = $ipmi->pass;
						} else {
							$resource_ipmi_pass_db = '';
						}
						if (strlen($ipmi->comment)) {
							$resource_ipmi_comment_db = $ipmi->comment;
						} else {
							$resource_ipmi_comment_db = '';
						}
						$res_id = $resource_db["resource_id"];
						$resource_ipmi_ip = "<input type='text' size='16' maxlength='16' id='ipmi_input' name=resource_ipmi_ip[$res_id] value=$resource_ipmi_ip_db>";
						$resource_ipmi_user = "<input type='text' size='6' maxlength='20' id='ipmi_input' name=resource_ipmi_user[$res_id] value=$resource_ipmi_user_db>";
						$resource_ipmi_pass = "<input type='password' size='6' maxlength='20' id='ipmi_input' name=resource_ipmi_pass[$res_id] value=$resource_ipmi_pass_db>";
						$resource_ipmi_comment = "<input type='text' size='10' maxlength='100' id='ipmi_input_comment' name=resource_ipmi_comment[$res_id] value=$resource_ipmi_comment_db>";
						// actions
						if ($resource->state == "off") {
							$resource_action = '<a href="'.$this->thisfile.'?plugin=ipmi&ipmi_action=wakeup&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['ipmi_wakeup'].'</strong></a>';
						} else if ($resource->state == "active") {
							$resource_action = '<a href="'.$this->thisfile.'?plugin=ipmi&ipmi_action=sleep&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['ipmi_sleep'].'</strong></a>';
						} else {
								$resource_action = "";
								$state_icon="/htvcenter/base/img/transition.png";
						}

					} else {
						$resource_action = "";
						$res_id = $resource_db["resource_id"];
						$resource_ipmi = '<a href="'.$this->thisfile.'?plugin=ipmi&ipmi_action=enable&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['ipmi_enable'].'</strong></a>';
						$resource_ipmi_ip = "";
						$resource_ipmi_user = "";
						$resource_ipmi_pass = "";
						$resource_ipmi_comment = "";
					}
				}

				$arBody[] = array(
					'resource_state' => $state_icon,
					'resource_icon' => $resource_icon_default,
					'resource_id' => $resource_db["resource_id"],
					'resource_mac' => $resource_mac,
					'resource_ip' => $resource_ipmi_ip,
					'resource_ipmi_user' => $resource_ipmi_user,
					'resource_ipmi_pass' => $resource_ipmi_pass,
					'resource_ipmi_comment' => $resource_ipmi_comment,
					'resource_ipmi' => $resource_ipmi,
					'resource_action' => $resource_action,
				);

			}

			$table->body = $arBody;
			$table->max = count($arBody);

		} else {
			$table = $this->response->html->div();
			$table->add($this->lang['ipmi_add_resources']);
		}
		$response = $this->response;
		$response->table = $table;
		return $response;
	}



}

?>


