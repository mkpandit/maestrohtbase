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


class puppet_form_controller {
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
			$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';
			$this->response = $response;
			$this->file     = $this->htvcenter->file();
			
			$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/puppet/lang", 'role-administration.ini');

			require_once($this->htvcenter->get('basedir').'/plugins/role-administration/web/class/role-administration.class.php');
			$this->role = new role_administration();
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
			$response = $this->add();
			if(isset($response->msg)) {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
				);
			}
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$data['label'] = $this->lang['label'];
			$vars = array_merge(
				$data, 
				array(
					'thisfile' => $response->html->thisfile,
			));
			$t = $response->html->template($this->tpldir.'/puppet-form.tpl.php');
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
		function add() {
			$response = $this->get_response();
			$form = $response->form;
			if(!$form->get_errors() && $this->response->submit()) {
				$request = $form->get_request();

				// check name
				$check = $this->role->get_role_infos_by_name( $request['role_name'] );
				if(!isset($check)) {
					$request['role_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					if(isset($request['permission_group'])) {
						$fields['role_id'] = $request['role_id'];
						$fields['permission_group'] = $request['permission_group'];
						$error = $this->role->role2group( $fields, 'insert' );
						unset($request['permission_group']);
					}
					$error = $this->role->add_role_infos( $request );
				} else {
					$error = sprintf($this->lang['error_in_use'], $request['role_name']);
					$form->set_error('role_name', $error);
				}
				if(isset($error)) {
					$response->error = $error;
				} else {
					$response->msg = sprintf($this->lang['msg'], $request['role_name']);
				}
			} 
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
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
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$d['name']['label']                    = $this->lang['name'];
			$d['name']['required']                 = true;
			$d['name']['validate']['regex']        = '/^[a-z0-9_-]+$/i';
			$d['name']['validate']['errormsg']     = sprintf($this->lang['error_name'], 'a-z0-9_-');
			$d['name']['object']['type']           = 'htmlobject_input';
			$d['name']['object']['attrib']['type'] = 'text';
			$d['name']['object']['attrib']['placeholder'] = 'Name';
			$d['name']['object']['attrib']['id']   = 'role_name';
			$d['name']['object']['attrib']['name'] = 'role_name';
			$d['name']['object']['attrib']['maxlength'] = 50;

			$groups = $this->role->get_permission_groups();

			$d['group']['label']                        = $this->lang['groups'];
			$d['group']['object']['type']               = 'htmlobject_select';
			$d['group']['object']['attrib']['name']     = 'permission_group[]';
			$d['group']['object']['attrib']['css']      = 'role2groups';
			$d['group']['object']['attrib']['index']    = array('permission_group_id','permission_group_name');
			$d['group']['object']['attrib']['multiple'] = true;
			$d['group']['object']['attrib']['options']  = $groups;
			$d['group']['object']['attrib']['id']       = 'group_select';
			$d['group']['object']['attrib']['title']    = $this->lang['groups_title'];

			$d['comment']['label']                    = $this->lang['comment'];
			$d['comment']['object']['type']           = 'htmlobject_textarea';
			$d['comment']['object']['attrib']['id']   = 'role_comment';
			$d['comment']['object']['attrib']['name'] = 'role_comment';
			$d['comment']['object']['attrib']['maxlength'] = 255;

			$form->add($d);
			$form->display_errors = false;
			$response->form = $form;
			return $response;
		}
}
?>
