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

class storage_aws_config
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aws_config_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aws_config_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aws_config_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aws_config_identifier';
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
				$this->response->get_url($this->actions_name, 'configaws', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/storage-configaws.tpl.php');
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
			$aws_access_key_id	= $form->get_request('aws_access_key_id');
			$aws_secret_access_key			= $form->get_request('aws_secret_access_key');
			
			$credential_file_content = "aws_access_key_id:".trim($aws_access_key_id)."\n";
			$credential_file_content .= "aws_secret_access_key:".trim($aws_secret_access_key);
			
			if(file_put_contents($this->rootdir."/server/storage/script/aws.key", $credential_file_content)) {
				$response->msg = sprintf("AWS Credentials Saved successfully.");
			} else {
				$response->msg = sprintf("AWS Credentials did not save.");
			}
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
		$form = $response->get_form($this->actions_name, 'configaws');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Save Credentials';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$credential_file_content 			= file_get_contents($this->rootdir."/server/storage/script/aws.key");
		$temp_content 						= explode("\n", $credential_file_content);
		$aws_access_key_id 					= explode(":", $temp_content[0]); $aws_access_key_id = $aws_access_key_id[1];
		$aws_secret_access_key 				= explode(":", $temp_content[1]); $aws_secret_access_key = $aws_secret_access_key[1];
		
		$d['aws_access_key_id']['label']                             	= $this->lang['aws_access_key_id'];
		$d['aws_access_key_id']['required']                          	= true;
		$d['aws_access_key_id']['object']['type']                    	= 'htmlobject_input';
		$d['aws_access_key_id']['object']['attrib']['name']          	= 'aws_access_key_id';
		$d['aws_access_key_id']['object']['attrib']['id']            	= 'aws_access_key_id';
		$d['aws_access_key_id']['object']['attrib']['type']          	= 'text';
		$d['aws_access_key_id']['object']['attrib']['value']         	= $aws_access_key_id;
		$d['aws_access_key_id']['object']['attrib']['maxlength']     	= 50;
		
		$d['aws_secret_access_key']['label']                            = $this->lang['aws_secret_access_key'];
		$d['aws_secret_access_key']['required']                         = true;
		$d['aws_secret_access_key']['object']['type']                   = 'htmlobject_input';
		$d['aws_secret_access_key']['object']['attrib']['name']         = 'aws_secret_access_key';
		$d['aws_secret_access_key']['object']['attrib']['id']           = 'aws_secret_access_key';
		$d['aws_secret_access_key']['object']['attrib']['type']         = 'text';
		$d['aws_secret_access_key']['object']['attrib']['value']        = $aws_secret_access_key;
		$d['aws_secret_access_key']['object']['attrib']['maxlength']    = 100;
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
