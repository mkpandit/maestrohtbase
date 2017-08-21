<?php
/**
 * Cloud User Instances
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_user_instances
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud_instances';



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
		$this->file = $this->htvcenter->file();
		$this->webdir  = $this->htvcenter->get('webdir');
		$this->rootdir  = $this->htvcenter->get('basedir');
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

		require_once($this->htvcenter->get('basedir').'/plugins/cloud/cloud-fortis/web/user/class/cloud-ui.appliances.class.php');
		require_once($this->htvcenter->get('basedir').'/plugins/cloud/cloud-fortis/web/user/class/cloud-ui.images.class.php');
		require_once($this->htvcenter->get('basedir').'/plugins/cloud/web/class/clouduser.class.php');
		require_once($this->htvcenter->get('basedir').'/plugins/cloud/web/class/cloudconfig.class.php');

		$return = '';

		$username = $this->response->html->request()->get('username');
		$this->response->add('username', $username);

		$user = new clouduser($username);
		$user->get_instance_by_name($username);
		$this->lang['appliances']['label'] = $this->lang['appliances']['label'] .' ('.$user->name.')';

		$response = $this->response->response();
		$response->add($this->actions_name, 'instances');
		$response->redirect = false;

		require_once($this->htvcenter->get('basedir').'/web/base/class/htvcenter.class.php');
		$htvcenter = new htvcenter($this->htvcenter->file(), $user, $response);

		$controller = new cloud_ui_appliances($htvcenter, $response);
		$controller->tpldir = $this->tpldir;
		$controller->identifier_name = 'cloudappliance_id';
		$controller->lang = $this->lang;
		$controller->basedir = $this->htvcenter->get('basedir');
		$controller->message_param = $this->message_param;
		$controller->clouduser = $user;
		$controller->cloudconfig = new cloudconfig();

		$data = $controller->action();

		if( $data instanceof htmlobject_template || $data instanceof htmlobject_template_debug) {
			$data->add('', 'private_images_link');
			$data->add('', 'profiles_link');
			$data->add('', 'profiles');
			$data->add('none', 'display_price_list');
			$return = $data->get_string();
		}


		if($controller->cloudconfig->get_value_by_key('show_private_image') === 'true') {
			$controller = new cloud_ui_images($htvcenter, $response);
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = 'cloudappliance_id';
			$controller->lang = $this->lang['images'];
			$controller->basedir = $this->htvcenter->get('basedir');
			$controller->message_param = $this->message_param;
			$controller->clouduser = $user;
			$controller->cloudconfig = new cloudconfig();

			$data = $controller->action();

			if( $data instanceof htmlobject_template || $data instanceof htmlobject_template_debug) {
				$return .= $data->get_string();
			}			
		}


		return $return;
	}

}
?>
