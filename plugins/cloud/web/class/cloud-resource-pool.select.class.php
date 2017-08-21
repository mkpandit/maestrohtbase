<?php
/**
 * Cloud Resource-Pool Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_resource_pool_select
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-resource-poolselect';



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
		$this->rootdir  = $this->htvcenter->get('rootdir');
		$this->webdir  = $this->htvcenter->get('webdir');
		require_once $this->webdir."/plugins/cloud/class/cloudusergroup.class.php";
		$this->cloud_user_group = new cloudusergroup();
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudrespool.class.php";
		$this->cloudrespool = new cloudrespool();
		$this->appliance = new appliance();
		$this->virtualization = new virtualization();
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
		$template = $this->response->html->template($this->tpldir."/cloud-resource-pool-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($response->table, 'table');
		$template->add($this->lang['cloud_resource_pool_management'], 'title');
		$template->add($response->form);		
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Cloud Resource-Pool Select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'select');
		$response->form = $form;

		$head['appliance_icon']['title'] = ' ';
		$head['appliance_icon']['sortable'] = false;
		$head['appliance_id']['title'] = $this->lang['cloud_resource_pool_id'];
		$head['appliance_name']['title'] = $this->lang['cloud_resource_pool_name'];
		$head['appliance_virtualization']['title'] = $this->lang['cloud_resource_pool_type'];
		$head['appliance_comment']['title'] = $this->lang['cloud_resource_pool_comment'];
		$head['appliance_assigned']['title'] = $this->lang['cloud_resource_pool_assigned'];
		$head['appliance_assigned']['sortable'] = false;
		$head['appliance_actions']['title'] = ' ';
		$head['appliance_actions']['sortable'] = false;

		$table = $response->html->tablebuilder( 'cloud_resource_pool_table', $this->response->get_array($this->actions_name, 'select'));
		$table->offset = 0;
		$table->limit  = 10;
		$table->order  = 'ASC';
		$table->max    = $this->appliance->get_count();
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'cloud_resource_pool_table';
		$table->head            = $head;
		$table->sort            = 'appliance_id';
		$table->autosort        = false;
		$table->form_action	= $this->response->html->thisfile;
		$table->sort_link       = false;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$appliance_icon = "<img width='24' height='24' src='/htvcenter/base/img/appliance.png'>";
		$cloud_resource_pool_array = $this->appliance->display_overview(0, 10000, $table->sort, $table->order);
		$ta = '';
		foreach ($cloud_resource_pool_array as $index => $cz) {
			$this->appliance->get_instance_by_id($cz["appliance_id"]);
			$this->virtualization->get_instance_by_id($this->appliance->virtualization);
			if (!strstr($this->virtualization->type, "-vm")) {
				// update action
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_resource_pool_update'];
				$a->label   = $this->lang['cloud_resource_pool_update'];
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, "update").'&cloud_resource_pool_id='.$cz["appliance_id"];

				if (!strlen($cz["appliance_comment"])) {
					$cz["appliance_comment"] = '-';
				}


				// private image config existing
				$assigned_to = '';
				if ($this->cloudrespool->exists_by_resource_id($this->appliance->resources)) {
					$this->cloudrespool->get_instance_by_resource($this->appliance->resources);
					

					$ide = $this->appliance->resources;
					
					$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
						require_once ($RootDir.'include/htvcenter-database-functions.php');
						$db = htvcenter_get_db_connection();
						$sql = 'SELECT `rp_cg_id` FROM `cloud_respool` WHERE `rp_resource_id` ='.$ide;
						$rez = mysql_query($sql);
						$ids = array();
						while( $arr=mysql_fetch_assoc($rez)) {
							$ids[]= $arr["rp_cg_id"];
						}
						$assigned_to ='';

					foreach ($ids as $id) {
						$this->cloud_user_group->get_instance_by_id($id);
						if ($this->cloudrespool->cg_id > 0) {
							$assigned_to .=' '.$this->cloud_user_group->name;
						} else if ($this->cloudrespool->cg_id == 0) {
							// 0 == all
							$assigned_to .=' '.$this->cloud_user_group->name;
						} else if ($this->cloudrespool->cg_id < 0) {
							$assigned_to .=' '.$this->lang['cloud_resource_pool_nobody'];
						}
					}
				} else {
					$assigned_to = $this->lang['cloud_resource_pool_nobody'];
				}

				$appnamer = '<div class="appnamer panel-heading"><h3 class="panel-title">'.$cz["appliance_name"].'</h3></div>';

				if ($this->virtualization->type == 'kvm') {
					$this->virtualization->type = 'OCH';
				}

				//if ($cz["appliance_id"] != '1') {
					$ta[] = array(
						//'appliance_icon' => $appliance_icon,
						'appliance_id' => '<b>ID: </b>'.$cz["appliance_id"],
						'appliance_name' => $appnamer.'<b>Name: </b>'.$cz["appliance_name"],
						'appliance_virtualization' => '<b>Type: </b>'.$this->virtualization->type,
						'appliance_comment' => '<b>Comment: </b>'.$cz["appliance_comment"],
						'appliance_assigned' => '<b>Assigned to: </b>'.$assigned_to,
						'appliance_actions' => $a->get_string(),
					);
				//}
			}
		}
		$table->max	 = count($ta);
		$table->body = $ta;
		$response->table = $table;
		return $response;
	}




}

?>


