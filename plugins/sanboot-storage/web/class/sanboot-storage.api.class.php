<?php
/**
 * Sanboot Api
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class sanboot_storage_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $iscsi_storage_controller
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
		$action = $this->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'progress':
				$this->progress();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get progress
	 *
	 * @access public
	 */
	//--------------------------------------------
	function progress() {
		$name = basename($this->response->html->request()->get('name'));
		$file = $this->controller->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$name;
		if($this->file->exists($file)) {
			echo $this->file->get_contents($file);
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	}





}
?>
