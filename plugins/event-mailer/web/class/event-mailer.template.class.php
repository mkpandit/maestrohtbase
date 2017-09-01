<?php
/**
 * event_mailer edit
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class event_mailer_template
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'event_mailer_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "event_mailer_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'event_mailer_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'event_mailer_identifier';
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
		$this->htvcenter  = $htvcenter;
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->tpldir   = $this->rootdir.'/plugins/event-mailer/tpl';
		require_once($this->htvcenter->get('basedir').'/plugins/event-mailer/web/class/event-mailer.class.php');
		$this->mailer = new event_mailer();
		$this->result = $this->mailer->get_template();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$response = $this->edit();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$data['lang_replacements'] = $this->lang['replacements'];
		$data['lang_subject'] = $this->lang['subject'].':';
		$data['lang_body'] = $this->lang['body'].':';
		$data['label'] = $this->lang['label'];
		$data['label'] = $this->lang['label'];
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
		));
		$t = $response->html->template($this->tpldir.'/event-mailer-template.tpl.php');
		$t->add($vars);
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
		$form = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$request = $form->get_request();
			$error = $this->mailer->update_template($request);
			if(isset($error)) {
				$response->error = $error;
			} else {
				$response->msg = $this->lang['msg'];
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [select|insert|edit|account|delete]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'template');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['subject']['label']                         = $this->lang['subject'];
		$d['subject']['required']                      = true;
		$d['subject']['object']['type']                = 'htmlobject_input';
		$d['subject']['object']['attrib']['type']      = 'text';
		$d['subject']['object']['attrib']['name']      = 'event_mailer_subject';
		$d['subject']['object']['attrib']['maxlength'] = 255;
		if(isset($this->result['event_mailer_subject'])) {
			$d['subject']['object']['attrib']['value'] = $this->result['event_mailer_subject'];
		}

		$d['body']['label']                         = $this->lang['body'];
		$d['body']['required']                      = true;
		$d['body']['object']['type']                = 'htmlobject_textarea';
		$d['body']['object']['attrib']['name']      = 'event_mailer_body';
		$d['body']['object']['attrib']['maxlength'] = 512;
		if(isset($this->result['event_mailer_body'])) {
			$d['body']['object']['attrib']['value'] = $this->result['event_mailer_body'];
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
