<?php
/**
 * novnc Api
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/

class novnc_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->response->html;
		$this->response   = $this->html->response();
		$this->file       = $this->controller->file;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'console':
				$this->console();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Console
	 *
	 * @access public
	 */
	//--------------------------------------------
	function console() {
		$_REQUEST[$this->controller->actions_name] = 'console';
		$role  = $this->controller->htvcenter->role($this->response);
		$controller = $role->check_permission($this->controller);

		if(is_object($controller)) {
			$data = $controller->get_string();
		} else {
			$data = $controller;
		}

		$css = $this->file->get_contents($this->controller->htvcenter->get('basedir').'/plugins/novnc/web/css/novnc.css');
		$css = str_replace('../novncjs/Orbitron700.woff', $this->controller->jsurl.'Orbitron700.woff', $css);
		$css = str_replace('../novncjs/Orbitron700.ttf', $this->controller->jsurl.'Orbitron700.ttf', $css);
		echo '<!DOCTYPE html>';
		echo '<html>';
		echo '<head>';
		echo '<title>htvcenter Server</title>';
		echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
		echo '<style type="text/css">'.$css.'</style>';
		echo '<style type="text/css">';
		echo 'html { font-family: Arial,sans-serif; font-size: 13px;}';
		echo 'body { margin: 0; padding: 0; }';
		echo '.novncpage { margin: 0; }';
		echo ' #detachbutton { display: none; }';
		echo '</style>';
		echo '</head>';
		echo '<body>';
		echo $data;
		echo '</body>';
		echo '</html>';
	}
}
?>
