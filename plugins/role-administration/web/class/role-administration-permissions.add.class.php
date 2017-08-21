<?php
/**
 * role-administration add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class role_administration_permissions_add
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

		require_once($this->htvcenter->get('basedir').'/plugins/role-administration/web/class/permissions.class.php');
		$this->permissions = new permissions($this->htvcenter, $this->response);
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
		$t = $response->html->template($this->tpldir.'/role-administration-permissions-add.tpl.php');
		$t->add($vars);
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('plugin_' => 'plugins'));
		$t->group_elements(array('base_' => 'base'));

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
			$check = $this->role->get_permission_groups_by_name( $request['permission_group_name'] );
			if(!isset($check)) {
				$values['permission_group_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$values['permission_group_name'] = $request['permission_group_name'];
				if(isset($request['permission_group_comment'])) {
					$values['permission_group_comment'] = $request['permission_group_comment'];
				}
				$error = $this->role->add_permission_groups( $values );

				foreach($request as $k => $v) {
					if($k !== 'permission_group_name' && $k !== 'permission_group_comment') {
							$fields['permission_group_id'] = $values['permission_group_id'];
							$fields['permission_controller'] = $k;
							$fields['permission_actions'] = implode(',', array_keys($v));
							$error = $this->role->permissions($fields, 'insert');
					}
				}
			} else {
				$error = sprintf($this->lang['error_in_use'], $request['permission_group_name']);
				$form->set_error('role_name', $error);
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$response->msg = sprintf($this->lang['msg'], $request['permission_group_name']);
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
		$d['name']['object']['attrib']['id']   = 'permission_group_name';
		$d['name']['object']['attrib']['name'] = 'permission_group_name';
		$d['name']['object']['attrib']['maxlength'] = 50;

		$data['base']   = $this->permissions->get_base();
		$data['plugin'] = $this->permissions->get_plugins();

		foreach($data as $key => $content) {
			if(is_array($content) && count($content) > 0) {
				$i = 0;
				foreach($content as $v) {

					$ro = new ReflectionObject($v['object']);

					$outer = $this->response->html->div();
					$outer->name = $v['class'];
					$outer->style = 'margin: 10px 0;';

			/*
					$a = $this->response->html->a();
					$a->label = '-';
					$a->href = '#';
					$a->id = $v['class'].'_a';
					$a->css = 'togglebutton';
					$a->handler = 'onclick="toggle(\''.$v['class'].'\'); return false;"';
			*/
					$docblock = '';
					$doc = $ro->getDocComment();
					$doc = explode("\n", $doc);
					if(isset($doc[1])) {
						$docblock = trim(str_replace('*', '', $doc[1]));
					}

					$name = str_replace('_controller', '', $v['class']);
					$name = str_replace('aa_', '', $name);

					$a_label = $this->response->html->a();
					$a_label->label = $name;
					$a_label->href = '#';
					$a_label->id = $v['class'].'_a_label';
					$a_label->css = 'permission_label';
					$a_label->handler = 'onclick="toggle(\''.$v['class'].'\'); return false;"';

					$div = $this->response->html->div();
					$div->style = 'font-weight: bold; margin: 10px 0;';
					$div->add('<div style="float:left; width: 280px;" title="'.$docblock.'">'.$a_label->get_string().'</div>');
			//		$div->add($a);
					$div->add('<div class="floatbreaker" style="clear:both;line-height:0;">&#160;</div>');

					$outer->add($div);

					$wrapper = $this->response->html->div();
					$wrapper->id = $v['class'];
					$wrapper->css = 'wrapper';
					$wrapper->style = 'display: block;';

					foreach($v['actions'] as $action) {

						$method = $action;
						if($action === 'clone') {
							$method = 'duplicate';
						}

						$docblock = '';
						if($ro->hasMethod($method)) {
							$doc = $ro->getMethod($method)->getDocComment();
							$doc = explode("\n", $doc);
							if(isset($doc[1])) {
								$docblock = trim(str_replace('*', '', $doc[1]));
							}
						}

						$box = $this->response->html->box();
						$box->label = $action;
						$box->css = 'htmlobject_box';
						
						$input = $this->response->html->input();
						$input->type = 'checkbox';
						$input->name = $v['class'].'['.$action.']';
						$input->id = $v['class'].'['.$action.']';
						$input->title = $docblock;

						$box->add($input);
						$wrapper->add($box);
					}
					$wrapper->add('<script type="text/javascript">toggle(\''.$v['class'].'\');</script>');
					$outer->add($wrapper);

					$d[$key.'_'.$i.'_name']['object'] = $outer;

					$i++;
				}
			} else {
				$d[$key.'_name'] = '';
			}
		}

		$d['comment']['label']                    = $this->lang['comment'];
		$d['comment']['object']['type']           = 'htmlobject_textarea';
		$d['comment']['object']['attrib']['id']   = 'permission_group_comment';
		$d['comment']['object']['attrib']['name'] = 'permission_group_comment';
		$d['comment']['object']['attrib']['maxlength'] = 255;

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
