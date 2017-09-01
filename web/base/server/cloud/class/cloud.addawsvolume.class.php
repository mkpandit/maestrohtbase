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

class addawsvolume{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'addawsvolume';
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
				$this->response->get_url($this->actions_name, 'awsvolumes', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/cloud-add-aws-volume.tpl.php');
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
			$aws_volume_size					= trim($form->get_request('aws_volume_size'));
			$aws_volume_type					= trim($form->get_request('aws_volume_type'));
			
			$command = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsaddvolumes.py '.$aws_volume_size.' '.$aws_volume_type);
			$aws_create_instance = json_decode($command, true);			
			foreach($aws_create_instance as $k => $v){
				$data[] = $v;
			}
			if(empty($data)) {
				$response->msg = sprintf("Volume not created");
			} else {
				$response->msg = sprintf("Volume created successfully");
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
		$form = $response->get_form($this->actions_name, 'addawsvolume');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Add AWS Volume (EBS)';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$disk_size_option = array( array('20 GB', 20), array('50 GB', 50), array('100 GB', 100), array('150 GB', 150), array('200 GB', 200), array('500 GB', 500), array('1000 GB', 1000)); 
		
		$d['aws_volume_size']['label']                            = $this->lang['aws_volume_size'];
		$d['aws_volume_size']['required']                         = true;
		$d['aws_volume_size']['object']['type']                   = 'htmlobject_select';
		$d['aws_volume_size']['object']['attrib']['index']   	   = array(1, 0);
		$d['aws_volume_size']['object']['attrib']['name']         = 'aws_volume_size';
		$d['aws_volume_size']['object']['attrib']['id']           = 'aws_volume_size';
		$d['aws_volume_size']['object']['attrib']['type']         = 'text';
		$d['aws_volume_size']['object']['attrib']['value']        = $azure_disk_size;
		$d['aws_volume_size']['object']['attrib']['options']      = $disk_size_option;
		
		$disk_type_option = array( array('General Purpose SSD', 'gp2'), array('Provisioned IOPS SSD', 'io1'), array('Throughput Optimized HDD', 'st1'), array('Cold HDD', 'sc1')); 
		$d['aws_volume_type']['label']                            = $this->lang['aws_volume_type'];
		$d['aws_volume_type']['required']                         = true;
		$d['aws_volume_type']['object']['type']                   = 'htmlobject_select';
		$d['aws_volume_type']['object']['attrib']['index']   	   = array(1, 0);
		$d['aws_volume_type']['object']['attrib']['name']         = 'aws_volume_type';
		$d['aws_volume_type']['object']['attrib']['id']           = 'aws_volume_type';
		$d['aws_volume_type']['object']['attrib']['type']         = 'text';
		$d['aws_volume_type']['object']['attrib']['value']        = $aws_volume_type;
		$d['aws_volume_type']['object']['attrib']['options']      = $disk_type_option;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
