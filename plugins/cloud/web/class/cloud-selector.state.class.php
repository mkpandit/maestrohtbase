<?php
/**
 * Cloud Selector Product State
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/



class cloud_selector_state
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cloud_selector_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "cloud_selector_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'cloud_selector_tab';
/**
* path to templates
* @access public
* @var string
*/

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
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
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudselector.class.php";
		$this->cloudselector = new cloudselector();
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
		$response = $this->disable();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'products', $this->message_param, $response->msg).'&product='.$response->product_type
			);
		}
	}

	//--------------------------------------------
	/**
	 * Cloud Selector Disable Product
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function disable() {
		$cloud_selector_id = $this->response->html->request()->get('cloud_selector_id');
		$this->cloudselector->get_instance_by_id($cloud_selector_id);
		$this->response->product_type = $this->cloudselector->type;
		if($this->cloudselector->state == 0) {
			$this->cloudselector->set_state($cloud_selector_id, 1);
			$this->response->msg = $this->lang['msg_enable_successful'];
		} else {
			$this->cloudselector->set_state($cloud_selector_id, 0);
			$this->response->msg = $this->lang['msg_disable_successful'];
		}
		return $this->response;
	}

}
?>
