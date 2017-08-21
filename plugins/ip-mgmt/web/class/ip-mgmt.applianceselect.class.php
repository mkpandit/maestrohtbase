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


class ip_mgmt_applianceselect
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
		$table = $this->applianceselect();
		$template = $this->response->html->template($this->tpldir."/ip-mgmt-applianceselect.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['ip_mgmt_appliances'], "appliances");
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
	function applianceselect() {
		// $this->response->html->debug();

		$head['appliance_state']['title'] = ' ';
		$head['appliance_state']['sortable'] = false;
		$head['appliance_icon']['title'] = ' ';
		$head['appliance_icon']['sortable'] = false;
		$head['appliance_id']['title'] = $this->lang['ip_mgmt_id'];
		$head['appliance_name']['title'] = $this->lang['ip_mgmt_name'];
		$head['appliance_kernelid']['title'] = $this->lang['ip_mgmt_kernel'];
		$head['appliance_imageid']['title'] = $this->lang['ip_mgmt_image'];
		$head['appliance_resources']['title'] = $this->lang['ip_mgmt_resource'];
		$head['appliance_virtualization']['title'] = $this->lang['ip_mgmt_type'];

		$head['edit']['title'] = '&#160;';
		$head['edit']['sortable'] = false;


		$appliance_tmp = new appliance();

		$table = $this->response->html->tablebuilder( 'ipmgmt_appliance', $this->response->get_array($this->actions_name, 'applianceselect'));
		$table->css         = 'htmlobject_table';
		$table->border      = 0;
		$table->id          = 'Tabelle1';
		$table->head        = $head;
		$table->sort        = 'appliance_id';
		$table->autosort    = false;
		$table->limit       = 10;
		$table->sort_link   = false;
		$table->form_action = $this->response->html->thisfile;
		$table->max         = $appliance_tmp->get_count();
		$table->init();

		$arBody = array();
		$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

		if(count($appliance_array) > 0) {
			
			foreach ($appliance_array as $index => $appliance_db) {
				$appliance = new appliance();
				$appliance->get_instance_by_id($appliance_db["appliance_id"]);
				$resource = new resource();
				$appliance_resources=$appliance_db["appliance_resources"];
				if ($appliance_resources >=0) {
					// an appliance with a pre-selected resource
					$resource->get_instance_by_id($appliance_resources);
					$appliance_resources_str = "$resource->id/$resource->ip";
				} else {
					// an appliance with resource auto-select enabled
					$appliance_resources_str = "auto-select";
				}

				// active or inactive
				$resource_icon_default='<i class="fa fa-globe fabelle"></i>';
				$active_state_icon='<i class="fa fa-long-arrow-right fabelle"></i>';
				$inactive_state_icon='<i class="fa fa-close fabelle"></i>';
				if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
					$state_icon=$active_state_icon;
				} else {
					$state_icon=$inactive_state_icon;
				}

				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
				$image = new image();
				$image->get_instance_by_id($appliance_db["appliance_imageid"]);
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
				$appliance_virtualization_type=$virtualization->name;

				// ha or not ?
				if ($appliance_db["appliance_highavailable"] == 1) {
					$ha_icon = $active_state_icon;
				} else {
					$ha_icon = $inactive_state_icon;
				}
				$ha_img = "<img src=$ha_icon>";

				// configure link
				$href_configure = $this->response->html->a();
				$href_configure->label = $this->lang['ip_mgmt_configure'];
				$href_configure->href = $this->response->get_url($this->actions_name, "configure").'&'.$this->identifier_name.'[]='.$appliance->id;
				$href_configure->css = 'edit';		
				$href_configure->title = $this->lang['ip_mgmt_configure'];	
				$configure = $href_configure->get_string();

				$arBody[] = array(
					'appliance_state' => $state_icon,
					'appliance_icon' => $resource_icon_default,
					'appliance_id' => $appliance_db["appliance_id"],
					'appliance_name' => $appliance_db["appliance_name"],
					'appliance_kernelid' => $kernel->name,
					'appliance_imageid' => $image->name,
					'appliance_resources' => "$appliance_resources_str",
					'appliance_virtualization' => $appliance_virtualization_type,
					'edit' => $configure,
				);

			}

			$table->body = $arBody;
			$table->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 50, "text" => 50),
			);

		} else {
			$table = $this->response->html->div();
			$table->add('Please create appliances first!');
		}
		return $table;
	}


}

?>


