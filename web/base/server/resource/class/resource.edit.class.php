<?php
/**
 * Resource Update
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class resource_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'resource_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'resource_identifier';
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
		$this->user	  = $htvcenter->user();
		$this->response->params['resource_id'] = $this->response->html->request()->get('resource_id');

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
		$t = $this->response->html->template($this->tpldir.'/resource-edit.tpl.php');
		$t->add(sprintf($this->lang['label'], $this->response->html->request()->get('resource_id')), 'label');
		$t->add($this->lang['form_docu'], 'form_docu');
		$t->add($this->lang['form_edit_resource'], 'form_edit_resource');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * New
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {
		$response = $this->get_response();
		$form	= $response->form;
		$id		= $this->response->html->request()->get('resource_id');
		$ip		= $form->get_request('ip');
		if (strlen($ip)) {
			if(!$form->get_errors() && $this->response->submit()) {
				$resource = new resource();
				// ip in use already ?
				$resource->get_instance_by_ip($ip);
				if (strlen($resource->id)) {
					$response->error = sprintf($this->lang['msg_ip_in_use']);
					return $response;
				}
				$resource->get_instance_by_id($id);
				$fields["resource_ip"] = $ip;
				$resource->update_info($id, $fields);
				$response->msg = sprintf($this->lang['msg'], $id);
				$response->resource_id = $id;
			} else {
				$response->error = sprintf($this->lang['msg_edit_failed']);
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

		$resource = new resource();
		$resource->get_instance_by_id($this->response->html->request()->get('resource_id'));

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['ip']['label']                         = $this->lang['form_ip'];
		$d['ip']['required']                      = true;
		$d['ip']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['ip']['validate']['errormsg']          = sprintf($this->lang['error_ip'], 'a-z0-9._');
		$d['ip']['object']['type']                = 'htmlobject_input';
		$d['ip']['object']['attrib']['name']      = 'ip';
		$d['ip']['object']['attrib']['type']      = 'text';
		$d['ip']['object']['attrib']['value']     = $resource->ip;
		$d['ip']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
