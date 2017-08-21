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

class htvcenter_cli
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* absolute path to webroot
* @access public
* @var string
*/
var $rootdir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter_controller $controller
	 * @param string $argv console parameters
	 */
	//--------------------------------------------
	function __construct($controller, $argv) {
		$this->controller = $controller;
		$this->response   = $this->controller->response;
		$this->htvcenter    = $this->controller->htvcenter;
		$this->file       = $this->controller->htvcenter->file();
		$this->user       = $this->controller->htvcenter->user();

		$this->htvcenter->init();

		// set request params
		foreach($argv as $k => $v) {
			if($k !== 0) {
				$tmp = explode('=', $v);
				if(isset($tmp[0]) && isset($tmp[1])) {
					if(strpos($tmp[0], '[]') !== false) {
						$tmp[0] = str_replace('[]', '', $tmp[0]);
						$_REQUEST[$tmp[0]][] = $tmp[1];
					} else {
						$_REQUEST[$tmp[0]] = $tmp[1];
					}
				}
			}
		}
		#print_r($_REQUEST);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get('action');
		switch( $action ) {
			case 'plugin':
				$this->plugin();
			break;
			case 'base':
				$this->base();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Load plugins
	 *
	 * @access public
	 */
	//--------------------------------------------
	function plugin() {
		$plugin = $this->response->html->request()->get('plugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';
		$path   = $this->controller->rootdir.'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
		if($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->htvcenter, $this->response);
			if(method_exists($controller, 'cli')) {
				$controller->cli();
			}
		}
	}

	//--------------------------------------------
	/**
	 * Load Base
	 *
	 * @access public
	 */
	//--------------------------------------------
	function base() {
		$plugin = $this->response->html->request()->get('base');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';
		$path   = $this->controller->rootdir.'/server/'.$plugin.'/class/'.$name.'.controller.class.php';
		if($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->htvcenter, $this->response);
			if(method_exists($controller, 'cli')) {
				$controller->cli();
			}
		}
	}

}
?>
