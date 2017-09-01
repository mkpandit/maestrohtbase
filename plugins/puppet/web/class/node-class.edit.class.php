<?php
/**
 * Puppet Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


class node_class_edit {
	

/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'node_class_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'node_class_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'node_class_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'node_class_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
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
	function __construct($htvcenter, $response, $controller) {
		$this->controller = $controller;
		$this->response   = $response;
		$this->htvcenter = $htvcenter;
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->file = $this->htvcenter->file();
		$this->user = $htvcenter->user();
		$this->tpldir   = $this->htvcenter->get('basedir').'/plugins/puppet/web/tpl';
		$node_name = $this->response->html->request()->get('node_name');
		$this->response->node_name = $node_name;
		$this->class_path = $this->htvcenter->get('basedir').'/plugins/puppet/web/puppet/modules';
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
		$response = $this->edit();
		
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $response->html->template($this->tpldir.'/node-class-edit.tpl.php');
		
		if($response->action_status) {
			$t->add($response->action_status, 'action_status');
		} else {
			$t->add("Node class ready to be edited", 'action_status');
		}
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
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
	function edit() {
		$response = $this->get_response_edit();
		$form     = $response->form;
		$server   = new htvcenter_server();
		
		if(!$form->get_errors() && $this->response->submit()) {
			$name        = strtolower($form->get_request('name'));
			$content	 = $form->get_request('node_content');
		}
		
		if(!$form->get_errors()) {
			if(isset($error)) {
				$response->error = $error;
			} else {
				chmod($this->class_path . "/" . $name . "/" . "manifests/init.pp", 0777);
				
				if(file_put_contents($this->class_path . "/".$name."/". "manifests/init.pp", $content)) {
					/******** command execution on server ********/
					$cls_rsync_command = $server->send_command("rsync -r ".$this->class_path."/".$name." /etc/puppet/modules/");
					$cls_dir_rsync_command = $server->send_command("rsync -r ".$this->class_path."/".$name." /etc/puppet/modules/");
					
					$response->action_status = $name . " edited successfully.";
					$response->msg = sprintf($name . " edited successfully.", $name);
					$this->response->redirect(
						$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
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
	function get_response_edit() {
		$response = $this->response;
		
		$form = $response->get_form($this->actions_name, 'edit');
		
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Edit class';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                             = "Class"; //$this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['readonly']      = true;
		$d['name']['object']['attrib']['size']          = 50;
		$d['name']['object']['attrib']['maxlength']     = 50;
		$d['name']['object']['attrib']['value']			= $this->response->node_name;
		
		$d['comment']['label']                         	= "Content";
		$d['comment']['required']                      	= true;
		$d['comment']['object']['type']                	= 'htmlobject_textarea';
		$d['comment']['object']['attrib']['id']        	= 'node_content';
		$d['comment']['object']['attrib']['name']      	= 'node_content';
		$d['comment']['object']['attrib']['maxlength'] 	= 255;
		$d['comment']['object']['attrib']['value']     	= $this->get_content(); //""; //(isset($this->role->current['role_comment'])) ? $this->role->current['role_comment'] : '';
		

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
	function get_content(){
		$file_name = $this->class_path . "/" . $this->response->node_name . "/" . "manifests/init.pp";
		$file_content = file_get_contents($file_name, true);
		return $file_content;
	}
	
}
?>
