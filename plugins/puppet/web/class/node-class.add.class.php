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


class node_class_add {
	

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
		$response = $this->add();
		
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		
		$t = $response->html->template($this->tpldir.'/node-class-add.tpl.php');
		if($response->class_exists) {
			$t->add($response->class_exists, 'class_exists');
		} else {
			$t->add("No class has been created yet.", 'class_exists');
		}
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($content);
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
	function add() {
		$response = $this->get_response();
		$form     = $response->form;
		$server   = new htvcenter_server();
		
		if(!$form->get_errors() && $this->response->submit()) {
			$name        = strtolower($form->get_request('name'));
			if ( in_array( $name.'.pp', $this->scan_dir($this->class_path) ) ) {
				$error = sprintf('This node already exists', $name);
			}
			
			if(!$form->get_errors()) {
				if(isset($error)) {
					$response->error = $error;
				} else {
					$node_file_content = "class " . $name . " { ";
					$node_file_content .= "\n";
					$node_file_content .= "\n }";
					
					/*$node_file = fopen($this->class_path . "/".$name.".pp","wb");
					fwrite($node_file, $node_file_content);
					fclose($node_file);
					
					chown($this->class_path . "/". $name . ".pp", 'htbase');
					chmod($this->class_path . "/". $name . ".pp", 0777);*/
					
					if(mkdir($this->class_path . "/". $name, 0777, true)) {
						mkdir($this->class_path . "/". $name . "/files", 0777, true);
						if(mkdir($this->class_path . "/". $name . "/manifests", 0777, true)){
							$response->msg = sprintf("modules/" . $name."/manifests directory is added successfully.", $name);
							$init_file = fopen($this->class_path . "/".$name."/"."manifests/init.pp","wb");
							fwrite($init_file, $node_file_content);
							fclose($init_file);
							chmod($this->class_path . "/".$name."/"."manifests/init.pp", 0777);
						}
						$response->msg = sprintf("modules/" . $name." directory is added successfully.", $name);
					}
					
					$response->class_exists = $name . " is added successfully.";
					$response->msg = sprintf($name." is added successfully.", $name);
					
					/******** command execution on server ********/
					$cls_rsync_command = $server->send_command("rsync -r ".$this->class_path."/".$name.".pp /etc/puppet/modules/");
					$cls_dir_rsync_command = $server->send_command("rsync -r ".$this->class_path."/".$name." /etc/puppet/modules/");
					
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
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Create class';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	

		$d['name']['label']                             = "Class Name"; //$this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['size']          = 50;
		$d['name']['object']['attrib']['value']         = '';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
	function scan_dir($path) {
		$node_list = array_diff(scandir($path), array('..', '.'));
		return $node_list;
	}
	
}
?>
