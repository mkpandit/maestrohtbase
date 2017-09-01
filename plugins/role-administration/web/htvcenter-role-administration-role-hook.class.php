<?php
/**
 * htvcenter Role Hook
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class htvcenter_role_administration_role_hook
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($htvcenter, $controller) {
		$this->htvcenter  = $htvcenter;
		$this->controller  = $controller;
		require_once($this->htvcenter->get('basedir').'/plugins/role-administration/web/class/role-administration.class.php');
		$this->role = new role_administration();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param bool $bool if true only true or false will be returned
	 * @return htmlobject_template|bool
	 */
	//--------------------------------------------
	function role_administration_check_permission($bool = false) {
		$user = $this->htvcenter->user();
		if($user->isAdmin()) {
			if($bool === true) {
				return true;
			} else {
				return $this->controller->action();
			}
		} else {
			$response = $this->controller->response;
			$html     = $response->html;
			$action   = $html->request()->get($this->controller->actions_name);
			// handle empty action
			if($action === '') {
				// try select as first action
				if(method_exists($this->controller, 'select')) {
					$action = 'select';
				} else {
					// pick first method as action
					$m = get_class_methods($this->controller);
					foreach($m as $a) {
						if(!in_array($a, array('api', 'action')) && strpos($a, '__') === false && strripos($a, 'reload') === false) {
							$action = $a;
							break;
						}
					}
				}
			}
			// handle array action
			else if(is_array($action)) {
				$action = key($action);
			}

			$role        = $this->role->get_role_infos_by_name($user->role);
			$groups      = $this->role->role2group(array('role_id' => $role[0]['role_id']), 'select');
			$permissions = array();
			if(is_array($groups)) {
				foreach($groups as $v) {
					$p['permission_controller'] = get_class($this->controller);
					$p['permission_group_id'] = $v['permission_group_id'];
					$permission = $this->role->permissions($p, 'select');
					if(is_array($permission)) {
						$permissions = array_merge($permissions, array_shift($permission));
					}
				}
			}

			if(in_array($action, $permissions)) {
				if($bool === true) {
					return true;
				} else {
					return $this->controller->action();
				}
			} else {
				if($bool === true) {
					return false;
				} else {
					$controller = str_replace('_controller', '', get_class($this->controller));
					$controller = str_replace('aa_', '', $controller);
	
					$t = $html->template($this->htvcenter->get('webdir').'/tpl/permission_denied.tpl.php');
					$t->add($action, 'action');
					$t->add($controller, 'controller');
					$t->add($this->htvcenter->get('baseurl'), 'baseurl');

					$content['label']   = 'Permissions';
					$content['value']   = $t;
					$content['target']  = $html->thisfile;
					$content['request'] = $response->get_array($this->controller->actions_name, '' );
					$content['onclick'] = false;
					$content['active']  = true;
					$tab = $html->tabmenu('permissions');
					$tab->css = 'htmlobject_tabs';
					$tab->add(array($content));
					return $tab;
				}
			}
		}
	}


}
?>
