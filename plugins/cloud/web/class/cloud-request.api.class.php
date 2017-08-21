<?php
/**
 * Cloud Request API
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_request_api
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud-api';



	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param controller $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->controller->action = 'details;';
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		// $this->controller->response->html->help($this->controller);
		// echo $this->controller->actions_name;

		$this->action = '';
		$ar = $this->controller->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'details':
				$data = $this->controller->details(true);
				$details = $data['value'];
				echo $details->get_string();
			break;
		}
	}	

}

?>


