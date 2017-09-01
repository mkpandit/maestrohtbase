<?php
/**
 * Cloud User Requests
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_user_requests
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud_instance';



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
		$this->response = $response;
		$this->htvcenter = $htvcenter;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {

		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/clouduser.class.php";
		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/cloudrequest.class.php";

		$username = $this->response->html->request()->get('username');
		$this->response->add('username', $username);
		$user = new clouduser($username);
		$user->get_instance_by_name($username);
		$request = new cloudrequest();
		$requests = $request->get_all_ids_per_user($user->id);

		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/cloud-request.controller.class.php";
		$controller = new cloud_request_controller($this->htvcenter, $this->response);

		$str = '';
		foreach ($requests as $id) {
			$_REQUEST['cloud_request_id'] = $id['cr_id'];
			$_REQUEST[$controller->actions_name] = 'details';
			ob_start();
			$controller->api();
			$str .= ob_get_contents();
			ob_end_clean();
		}
		return $str;
	}

}
?>
