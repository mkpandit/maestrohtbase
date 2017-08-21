<?php

/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/




class wakeuponlan_sleep
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'wol_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'wol_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'wol_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'wol_identifier';
var $tpldir;
var $lang;
var $htvcenter_base_dir;
var $htvcenter;
var $htvcenter_ip;
var $event;


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
		$this->htvcenter  = $htvcenter;
		$this->user	    = $htvcenter->user();
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
		if ($this->response->html->request()->get($this->identifier_name) === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}

		$response = $this->response;
		// identifier array
		$identifier_ar = $this->response->html->request()->get($this->identifier_name);
		if( $identifier_ar !== '' ) {
			$i = 0;
			foreach($identifier_ar as $ident_id) {
				// get resource
				$resource_sleep = new resource();
				$resource_sleep->get_instance_by_id($ident_id);
				// check if wakeuponlan is enabled
				$wakeuponlan_state = $resource_sleep->get_resource_capabilities("SFO");
				if ($wakeuponlan_state != 1) {
					$response_message .= $this->lang['wakeuponlan_disabled']."<br>";
					continue;
				}
				# power of resource
				$resource_sleep->send_command($resource_sleep->ip, "halt");
				// set state to off
				$resource_fields=array();
				$resource_fields["resource_state"]="off";
				$resource_sleep->update_info($ident_id, $resource_fields);
				$response_message .= $this->lang['wakeuponlan_set_resource_to_sleep']."".$ident_id."<br>";
				$i++;
			}
		}
		// redirect to select
		if(isset($response_message)) {
			$response->msg = $response_message;
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
	}
}



?>


