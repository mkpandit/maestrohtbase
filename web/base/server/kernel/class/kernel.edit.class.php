<?php
/**
 * Edit kernel
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kernel_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kernel_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "kernel_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kernel_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kernel_identifier';
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
		$this->response = $response;
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;

		$kernel_id = $response->html->request()->get($this->identifier_name);
		$kernel = $this->htvcenter->kernel();
		$kernel->get_instance_by_id($kernel_id);
		$this->kernel_id = $kernel_id;
		$this->kernel_name = $kernel->name;
		$this->response->params[$this->identifier_name] = $this->response->html->request()->get($this->identifier_name);

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
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/kernel-edit.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->kernel_name, 'kernel_name');
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {

		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$errors     = array();
			$message    = array();

			$kernel_id		= $this->response->html->request()->get($this->identifier_name);
			$kernel_comment		= $this->response->html->request()->get('kernel_comment');

			$kernel = new kernel();
			$kernel->get_instance_by_id($kernel_id);

			$kernel_comment = $response->html->request()->get('kernel_comment');
			$fields['kernel_comment'] = htmlspecialchars($kernel_comment);
			$kernel->update($kernel_id, $fields);
			$response->msg = sprintf($this->lang['msg'], $kernel->name);
		}
		return $response;

	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$kernel = $this->htvcenter->kernel();
		$kernel->get_instance_by_id($this->kernel_id);

		$d['kernel_comment']['label']                     = $this->lang['comment'];
		$d['kernel_comment']['object']['type']            = 'htmlobject_textarea';
		$d['kernel_comment']['object']['attrib']['name']  = 'kernel_comment';
		$d['kernel_comment']['object']['attrib']['value'] = $kernel->comment;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
