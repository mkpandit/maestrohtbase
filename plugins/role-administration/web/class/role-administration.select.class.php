<?php
/**
 * role-administration Appliance
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class role_administration_select
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
		$this->tpldir   = $this->rootdir.'/plugins/role-administration/tpl';
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
		$response = $this->select();
		$t = $this->response->html->template($this->tpldir.'/role-administration-select.tpl.php');
		$t->add($response->table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');

		$a           = $response->html->a();
		$a->href     = $response->get_url($this->actions_name, 'add' );
		$a->label    = $this->lang['action_add'];
		$a->title    = $this->lang['action_add'];
		$a->css      = 'add';
		$a->handler  = 'onclick="wait();"';

		$t->add($a, 'add');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->response;

		$h['role_id']['title']    = $this->lang['id'];
		$h['role_id']['sortable'] = true;

		$h['role_name']['title']    = $this->lang['name'];
		$h['role_name']['sortable'] = true;

		$h['permission_groups']['title']    = $this->lang['permission_groups'];
		$h['permission_groups']['sortable'] = false;

		$h['role_comment']['title']    = $this->lang['comment'];
		$h['role_comment']['sortable'] = false;

		$h['edit']['title']    = '&#160;';
		$h['edit']['sortable'] = false;

		$result = $this->role->get_role_infos();
		$b = array();

		if(is_array($result)) {
			foreach($result as $k => $v) {
				$tmp = array();
				$temp = array();
				$tmp['role_id'] = $v['role_id'];
				$tmp['role_name'] = $v['role_name'];
				$tmp['role_comment'] = (isset($v['role_comment'])) ? $v['role_comment'] : '&#160;';
				$groups = $this->role->role2group(array('role_id' => $v['role_id']), 'select');
				if(isset($groups) && is_array($groups)) {
					foreach($groups as $v) {
						$z = $this->role->get_permission_groups_by_id($v['permission_group_id']);
						$temp[] = $z['permission_group_name'];
					}
					$group = implode(', ', $temp);
				} else {
					$group = '&#160';
				}
				$tmp['permission_groups'] = $group;
					if($v['role_id'] !== '0' && $v['role_id'] !== '1') {
						$a           = $response->html->a();
						$a->href     = $response->get_url($this->actions_name, 'edit' ).'&role_id='.$v['role_id'];
						$a->label    = $this->lang['action_edit'];
						$a->title    = $this->lang['action_edit'];
						$a->css      = 'edit';
						$a->handler  = 'onclick="wait();"';
						$tmp['edit'] = $a->get_string();
					} else {
						$tmp['edit'] = '&#160;';
					}

				$b[] = $tmp;
			}
		}


		$table = $this->response->html->tablebuilder('role_administration', $this->response->get_array($this->actions_name, 'select'));
		$table->offset       = 0;
		$table->sort         = 'role_id';
		$table->limit        = 20;
		$table->order        = 'ASC';
		$table->max          = count($b);
		$table->css          = 'htmlobject_table';
		$table->border       = 0;
		$table->id           = 'Tabelle';
		$table->form_action  = $this->response->html->thisfile;
		$table->head         = $h;
		$table->body         = $b;
		$table->sort_params  = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form    = true;
		$table->sort_link    = false;
		$table->autosort     = true;
		$table->identifier = 'role_id';
		$table->identifier_name = $this->identifier_name;
		$table->actions_name = $this->actions_name;
		$table->actions      = array(array('remove' => $this->lang['action_remove']));
		$table->identifier_disabled = array(0,1);

		$response->table = $table;
		return $response;
	}


}
?>
