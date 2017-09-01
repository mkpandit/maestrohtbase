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


#$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
#require_once "$RootDir/class/PHPLIB.php";
#require_once "$RootDir/class/resource.class.php";
#require_once "$RootDir/class/virtualization.class.php";
#require_once "$RootDir/plugins/wakeuponlan/class/wakeuponlan.class.php";


class wakeuponlan_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'wol_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'wol_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'wol_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'wol_identifier';
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
		$table = $this->select();
		$template = $this->response->html->template($this->tpldir.'/wakeuponlan-select.tpl.php');
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['wakeuponlan_title'], "wakeuponlan_title");
		$template->add($table, 'table');
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
//		$this->response->html->debug();

		$arHead['resource_state']['title'] =' ';
		$arHead['resource_state']['sortable'] = false;

		$arHead['resource_icon']['title'] =' ';
		$arHead['resource_icon']['sortable'] = false;

		$arHead['resource_id']['title'] = $this->lang['wakeuponlan_id'];

		$arHead['resource_hostname']['title'] = $this->lang['wakeuponlan_name'];

		$arHead['resource_mac']['title'] = $this->lang['wakeuponlan_mac'];

		$arHead['resource_ip']['title'] = $this->lang['wakeuponlan_ip'];

		$arHead['resource_type']['title'] = $this->lang['wakeuponlan_type'];
		$arHead['resource_type']['sortable'] = false;

		$arHead['resource_wakeuponlan']['title'] ='WOL';
		$arHead['resource_wakeuponlan']['sortable'] = false;

		$arHead['resource_action']['title'] = $this->lang['wakeuponlan_actions'];
		$arHead['resource_action']['sortable'] = false;

		// here we construct the resource table
		$resource_tmp = new resource();

		$table = $this->response->html->tablebuilder( 'wakeuponlan', $this->response->get_array($this->actions_name, 'select'));
		$table->max    = $resource_tmp->get_count("all");
		$table->limit  = 10;
		$table->offset = 0;
		$table->sort   = 'resource_id';
		$table->init();

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
				//$state_icon="/htvcenter/base/img/$resource->state.png";

				if ($resource->state == 'active') {
					$state_icon = '<i class="fa fa-long-arrow-right fabelle"></i>';
				}
				if (!strlen($resource->state)) {
					$state_icon="/htvcenter/base/img/transition.png";
				}
				// idle ?
				if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
					$state_icon='<i class="fa fa-close fabelle"></i>';
				}

				$resource_mac = $resource_db["resource_mac"];
				// type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($resource->vtype);

				// only physical systems for WOL
				if ($virtualization->id != 1) {
					$pos=strpos($virtualization->name, "Host");
					if ($pos === false) {
						continue;
					}
				}
				$resource_virtualization_type=$virtualization->name;

				// enable/disable
				$wakeuponlan_state = $resource->get_resource_capabilities("SFO");
				if ($wakeuponlan_state == 1) {
					$resource_wakeuponlan = '<a href="'.$this->thisfile.'?plugin=wakeuponlan&wol_action=disable&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['wakeuponlan_disable'].'</strong></a>';
					// actions
					if ($resource->state == "off") {
						$resource_action = '<a href="'.$this->thisfile.'?plugin=wakeuponlan&wol_action=wakeup&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['wakeuponlan_wakeup'].'</strong></a>';
					} else if ($resource->state == "active") {
						$resource_action = '<a href="'.$this->thisfile.'?plugin=wakeuponlan&wol_action=sleep&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['wakeuponlan_sleep'].'</strong></a>';
					} else {
							$resource_action = "";
							$state_icon="/htvcenter/base/img/transition.png";
					}

				} else {
					$resource_action = "";
					$resource_wakeuponlan = '<a href="'.$this->thisfile.'?plugin=wakeuponlan&wol_action=enable&'.$this->identifier_name.'[]='.$resource_db["resource_id"].'"><strong>'.$this->lang['wakeuponlan_enable'].'</strong></a>';
				}

				$arBody[] = array(
					'resource_state' => $state_icon,
					'resource_icon' => $resource_icon_default,
					'resource_id' => $resource_db["resource_id"],
					'resource_hostname' => $resource_db["resource_hostname"],
					'resource_mac' => $resource_mac,
					'resource_ip' => $resource_db["resource_ip"],
					'resource_type' => $resource_virtualization_type,
					'resource_wakeuponlan' => $resource_wakeuponlan,
					'resource_action' => $resource_action,
				);

			}

			$table->css             = 'htmlobject_table';
			$table->border          = 0;
			$table->id              = 'Tabelle';
			$table->head            = $arHead;
			$table->body            = $arBody;
			$table->max             = count($arBody);
			$table->autosort        = false;
			$table->sort_link       = false;
			$table->identifier      = 'resource_id';
			$table->identifier_name = $this->identifier_name;
			$table->actions         = array('enable', 'disable', 'wakeup', 'sleep');
			$table->actions_name    = $this->actions_name;
			$table->form_action		= $this->response->html->thisfile;

		} else {
			$table = $this->response->html->div();
			$table->add($this->lang['wakeuponlan_add_resources']);
		}
		return $table;
	}



}

?>


