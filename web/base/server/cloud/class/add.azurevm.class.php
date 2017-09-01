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

class addazurevm
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
var $message_param = "addazurevm";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'addazurevm';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'addazurevm';
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
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/add-azure-vm.tpl.php');
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
			$azure_vm_name				= trim($form->get_request('azure_vm_name'));
			$azure_vnet_name			= trim($form->get_request('azure_vnet_name'));
			$azure_subnet_name			= trim($form->get_request('azure_subnet_name'));
			$azure_osdisk_name			= trim($form->get_request('azure_osdisk_name'));
			$azure_ipconfig_name		= trim($form->get_request('azure_ipconfig_name'));
			$azure_nic_name				= trim($form->get_request('azure_nic_name'));
			$azure_user_name			= trim($form->get_request('azure_user_name'));
			$azure_password				= trim($form->get_request('azure_password'));
			$azure_resource_group		= trim($form->get_request('azure_resource_group'));

			$command = shell_exec('python '.$this->rootdir.'/server/cloud/script/createazurevm.py '.$azure_vm_name. ' '.$azure_vnet_name.' '.$azure_subnet_name .' '.$azure_osdisk_name.' '.$azure_ipconfig_name.' '.$azure_nic_name.' '.$azure_user_name.' '.$azure_password.' '.$azure_resource_group);
			$azure_create_vm = json_decode($command, true);
			
			foreach($azure_create_vm as $k => $v){
				$data[] = $v;
			}
			
			if(empty($data)) {
				$response->msg = sprintf("Azure VM not created");
			} else {
				$response->msg = sprintf("Azure VM created successfully");
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
		$form = $response->get_form($this->actions_name, 'addazurevm');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Add Azure VM';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		//Resource Group
		//$azure_resource_group_options = array( array(1, 1), array(2, 2), array(3, 3), array(4, 4), array(5, 5), array(6, 6), array(7, 7), array(8, 8), array(9, 9) ); 
		
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
		
		$d['azure_vm_name']['label']                             	= $this->lang['azure_vm_name'];
		$d['azure_vm_name']['required']                          	= true;
		$d['azure_vm_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_vm_name']['object']['attrib']['name']          	= 'azure_vm_name';
		$d['azure_vm_name']['object']['attrib']['id']            	= 'azure_vm_name';
		$d['azure_vm_name']['object']['attrib']['type']          	= 'text';
		$d['azure_vm_name']['object']['attrib']['value']         	= $azure_vm_name;
		$d['azure_vm_name']['object']['attrib']['maxlength']     	= 50;
		//VNET_NAME
		$d['azure_vnet_name']['label']                             	= $this->lang['azure_vnet_name'];
		$d['azure_vnet_name']['required']                          	= true;
		$d['azure_vnet_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_vnet_name']['object']['attrib']['name']          	= 'azure_vnet_name';
		$d['azure_vnet_name']['object']['attrib']['id']            	= 'azure_vnet_name';
		$d['azure_vnet_name']['object']['attrib']['type']          	= 'text';
		$d['azure_vnet_name']['object']['attrib']['value']         	= $azure_vnet_name;
		$d['azure_vnet_name']['object']['attrib']['maxlength']     	= 50;
		//SUBNET_NAME
		$d['azure_subnet_name']['label']                             	= $this->lang['azure_subnet_name'];
		$d['azure_subnet_name']['required']                          	= true;
		$d['azure_subnet_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_subnet_name']['object']['attrib']['name']          	= 'azure_subnet_name';
		$d['azure_subnet_name']['object']['attrib']['id']            	= 'azure_subnet_name';
		$d['azure_subnet_name']['object']['attrib']['type']          	= 'text';
		$d['azure_subnet_name']['object']['attrib']['value']         	= $azure_subnet_name;
		$d['azure_subnet_name']['object']['attrib']['maxlength']     	= 50;
		//OS_DISK_NAME
		$d['azure_osdisk_name']['label']                             	= $this->lang['azure_osdisk_name'];
		$d['azure_osdisk_name']['required']                          	= true;
		$d['azure_osdisk_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_osdisk_name']['object']['attrib']['name']          	= 'azure_osdisk_name';
		$d['azure_osdisk_name']['object']['attrib']['id']            	= 'azure_osdisk_name';
		$d['azure_osdisk_name']['object']['attrib']['type']          	= 'text';
		$d['azure_osdisk_name']['object']['attrib']['value']         	= $azure_osdisk_name;
		$d['azure_osdisk_name']['object']['attrib']['maxlength']     	= 50;
		//IP_CONFIG_NAME
		$d['azure_ipconfig_name']['label']                             	= $this->lang['azure_ipconfig_name'];
		$d['azure_ipconfig_name']['required']                          	= true;
		$d['azure_ipconfig_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_ipconfig_name']['object']['attrib']['name']          	= 'azure_ipconfig_name';
		$d['azure_ipconfig_name']['object']['attrib']['id']            	= 'azure_ipconfig_name';
		$d['azure_ipconfig_name']['object']['attrib']['type']          	= 'text';
		$d['azure_ipconfig_name']['object']['attrib']['value']         	= $azure_ipconfig_name;
		$d['azure_ipconfig_name']['object']['attrib']['maxlength']     	= 50;
		//NIC_NAME
		$d['azure_nic_name']['label']                             	= $this->lang['azure_nic_name'];
		$d['azure_nic_name']['required']                          	= true;
		$d['azure_nic_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_nic_name']['object']['attrib']['name']          	= 'azure_nic_name';
		$d['azure_nic_name']['object']['attrib']['id']            	= 'azure_nic_name';
		$d['azure_nic_name']['object']['attrib']['type']          	= 'text';
		$d['azure_nic_name']['object']['attrib']['value']         	= $azure_nic_name;
		$d['azure_nic_name']['object']['attrib']['maxlength']     	= 50;
		//USERNAME
		$d['azure_user_name']['label']                             	= $this->lang['azure_user_name'];
		$d['azure_user_name']['required']                          	= true;
		$d['azure_user_name']['object']['type']                    	= 'htmlobject_input';
		$d['azure_user_name']['object']['attrib']['name']          	= 'azure_user_name';
		$d['azure_user_name']['object']['attrib']['id']            	= 'azure_user_name';
		$d['azure_user_name']['object']['attrib']['type']          	= 'text';
		$d['azure_user_name']['object']['attrib']['value']         	= $azure_user_name;
		$d['azure_user_name']['object']['attrib']['maxlength']     	= 50;
		//PASSWORD
		$d['azure_password']['label']                             	= $this->lang['azure_password'];
		$d['azure_password']['required']                          	= true;
		$d['azure_password']['object']['type']                    	= 'htmlobject_input';
		$d['azure_password']['object']['attrib']['name']          	= 'azure_password';
		$d['azure_password']['object']['attrib']['id']            	= 'azure_password';
		$d['azure_password']['object']['attrib']['type']          	= 'password';
		$d['azure_password']['object']['attrib']['value']         	= $azure_password;
		$d['azure_password']['object']['attrib']['maxlength']     	= 50;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
