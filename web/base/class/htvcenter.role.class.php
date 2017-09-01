<?php
/**
 * htvcenter Content
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_role
{

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
		$this->response = $response;
	}

	//--------------------------------------------
	/**
	 * Get plugin
	 *
	 * @access public
	 * @param string $class
	 * @param string $path
	 */
	//--------------------------------------------
	function get_plugin($class, $path) {
		if($this->htvcenter->file()->exists($path)) {
			$this->htvcenter->init();
			require_once($path);
			$controller = new $class($this->htvcenter, $this->response);
			$data = $this->check_permission($controller);
			return $data;
		}
		else {
			$t = $this->response->html->template($this->htvcenter->get('webdir').'/tpl/plugin_not_found.tpl.php');
			$t->add(str_replace('_controller', '', $class), 'plugin');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');

			$content['label']   = 'Plugin not found';
			$content['value']   = $t;
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = array();
			$content['onclick'] = false;
			$content['active']  = true;
			$tab = $this->response->html->tabmenu('permissions');
			$tab->css = 'htmlobject_tabs';
			$tab->add(array($content));
			return $tab;
		}
	}

	//--------------------------------------------
	/**
	 * check_permission
	 *
	 * datacenter_controller, user_controller,
	 * documentation_controller, about(_.._controller)s
	 * and action load(...) will be ignored
	 *
	 * @access public
	 * @param object $object
	 * @param bool $bool if true only true or false will be returned
	 * @return htmlobject_template|bool
	 */
	//--------------------------------------------
	function check_permission($object = null, $bool = false) {
		$controller = get_class($object);
		$action     = $this->response->html->request()->get($object->actions_name);
		$user       = $this->htvcenter->user();
		if(is_array($action)) {
			$action = key($action);
		}
		if(
			!$user->isAdmin() &&
			$controller !== 'datacenter_controller' &&
			$controller !== 'user_controller' &&
			$controller !== 'documentation_controller' &&
 			strripos($controller, 'about') === false &&
			substr($action, 0, 4) !== 'load'
		) {
			// check for plugin implementing the role hook
			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			foreach ($enabled_plugins as $index => $plugin_name) {
				$plugin_role_hook = $this->htvcenter->get('webdir')."/plugins/".$plugin_name."/htvcenter-".$plugin_name."-role-hook.class.php";
				if (file_exists($plugin_role_hook)) {
					require_once $plugin_role_hook;
					$plugin_role_hook_class = "htvcenter-".$plugin_name."-role-hook";
					$plugin_role_hook_class = str_replace("-", "_", $plugin_role_hook_class);
					$plugin_role_hook_method = $plugin_name."-check_permission";
					$plugin_role_hook_method = str_replace("-", "_", $plugin_role_hook_method);
					$plugin_role_hook = new $plugin_role_hook_class($this->htvcenter, $object);
					return $plugin_role_hook->$plugin_role_hook_method($bool);
				}
			}
			// no plugin implemented the role hook, go on with basic role permission check
			// is_not_admin -> tab menu with global error permission msg
			$response = $object->response;
			$html     = $response->html;
			// handle empty action
			if($action === '') {
				// try select as first action
				if(method_exists($object, 'select')) {
					$action = 'select';
				} else {
					// pick first method as action
					$m = get_class_methods($object);
					foreach($m as $a) {
						if(!in_array($a, array('api', 'action')) && strpos($a, '__') === false && strripos($a, 'reload') === false) {
							$action = $a;
							break;
						}
					}
				}
			}

			if($bool === true) {
				return false;
			} else {
				$controller = str_replace('_controller', '', get_class($object));
				$controller = str_replace('aa_', '', $controller);

				$t = $html->template($this->htvcenter->get('webdir').'/tpl/permission_denied.tpl.php');
				$t->add($action, 'action');
				$t->add($controller, 'controller');
				$t->add($this->htvcenter->get('baseurl'), 'baseurl');

				$content['label']   = 'Permissions';
				$content['value']   = $t;
				$content['target']  = $html->thisfile;
				$content['request'] = $response->get_array($object->actions_name, '' );
				$content['onclick'] = false;
				$content['active']  = true;
				$tab = $html->tabmenu('permissions');
				$tab->css = 'htmlobject_tabs';
				$tab->add(array($content));
				return $tab;
			}

		} else {
			if($bool === true) {
				return true;
			} else {
				return $object->action();
			}
		}
	}


}
?>
