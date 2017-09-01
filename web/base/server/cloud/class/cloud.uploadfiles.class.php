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

class uploadfiles {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'uploadfiles';
/**
* message param
* @access public
* @var string
*/
var $message_param = "uploadfiles";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'uploadfiles';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'uploadfiles';
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
		$this->rootdir  = $this->htvcenter->get('webdir');
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
				$this->response->get_url($this->actions_name, 'uploadfiles', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/cloud-upload-files.tpl.php');
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
		$data = array();
		if(!$form->get_errors() && $this->response->submit()) {
			$azure_resource_group		= trim($form->get_request('azure_resource_group'));
			$azure_storage_name			= trim($form->get_request('azure_storage_name'));
			$storage_container_name		= trim($form->get_request('storage_container_name'));
			$file_full_path				= trim($form->get_request('file_full_path'));
			
			$command = shell_exec('python '.$this->rootdir.'/server/cloud/script/createazurefiletransfer.py '.$azure_storage_name. ' '.$azure_resource_group.' '. $storage_container_name.' '.$file_full_path);
			$azure_file_upload = json_decode($command, true);
			
			foreach($azure_file_upload as $k => $v){
				$data[] = $v;
			}
			
			if(empty($data)) {
				$response->msg = sprintf("File not uploaded to Azure");
			} else {
				$response->msg = sprintf($data[0]);
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
		$form = $response->get_form($this->actions_name, 'uploadfiles');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Upload Files to Azure Storage';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$rg_command = shell_exec('python '.$this->rootdir.'/server/cloud/script/listazureresourcegroup.py');
		$resource_group = json_decode($rg_command, true);
		
		foreach($resource_group as $k => $v){
			$temp = explode("_", $v);
			$azure_resource_group_options[] =  array($temp[0] . ' - ' . $temp[1], $temp[0]);
		}
		
		$d['azure_resource_group']['label']                            = $this->lang['azure_resource_group'];
		$d['azure_resource_group']['required']                         = true;
		$d['azure_resource_group']['object']['type']                   = 'htmlobject_select';
		$d['azure_resource_group']['object']['attrib']['index']   	   = array(1, 0);
		$d['azure_resource_group']['object']['attrib']['name']         = 'azure_resource_group';
		$d['azure_resource_group']['object']['attrib']['id']           = 'azure_resource_group';
		$d['azure_resource_group']['object']['attrib']['type']         = 'text';
		$d['azure_resource_group']['object']['attrib']['value']        = $azure_resource_group;
		$d['azure_resource_group']['object']['attrib']['options']      = $azure_resource_group_options;
		
		$sn_command = shell_exec('python '.$this->rootdir.'/server/cloud/script/listazurestorage.py');
		$storage_name = json_decode($sn_command, true);
		foreach($storage_name as $k => $v){
			$sn_tmp = explode("_*_", $v);
			$azure_storage_names[] =  array($sn_tmp[0], $sn_tmp[0]);
		}
			
		$d['azure_storage_name']['label']                             	= $this->lang['azure_storage_name'];
		$d['azure_storage_name']['required']                          	= true;
		$d['azure_storage_name']['object']['type']                    	= 'htmlobject_select';
		$d['azure_storage_name']['object']['attrib']['index']   	   	= array(1, 1);
		$d['azure_storage_name']['object']['attrib']['name']          	= 'azure_storage_name';
		$d['azure_storage_name']['object']['attrib']['id']            	= 'azure_storage_name';
		$d['azure_storage_name']['object']['attrib']['type']          	= 'text';
		$d['azure_storage_name']['object']['attrib']['value']         	= $azure_storage_name;
		$d['azure_storage_name']['object']['attrib']['options']      	= $azure_storage_names;
		
		$d['storage_container_name']['label']                             	= $this->lang['storage_container_name'];
		$d['storage_container_name']['required']                          	= true;
		$d['storage_container_name']['object']['type']                    	= 'htmlobject_input';
		$d['storage_container_name']['object']['attrib']['name']          	= 'storage_container_name';
		$d['storage_container_name']['object']['attrib']['id']            	= 'storage_container_name';
		$d['storage_container_name']['object']['attrib']['type']          	= 'text';
		$d['storage_container_name']['object']['attrib']['value']         	= $storage_container_name;
		$d['storage_container_name']['object']['attrib']['maxlength']     	= 50;
		
		$d['file_full_path']['label']                             	= $this->lang['file_full_path'];
		$d['file_full_path']['required']                          	= true;
		$d['file_full_path']['object']['type']                    	= 'htmlobject_input';
		$d['file_full_path']['object']['attrib']['name']          	= 'file_full_path';
		$d['file_full_path']['object']['attrib']['id']            	= 'file_full_path';
		$d['file_full_path']['object']['attrib']['type']          	= 'text';
		$d['file_full_path']['object']['attrib']['value']         	= $file_full_path;
		$d['file_full_path']['object']['attrib']['maxlength']     	= 50;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
