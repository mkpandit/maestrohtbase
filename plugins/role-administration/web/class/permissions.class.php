<?php
/**
 * permissions
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


class permissions
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
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->file     = $htvcenter->file();
	}

	//--------------------------------------------
	/**
	 * Get plugin permissions
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function get_plugins() {

		$plugins = new plugin();
		$plugins = $plugins->enabled();

		$content = array();
		// methodes that are no actions
		$methods = array('api', 'action');
		$i = 0;
		$x = 0;
		foreach($plugins as $plugin) {
			if(
				$plugin === 'documentation' ||
				$plugin === 'role-administration'
			) {
				continue;
			}
		
			$path = $this->rootdir.'/plugins/'.$plugin.'/class';
			$files = $this->file->get_files($path);

			$objs = array();
			if(is_array($files)) {
				foreach($files as $file) {
					if(strripos($file['name'], 'controller.class') !== false && strripos($file['name'], 'about') === false) {
						require_once($file['path']);
						$class = str_replace('.class.php', '', $file['name']);
						$class = str_replace('.', '_', $class);
						$class = str_replace('-', '_', $class);
						$class = new $class($this->htvcenter, $this->response);
						$objs[] = $class;
					}
				}
			}

			$tmp = array();
			foreach($objs as $obj) {
				$tmp['object'] = $obj;
				$tmp['type']   = 'plugin';
				$tmp['name']   = $plugin;
				$tmp['class']  = get_class($obj);
				$tmp['actions'] = array();
				if(get_class_methods($obj)) {
					$meths = get_class_methods($obj);
					foreach($meths as $m) {
						if(!in_array($m, $methods) && strpos($m, '__') === false && strripos($m, 'reload') === false) {
							if($m !== 'duplicate') {
								$tmp['actions'][] = $m;
							} else {
								$tmp['actions'][] = 'clone';
						}
							$x = $x + count($m);
						}
					}
				} else {
					$tmp['actions'] = '';
				}
				$content[$i] = $tmp;
				$i++;
			}

		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Get base permissions
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function get_base() {

		$plugins = array('appliance', 'image', 'kernel', 'resource', 'storage', 'event', 'aa_plugins');

		$content = array();
		// methodes that are no actions
		$methods = array('api', 'action');
		$i = 0;
		$x = 0;
		foreach($plugins as $plugin) {
		
			if($plugin === 'aa_plugins') {
				$path = $this->rootdir.'/plugins/'.$plugin.'/class';
			} else {
				$path = $this->rootdir.'/server/'.$plugin.'/class';
			}
			$files = $this->file->get_files($path);

			$objs = array();
			if(is_array($files)) {
				foreach($files as $file) {
					if(strripos($file['name'], 'controller.class') !== false && strripos($file['name'], 'about') === false) {
						require_once($file['path']);
						$class = str_replace('.class.php', '', $file['name']);
						$class = str_replace('.', '_', $class);
						$class = str_replace('-', '_', $class);
						$class = new $class($this->htvcenter, $this->response);
						$objs[] = $class;
					}
				}
			}

			$tmp = array();
			foreach($objs as $obj) {
				$tmp['object'] = $obj;
				$tmp['type']   = 'base';
				$tmp['name']   = $plugin;
				$tmp['class']  = get_class($obj);
				$tmp['actions'] = array();
				if(get_class_methods($obj)) {
					$meths = get_class_methods($obj);
					foreach($meths as $m) {
						if(!in_array($m, $methods) && strpos($m, '__') === false && strripos($m, 'reload') === false) {
							if($m !== 'duplicate') {
								$tmp['actions'][] = $m;
							} else {
								$tmp['actions'][] = 'clone';
						}
							$x = $x + count($m);
						}
					}
				} else {
					$tmp['actions'] = '';
				}
				$content[$i] = $tmp;
				$i++;
			}

		}
		return $content;
	}


}
?>
