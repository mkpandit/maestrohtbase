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

class event_mailer_edit
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

		$this->user_id = $this->response->html->request()->get('user_id');
		$this->response->add('user_id', $this->user_id);

		$this->result = $this->mailer->get_result_by_user($this->user_id);
		$this->user->current = $this->user->get_instance_by_id($this->user_id);



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
		$user = $this->user->current;
		if($user->id !== $this->user_id || $this->user_id === '0') {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select')
			);
		}
		$response = $this->edit();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$data['label'] = sprintf($this->lang['label'], $this->user->current->name);
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
		));
		$t = $response->html->template($this->tpldir.'/event-mailer-edit.tpl.php');
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
			if(count($request) < 1) {
				if(isset($this->result)) {
					$error = $this->mailer->remove_by_user($this->user_id);
				}
			}
			else if(count($request) >= 1) {
				if(isset($request['user_email'])) {
					$request['event_active']  = (isset($request['event_active']))  ? 1 : 0;
					$request['event_error']   = (isset($request['event_error']))   ? 1 : 0;
					$request['event_warning'] = (isset($request['event_warning'])) ? 1 : 0;
					$request['event_regular'] = (isset($request['event_regular'])) ? 1 : 0;
					$request['event_remove']  = (isset($request['event_remove']))  ? 1 : 0;
					if(isset($this->result)) {
						$error = $this->mailer->update($this->user_id, $request);
					}
					else if(!isset($this->result)) {
						$request['user_id'] = $this->user_id;
						$error = $this->mailer->insert($request);
					}
				}
				else if (!isset($request['user_email'])) {
					$error = $this->lang['error_email'];
					$form->set_error('user_email', '');
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$response->msg = sprintf($this->lang['msg'], $this->user->current->name);
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
		$form = $response->get_form($this->actions_name, 'edit');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['user_email']['label']                    = $this->lang['email'];
		$d['user_email']['object']['type']           = 'htmlobject_input';
		$d['user_email']['object']['attrib']['type'] = 'text';
		$d['user_email']['object']['attrib']['id']   = 'user_email';
		$d['user_email']['object']['attrib']['name'] = 'user_email';
		if(isset($this->result['user_email'])) {
			$d['user_email']['object']['attrib']['value'] = $this->result['user_email'];
		}

		$d['event_active']['label']                    = $this->lang['active'];
		$d['event_active']['object']['type']           = 'htmlobject_input';
		$d['event_active']['object']['attrib']['type'] = 'checkbox';
		$d['event_active']['object']['attrib']['id']   = 'event_active';
		$d['event_active']['object']['attrib']['name'] = 'event_active';
		if(isset($this->result['event_active']) && $this->result['event_active'] === '1') {
			$d['event_active']['object']['attrib']['checked'] = true;
		}

		$d['event_error']['label']                    = $this->lang['error'];
		$d['event_error']['object']['type']           = 'htmlobject_input';
		$d['event_error']['object']['attrib']['type'] = 'checkbox';
		$d['event_error']['object']['attrib']['id']   = 'event_error';
		$d['event_error']['object']['attrib']['name'] = 'event_error';
		if(isset($this->result['event_error']) && $this->result['event_error'] === '1') {
			$d['event_error']['object']['attrib']['checked'] = true;
		}

		$d['event_warning']['label']                    = $this->lang['warning'];
		$d['event_warning']['object']['type']           = 'htmlobject_input';
		$d['event_warning']['object']['attrib']['type'] = 'checkbox';
		$d['event_warning']['object']['attrib']['id']   = 'event_warning';
		$d['event_warning']['object']['attrib']['name'] = 'event_warning';
		if(isset($this->result['event_warning']) && $this->result['event_warning'] === '1') {
			$d['event_warning']['object']['attrib']['checked'] = true;
		}

		$d['event_regular']['label']                    = $this->lang['regular'];
		$d['event_regular']['object']['type']           = 'htmlobject_input';
		$d['event_regular']['object']['attrib']['type'] = 'checkbox';
		$d['event_regular']['object']['attrib']['id']   = 'event_regular';
		$d['event_regular']['object']['attrib']['name'] = 'event_regular';
		if(isset($this->result['event_regular']) && $this->result['event_regular'] === '1') {
			$d['event_regular']['object']['attrib']['checked'] = true;
		}

		$d['event_remove']['label']                    = $this->lang['remove'];
		$d['event_remove']['object']['type']           = 'htmlobject_input';
		$d['event_remove']['object']['attrib']['type'] = 'checkbox';
		$d['event_remove']['object']['attrib']['id']   = 'event_remove';
		$d['event_remove']['object']['attrib']['name'] = 'event_remove';
		if(isset($this->result['event_remove']) && $this->result['event_remove'] === '1') {
			$d['event_remove']['object']['attrib']['checked'] = true;
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
