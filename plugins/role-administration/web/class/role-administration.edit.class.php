<?php
/**
 * role-administration edit
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class role_administration_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'role_administration_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "role_administration_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'role_administration_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'role_administration_identifier';
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
var $lang;

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
		$this->tpldir   = $this->rootdir.'/plugins/role-administration/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();

		require_once($this->htvcenter->get('basedir').'/plugins/role-administration/web/class/role-administration.class.php');
		$this->role = new role_administration();

		$role_id = $this->response->html->request()->get('role_id');
		$this->response->add('role_id', $role_id);

		$this->role->current = $this->role->get_role_infos_by_id($role_id);

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
		$data['label'] = sprintf($this->lang['label'], $this->role->current['role_name']);
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
		));
		$t = $response->html->template($this->tpldir.'/role-administration-edit.tpl.php');
		$t->add($vars);
		$t->add($response->form);
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
		$response = $this->response;
		if(is_array($this->role->current) && $this->role->current['role_id'] !== '0' && $this->role->current['role_id'] !== '1') {
			$response = $this->get_response();
			$form = $response->form;
			if(!$form->get_errors() && $this->response->submit()) {
				$request = $form->get_request();
				if(isset($request['permission_group'])) {
					$fields['role_id'] = $this->role->current['role_id'];
					$fields['permission_group'] = $request['permission_group'];
					$error = $this->role->role2group( $fields, 'update' );
					unset($request['permission_group']);
				} else {
					$fields['role_id'] = $this->role->current['role_id'];
					$error = $this->role->role2group( $fields, 'delete' );
				}
				if(!isset($request['role_comment'])) {
					$request['role_comment'] = '';
				}
				$error = $this->role->update_role_infos($this->role->current['role_id'], $request );

				if(isset($error)) {
					$response->error = $error;
				} else {
					$response->msg = sprintf($this->lang['msg'], $this->role->current['role_name']);
				}
			} 
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
		} else {
			$response->msg = '';
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

		$groups   = $this->role->get_permission_groups();
		$select   = $this->role->role2group(array('role_id' => $this->role->current['role_id']), 'select');
		$selected = array();
		if(isset($select) && is_array($select)) {
			foreach($select as $v) {
				$selected[] = $v['permission_group_id'];
			}
		}

		$d['group']['label']                        = $this->lang['groups'];
		$d['group']['object']['type']               = 'htmlobject_select';
		$d['group']['object']['attrib']['name']     = 'permission_group[]';
		$d['group']['object']['attrib']['css']      = 'role2groups';
		$d['group']['object']['attrib']['index']    = array('permission_group_id','permission_group_name');
		$d['group']['object']['attrib']['multiple'] = true;
		$d['group']['object']['attrib']['options']  = $groups;
		$d['group']['object']['attrib']['id']       = 'group_select';
		$d['group']['object']['attrib']['title']    = $this->lang['groups_title'];
		$d['group']['object']['attrib']['selected'] = $selected;

		$d['comment']['label']                         = $this->lang['comment'];
		$d['comment']['object']['type']                = 'htmlobject_textarea';
		$d['comment']['object']['attrib']['id']        = 'role_comment';
		$d['comment']['object']['attrib']['name']      = 'role_comment';
		$d['comment']['object']['attrib']['maxlength'] = 255;
		$d['comment']['object']['attrib']['value']     = (isset($this->role->current['role_comment'])) ? $this->role->current['role_comment'] : '';

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
