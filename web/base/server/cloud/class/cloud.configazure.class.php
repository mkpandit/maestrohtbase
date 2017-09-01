<?php
/**
 * Storage Add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class cloud_az_config
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'az_config_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "az_config_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'az_config_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'az_config_identifier';
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
		$this->response   = $response;
		$this->file       = $htvcenter->file();
		$this->htvcenter    = $htvcenter;
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/cloud-configazure.tpl.php');
		$t->add($this->lang['label'], 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['label'], 'form_add');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$subscription_id	= trim($form->get_request('subscription_id'));
			$client_id			= trim($form->get_request('client_id'));
			$secret_key			= trim($form->get_request('secret_key'));
			$tenant_id			= trim($form->get_request('tenant_id'));
			
			$db = htvcenter_get_db_connection();
			$azCredentials = serialize(array('subscription_id' => $subscription_id, 'client_id' => $client_id, 'secret_key' => $secret_key, 'tenant_id' => $tenant_id));
			$dbSql = $db->Execute("UPDATE cloud_credential SET credentials='$azCredentials' WHERE id = 2");
			if($dbSql) {
				$response->msg = sprintf("Azure Credentials Saved successfully.");
			} else {
				$response->msg = sprintf("Azure Credentials did not save.");
			}
			
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'configazure', $this->message_param, $response->msg)
			);
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'configazure');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Save Credentials';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$db = htvcenter_get_db_connection();
		$credData = $db->GetAll("select * from cloud_credential where id = 2");
		foreach($credData as $k => $v){
			$credentials = unserialize($v['credentials']);
		}
		
		$subscription_id = $credentials['subscription_id'];
		$client_id = $credentials['client_id'];
		$secret_key = $credentials['secret_key'];
		$tenant_id = $credentials['tenant_id'];
		
		$d['subscription_id']['label']                             = $this->lang['subscription_id'];
		$d['subscription_id']['required']                          = true;
		$d['subscription_id']['object']['type']                    = 'htmlobject_input';
		$d['subscription_id']['object']['attrib']['name']          = 'subscription_id';
		$d['subscription_id']['object']['attrib']['id']            = 'subscription_id';
		$d['subscription_id']['object']['attrib']['type']          = 'text';
		$d['subscription_id']['object']['attrib']['value']         = $subscription_id;
		$d['subscription_id']['object']['attrib']['maxlength']     = 50;
		
		$d['client_id']['label']                             = $this->lang['client_id'];
		$d['client_id']['required']                          = true;
		$d['client_id']['object']['type']                    = 'htmlobject_input';
		$d['client_id']['object']['attrib']['name']          = 'client_id';
		$d['client_id']['object']['attrib']['id']            = 'client_id';
		$d['client_id']['object']['attrib']['type']          = 'text';
		$d['client_id']['object']['attrib']['value']         = $client_id;
		$d['client_id']['object']['attrib']['maxlength']     = 100;
		
		$d['secret_key']['label']                             = $this->lang['secret_key'];
		$d['secret_key']['required']                          = true;
		$d['secret_key']['object']['type']                    = 'htmlobject_input';
		$d['secret_key']['object']['attrib']['name']          = 'secret_key';
		$d['secret_key']['object']['attrib']['id']            = 'secret_key';
		$d['secret_key']['object']['attrib']['type']          = 'text';
		$d['secret_key']['object']['attrib']['value']         = $secret_key;
		$d['secret_key']['object']['attrib']['maxlength']     = 100;
		
		$d['tenant_id']['label']                             = $this->lang['tenant_id'];
		$d['tenant_id']['required']                          = true;
		$d['tenant_id']['object']['type']                    = 'htmlobject_input';
		$d['tenant_id']['object']['attrib']['name']          = 'tenant_id';
		$d['tenant_id']['object']['attrib']['id']            = 'tenant_id';
		$d['tenant_id']['object']['attrib']['type']          = 'text';
		$d['tenant_id']['object']['attrib']['value']         = $tenant_id;
		$d['tenant_id']['object']['attrib']['maxlength']     = 100;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
