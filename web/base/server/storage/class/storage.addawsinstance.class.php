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

class addawsinstance
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'addawsinstance';
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
				$this->response->get_url($this->actions_name, 'awsinstance', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/storage-add-aws-instance.tpl.php');
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
			$aws_ami_id					= trim($form->get_request('aws_ami_id'));
			$aws_instance_min			= trim($form->get_request('aws_instance_min'));
			$aws_instance_max			= trim($form->get_request('aws_instance_max'));
			$aws_instance_type			= trim($form->get_request('aws_instance_type'));
			//echo 'python '.$this->rootdir.'/server/storage/script/createawsinstance.py '.$aws_ami_id. ' '.$aws_instance_min.' '.$aws_instance_max .' '.$aws_instance_type; die;
			$command = shell_exec('python '.$this->rootdir.'/server/storage/script/createawsinstance.py '.$aws_ami_id. ' '.$aws_instance_min.' '.$aws_instance_max .' '.$aws_instance_type);
			$aws_create_instance = json_decode($command, true);
			
			foreach($aws_create_instance as $k => $v){
				$data[] = $v;
			}
			
			if(empty($data)) {
				$response->msg = sprintf("Instance(s) not created");
			} else {
				$response->msg = sprintf("Instance(s) created successfully");
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
		$form = $response->get_form($this->actions_name, 'addawsinstance');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Add AWS Instances (EC2)';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$d['aws_ami_id']['label']                             	= $this->lang['aws_ami_id'];
		$d['aws_ami_id']['required']                          	= true;
		$d['aws_ami_id']['object']['type']                    	= 'htmlobject_input';
		$d['aws_ami_id']['object']['attrib']['name']          	= 'aws_ami_id';
		$d['aws_ami_id']['object']['attrib']['id']            	= 'aws_ami_id';
		$d['aws_ami_id']['object']['attrib']['type']          	= 'text';
		$d['aws_ami_id']['object']['attrib']['value']         	= $aws_ami_id;
		$d['aws_ami_id']['object']['attrib']['maxlength']     	= 50;
		
		$aws_instance_min_options = array( array(1, 1), array(2, 2), array(3, 3), array(4, 4), array(5, 5), array(6, 6), array(7, 7), array(8, 8), array(9, 9) ); 
		$d['aws_instance_min']['label']                            = $this->lang['aws_instance_min'];
		$d['aws_instance_min']['required']                         = true;
		$d['aws_instance_min']['object']['type']                   = 'htmlobject_select';
		$d['aws_instance_min']['object']['attrib']['index']   	   = array(1, 0);
		$d['aws_instance_min']['object']['attrib']['name']         = 'aws_instance_min';
		$d['aws_instance_min']['object']['attrib']['id']           = 'aws_instance_min';
		$d['aws_instance_min']['object']['attrib']['type']         = 'text';
		$d['aws_instance_min']['object']['attrib']['value']        = $aws_instance_min;
		$d['aws_instance_min']['object']['attrib']['options']      = $aws_instance_min_options;
		
		$aws_instance_max_options = array( array(5, 5), array(1, 1), array(2, 2), array(3, 3), array(4, 4), array(6, 6), array(7, 7), array(8, 8), array(9, 9) ); 
		$d['aws_instance_max']['label']                            = $this->lang['aws_instance_max'];
		$d['aws_instance_max']['required']                         = true;
		$d['aws_instance_max']['object']['type']                   = 'htmlobject_select';
		$d['aws_instance_max']['object']['attrib']['index']   	   = array(1, 0);
		$d['aws_instance_max']['object']['attrib']['name']         = 'aws_instance_max';
		$d['aws_instance_max']['object']['attrib']['id']           = 'aws_instance_max';
		$d['aws_instance_max']['object']['attrib']['type']         = 'text';
		$d['aws_instance_max']['object']['attrib']['value']        = $aws_instance_max;
		$d['aws_instance_max']['object']['attrib']['options']      = $aws_instance_max_options;
		
		$available_instance_types = array( array('t2.nano', 't2.nano'), array('t2.micro', 't2.micro'), array('t2.small', 't2.small'), array('t2.medium', 't2.medium'), array('t2.large', 't2.large'), array('t2.xlarge', 't2.xlarge'), 
		array('t2.2xlarge', 't2.2xlarge'), array('m4.large', 'm4.large'), array('m4.xlarge', 'm4.xlarge') ); 
		$d['aws_instance_type']['label']                            = $this->lang['aws_instance_type'];
		$d['aws_instance_type']['object']['type']                   = 'htmlobject_select';
		$d['aws_instance_type']['object']['attrib']['index']   		= array(1, 0);
		$d['aws_instance_type']['object']['attrib']['name']         = 'aws_instance_type';
		$d['aws_instance_type']['object']['attrib']['id']           = 'aws_instance_type';
		$d['aws_instance_type']['object']['attrib']['type']         = 'text';
		$d['aws_instance_type']['object']['attrib']['value']        = $aws_instance_type;
		$d['aws_instance_type']['object']['attrib']['options']    	= $available_instance_types;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
