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



class ip_mgmt_configure
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
	function __construct($htvcenter, $response, $controller) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->thisfile = $this->response->html->thisfile;
		$this->controller = $controller;
		$this->ip_mgmt = $this->controller->ip_mgmt;
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
		$response = $this->configure();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'applianceselect', $this->message_param, $response->msg));
		}

		$template = $response->html->template($this->tpldir."/ip-mgmt-configure.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add(sprintf($this->lang['ip_mgmt_appliances_configuration'], $response->name), "appliances_configuration");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function configure() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			// print_r($data);
			$app_id = $response->html->request()->get($this->identifier_name);

			if(isset($app_id[0])) {
				$id = $app_id[0];
				$ips = $this->ip_mgmt->get_ips($id, true);

				$nics = array();
				foreach($data as $key => $value) {
					if( strpos($key, 'nic_') !== false ) {
						$nics[] = $value;
					}
				}
				foreach($nics as $key => $new) {
					// store old id
					$old = $this->ip_mgmt->get_id_by_appliance($id, $key);
					// check that ip is valid
					if(in_array($new, $ips) || $new === 'none') {
						$tmp_nics = $nics;
						unset($tmp_nics[$key]);
						if(in_array($new, $tmp_nics) &&  $new !== 'none') {
							$form->request_errors['nic_'.$key] = 'IP is double';
						} else {
							if($old !== $new) {
								$fields['ip_mgmt_nic_id']       = NULL;
								$fields['ip_mgmt_appliance_id'] = NULL;
								$this->ip_mgmt->update_ip($old, $fields);
							}
							if($key === 'none') {
								$fields['ip_mgmt_nic_id']       = NULL;
								$fields['ip_mgmt_appliance_id'] = NULL;
								$this->ip_mgmt->update_ip($new, $fields);
							} else {
								$fields['ip_mgmt_nic_id']       = $key;
								$fields['ip_mgmt_appliance_id'] = $id;
								$this->ip_mgmt->update_ip($new, $fields);
							}
						}
					}
				}
			}
			// success msg
			$response->msg = $this->lang['ip_mgmt_appliance_configuration_successful'];
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
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "configure");
		$appliance_id_arr = $this->response->html->request()->get($this->identifier_name);
		$appliance_id = $appliance_id_arr[0];
		$app      = $this->htvcenter->appliance();
		$res      = $this->htvcenter->resource();
		$app_data = $app->get_instance_by_id($appliance_id);
		$res_data = $res->get_instance_by_id($app_data->resources);

		$d = array();

		$d['appliance_id']['label']                        = 'Appliance ID';
		$d['appliance_id']['static']                       = true;
		$d['appliance_id']['object']['type']               = 'htmlobject_input';
		$d['appliance_id']['object']['attrib']['type']     = 'text';
		$d['appliance_id']['object']['attrib']['id']       = 'appliance_id';
		$d['appliance_id']['object']['attrib']['name']     = 'appliance_id';
		$d['appliance_id']['object']['attrib']['disabled'] = true;
		$d['appliance_id']['object']['attrib']['value']    = $appliance_id;

		$d['select_0'] = '';
		$d['select_1'] = '';
		$d['select_2'] = '';
		$d['select_3'] = '';

		$ips = $this->ip_mgmt->get_ips($appliance_id);
		if(count($ips) > 0) {

			$selected = array();
			for( $i = 0; $i < $res_data->nics; $i++) {
				$selected[$i] = $this->ip_mgmt->get_id_by_appliance($appliance_id, $i);
			}

			$ips = array_merge(array(array( 'id' => 'none', 'ip' => 'none')), $ips);

			$form_data['id']       = $appliance_id;
			$form_data['nics']     = $res_data->nics;
			$form_data['ips']      = $ips;
			$form_data['selected'] = $selected;

			if(isset($form_data)) {
				for( $i = 0; $i < $form_data['nics']; $i++) {
					$d['select_'.$i]['label']                       = 'Nic '.$i;
					$d['select_'.$i]['object']['type']              = 'htmlobject_select';
					$d['select_'.$i]['object']['attrib']['index']   = array('id', 'ip');
					$d['select_'.$i]['object']['attrib']['id']      = 'select_'.$i;
					$d['select_'.$i]['object']['attrib']['name']    = 'nic_'.$i;
					$d['select_'.$i]['object']['attrib']['options'] = $form_data['ips'];
					if(isset($form_data['selected'][$i])) {
						$d['select_'.$i]['object']['attrib']['selected'] = array($form_data['selected'][$i]);
					}
				}
			}
		} else {
			$div = $response->html->div();
			$div->name = 'nodata';
			$div->add('No Network found');

			$d['select_0'] = $div;
		}

		$form->add($d);
		$response->form = $form;
		$response->name = $app_data->name;
		return $response;
	}

}
?>
