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

class addawsstorage{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'addawsstorage';
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
				$this->response->get_url($this->actions_name, 'awsdisk', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/storage-add-aws-storage.tpl.php');
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
			$aws_bucket_name					= trim($form->get_request('aws_bucket_name'));
			$command = shell_exec('python '.$this->rootdir.'/server/storage/script/createawsbucket.py '.$aws_bucket_name);
			$aws_create_instance = json_decode($command, true);			
			foreach($aws_create_instance as $k => $v){
				$data[] = $v;
			}
			if(empty($data)) {
				$response->msg = sprintf("Bucket not created");
			} else {
				$response->msg = sprintf("Bucket created successfully");
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
		$form = $response->get_form($this->actions_name, 'addawsstorage');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Add AWS Storage (S3)';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$d['aws_bucket_name']['label']                             	= $this->lang['aws_bucket_name'];
		$d['aws_bucket_name']['required']                          	= true;
		$d['aws_bucket_name']['object']['type']                    	= 'htmlobject_input';
		$d['aws_bucket_name']['object']['attrib']['name']          	= 'aws_bucket_name';
		$d['aws_bucket_name']['object']['attrib']['id']            	= 'aws_bucket_name';
		$d['aws_bucket_name']['object']['attrib']['type']          	= 'text';
		$d['aws_bucket_name']['object']['attrib']['value']         	= $aws_bucket_name;
		$d['aws_bucket_name']['object']['attrib']['maxlength']     	= 50;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
