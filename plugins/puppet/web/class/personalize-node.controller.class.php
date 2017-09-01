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


class personalize_node_controller {
	
	/**
	* name of action buttons
	* @access public
	* @var string
	*/
	var $actions_name = 'personalize_node_action';
	/**
	* message param
	* @access public
	* @var string
	*/
	var $message_param = "personalize_node_msg";
	/**
	* id for tabs
	* @access public
	* @var string
	*/
	var $prefix_tab = 'personalize_node_tab';
	/**
	* identifier name
	* @access public
	* @var string
	*/
	var $identifier_name = 'personalize_node_identifier';
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
	var $lang = array(
		'select' => array(
			'tab' => 'Role Administration',
			'label' => 'Role Administration',
			'id' => 'ID',
			'name' => 'Role Name',
			'permission_groups' => 'Permission Groups',
			'comment' => 'Comment',
			'action_edit' => 'edit',
			'action_add' => 'Add new role',
			'action_remove' => 'delete',
			'please_wait' => 'Loading. Please wait ..',
		),
		'add' => array(
			'tab' => 'Add Role',
			'label' => 'Add new Role',
			'name' => 'Role Name',
			'groups' => 'Permission Groups',
			'groups_title' => 'Please select one or more groups',
			'comment' => 'Comment',
			'error_name' => 'Role Name must be %s only',
			'error_in_use' => 'Role Name %s is already in use',
			'msg' => 'Role %s saved successfully',
			'canceled' => 'Operation canceled. Please wait ..',
			'please_wait' => 'Loading. Please wait ..',
		),
		'remove' => array(
			'tab' => 'Remove Node(s)',
			'label' => 'Remove Node(s)',
			'msg_removed' => 'Removed node %s successfully',
			'canceled' => 'Operation canceled. Please wait ..',
			'please_wait' => 'Loading. Please wait ..',
		),
		'edit' => array(
			'tab' => 'Edit Role',
			'label' => 'Edit Role %s',
			'name' => 'Role Name',
			'groups' => 'Permission Groups',
			'groups_title' => 'Please select one or more groups',
			'comment' => 'Comment',
			'msg' => 'Role %s updated successfully',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..'
		),
	);

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
			$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/puppet/lang", 'personalize-node.ini');
			$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';
			$this->htvcenter->lc();
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
			$this->action = '';
			$ar = $this->response->html->request()->get($this->actions_name);
			
			if($ar !== '') {
				if(is_array($ar)) {
					$this->action = key($ar);
				} else {
					$this->action = $ar;
				}
			} 
			else if(isset($action)) {
				$this->action = $action;
			}
			if($this->response->cancel()) {
				$this->action = 'select';
			}

			$content = array();
			switch( $this->action ) {
				case '':
				default:
				case 'select':
					$content[] = $this->select(true);
				break;
				case 'add':
					$content[] = $this->select(false);
					$content[] = $this->add(true);
				break;
				case 'edit':
					$content[] = $this->select(false);
					$content[] = $this->edit(true);
				break;
				case 'remove':
					$content[] = $this->select(false);
					$content[] = $this->remove(true);
				break;
			}
			$tab = $this->response->html->tabmenu($this->prefix_tab);
			$tab->message_param = $this->message_param;
			$tab->css = 'htmlobject_tabs';
			$tab->add($content);
			return $tab;
		}

		//--------------------------------------------
		/**
		 * Select
		 *
		 * @access public
		 * @param bool $hidden
		 * @return array
		 */
		//--------------------------------------------
		function select( $hidden = true ) {
			$data = '';
			if( $hidden === true ) {
				require_once($this->rootdir.'/plugins/puppet/class/personalize-node.select.class.php');
				$controller = new personalize_node_select($this->htvcenter, $this->response);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['select'];
				$data = $controller->action();
			}
			$content['label']   = $this->lang['select']['tab'];
			$content['value']   = $data;
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = $this->response->get_array($this->actions_name, 'select' );
			$content['onclick'] = false;
			if($this->action === 'select'){
				$content['active']  = true;
			}
			return $content;
		}

		//--------------------------------------------
		/**
		 * Add
		 *
		 * @access public
		 * @param bool $hidden
		 * @return array
		 */
		//--------------------------------------------
		function add( $hidden = true ) {
			$data = '';
			if( $hidden === true ) {
				require_once($this->rootdir.'/plugins/puppet/class/personalize-node.add.class.php');
				$controller = new personalize_node_add($this->htvcenter, $this->response);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['add'];
				$data = $controller->action();
			}
			$content['label']   = $this->lang['add']['tab'];
			$content['value']   = $data;
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = $this->response->get_array($this->actions_name, 'add' );
			$content['onclick'] = false;
			if($this->action === 'add'){
				$content['active']  = true;
			}
			return $content;
		}

		//--------------------------------------------
		/**
		 * Remove
		 *
		 * @access public
		 * @param bool $hidden
		 * @return array
		 */
		//--------------------------------------------
		function remove( $hidden = true ) {
			$data = '';
			
			if( $hidden === true ) {
				require_once($this->rootdir.'/plugins/puppet/class/personalize-node.remove.class.php');
				$controller = new personalize_node_remove($this->htvcenter, $this->response);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['remove'];
				$data = $controller->action();
			}
			$content['label']   = 'remove';
			$content['hidden']  = true;
			$content['value']   = $data;
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
			$content['onclick'] = false;
			if($this->action === 'remove' || $this->action === $this->lang['select']['action_remove']){
				$content['active']  = true;
			}
			return $content;
		}


		//--------------------------------------------
		/**
		 * Edit
		 *
		 * @access public
		 * @param bool $hidden
		 * @return array
		 */
		//--------------------------------------------
		function edit( $hidden = true ) {
			
			$data = '';
			if( $hidden === true ) {
				require_once($this->rootdir.'/plugins/puppet/class/personalize-node.edit.class.php');
				$controller = new personalize_node_edit($this->htvcenter, $this->response);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['edit'];
				$data = $controller->action();
			}
			$content['label']   = $this->lang['edit']['tab'];
			$content['value']   = $data;
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
			$content['onclick'] = false;
			if($this->action === 'edit'){
				$content['active']  = true;
			}
			return $content;
		}
	
}
?>
