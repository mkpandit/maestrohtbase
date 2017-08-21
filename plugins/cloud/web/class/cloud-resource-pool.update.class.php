<?php
/**
 * Cloud Resource-Pool Update
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/



class cloud_resource_pool_update
{
var $tpldir;
var $lang;
var $actions_name = 'cloud_resource_pool';


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->webdir  = $this->htvcenter->get('webdir');
		$this->rootdir  = $this->htvcenter->get('basedir');
		require_once $this->webdir."/plugins/cloud/class/cloudusergroup.class.php";
		$this->cloud_user_group = new cloudusergroup();
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudrespool.class.php";
		$this->cloudrespool = new cloudrespool();
		$this->appliance = new appliance();
		$this->virtualization = new virtualization();

		// handle response
		$this->response->add('cloud_resource_pool_id', $this->response->html->request()->get('cloud_resource_pool_id'));
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
		$response = $this->update();

		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}

		$cloud_resource_pool_id = $this->response->html->request()->get('cloud_resource_pool_id');
		$cloud_resource_pool_name = '';
		if (strlen($cloud_resource_pool_id)) {
			$this->appliance->get_instance_by_id($cloud_resource_pool_id);
			$cloud_resource_pool_name = $this->appliance->name;
		}
		$template = $response->html->template($this->tpldir."/cloud-resource-pool-update.tpl.php");
		$template->add(sprintf($this->lang['cloud_resource_pool_update_title'], $cloud_resource_pool_name), 'title');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Cloud Resource-Pool Update
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			$data['cloud_resource_pool_id'] = $this->response->html->request()->get('cloud_resource_pool_id');



			// update data
			if(!$form->get_errors()) {
				$this->appliance->get_instance_by_id($data['cloud_resource_pool_id']);

				$assigned_toarr = $data['cloud_resource_pool_assign'];
				$ide = $this->appliance->resources;
				
				$sql = 'SELECT `rp_cg_id` FROM `cloud_respool` WHERE `rp_resource_id` ='.$ide;
				//var_dump($sql);
				$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
				require_once ($RootDir.'include/htvcenter-database-functions.php');
				$db = htvcenter_get_db_connection();
				$sql = 'SELECT `rp_cg_id` FROM `cloud_respool` WHERE `rp_resource_id` ='.$ide;
				$rez = mysql_query($sql);
				$ids = array();
				while( $arr=mysql_fetch_assoc($rez)) {
					$ids[]= $arr["rp_cg_id"];
				}
				
				$newids = array();
				foreach ($assigned_toarr as $newid) {
					$newids[] = $newid;
				}

				$diff = array_diff($ids, $newids);
				//var_dump($diff); die();
				foreach ($diff as $delid) {
					$sql = "DELETE FROM `cloud_respool` WHERE `rp_cg_id` = '".$delid."' AND `rp_resource_id` ='".$ide."'";
					$db->Execute($sql);
				}

				//$dbres = $db->Execute($sql);
				//var_dump($dbres); die();


				foreach ($assigned_toarr as $assigned_to) {

					
					if(in_array($assigned_to, $ids)) {
						continue;
					} else {
					
				/*	if ($this->cloudrespool->exists_by_resource_id($this->appliance->resources)) {
						// remove
						if ($assigned_to == -1) {
							// remove from table
							$this->cloudrespool->get_instance_by_resource($this->appliance->resources);
							$this->cloudrespool->remove($this->cloudrespool->id);
						} else {
							// update
							$this->cloudrespool->get_instance_by_resource($this->appliance->resources);
							$private_cloud_resource_pool_fields["rp_cg_id"] = $assigned_to;
							$this->cloudrespool->update($this->cloudrespool->id, $private_cloud_resource_pool_fields);
						}
					} else {*/
						// new
						$private_cloud_resource_pool_fields["rp_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$private_cloud_resource_pool_fields["rp_resource_id"] = $this->appliance->resources;
						$private_cloud_resource_pool_fields["rp_cg_id"] = $assigned_to;
						
						$this->cloudrespool->add($private_cloud_resource_pool_fields);
					}
				}


			    // success msg
			    $response->msg = sprintf($this->lang['cloud_resource_pool_updated'], $this->appliance->name);

			}
		}

		return $response;
	}


	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$assigned_to = '';
		$assigned_to_default = '';
		$cloud_resource_pool_id = $this->response->html->request()->get('cloud_resource_pool_id');
		if (strlen($cloud_resource_pool_id)) {
			$this->appliance->get_instance_by_id($cloud_resource_pool_id);
			// private image config existing
			if ($this->cloudrespool->exists_by_resource_id($this->appliance->resources)) {
				$this->cloudrespool->get_instance_by_resource($this->appliance->resources);
				if ($this->cloudrespool->cg_id > 0) {
					$this->cloud_user_group->get_instance_by_id($this->cloudrespool->cg_id);
					$assigned_to = $this->cloud_user_group->name;
					$assigned_to_default = $this->cloud_user_group->id;
				} else if ($this->cloudrespool->cg_id == 0) {
					// 0 == all
					$assigned_to_default = 0;
				} else if ($this->cloudrespool->cg_id < 0) {
					$assigned_to_default = -1;
				}
			} else {
				$assigned_to_default = -1;
			}
		}

		$response = $this->response;
		$form = $response->get_form($this->actions_name, "update");

		$cloud_resource_pool_assign_default_arr = array();
		$cloud_resource_pool_assign_default_arr[] = array( 'value' => '-1', 'label' => $this->lang['cloud_resource_pool_nobody']);
		$cloud_resource_pool_assign_arr = $this->cloud_user_group->get_list();
		$cloud_resource_pool_assign_select = array_merge($cloud_resource_pool_assign_arr, $cloud_resource_pool_assign_default_arr);

		$d = array();

		$d['cloud_resource_pool_assign']['label']                          = ' ';
		$d['cloud_resource_pool_assign']['object']['type']                 = 'htmlobject_select';
		$d['cloud_resource_pool_assign']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_resource_pool_assign']['object']['attrib']['id']         = 'cloud_resource_pool_assign';
		$d['cloud_resource_pool_assign']['object']['attrib']['name']       = 'cloud_resource_pool_assign[]';
		$d['cloud_resource_pool_assign']['object']['attrib']['options']    = $cloud_resource_pool_assign_select;
		$d['cloud_resource_pool_assign']['object']['attrib']['selected']    = array($assigned_to_default);
		$d['cloud_resource_pool_assign']['object']['attrib']['size'] = '6';
		
		$hpoptions = '';
		foreach ($cloud_resource_pool_assign_select as $key => $val) {
			$hpoptions .= '<option value="'.$val["value"].'">'.$val["label"].'</option>';
		}

		$d['hpoptions'] = $hpoptions;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
}












?>
