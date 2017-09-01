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




class ipmi_update
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ipmi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ipmi_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ipmi_tab';
/**
* id for tabs
* @access public
* @var string
*/
var $identifier_name = 'ipmi_ident';
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
				$resource_ipmi_ip = $this->response->html->request()->get("resource_ipmi_ip[$ident_id]");
				$resource_ipmi_user = $this->response->html->request()->get("resource_ipmi_user[$ident_id]");
				$resource_ipmi_pass = $this->response->html->request()->get("resource_ipmi_pass[$ident_id]");
				$resource_ipmi_comment = $this->response->html->request()->get("resource_ipmi_comment[$ident_id]");
				// check
				if ((!strlen($resource_ipmi_user)) || (!strlen($resource_ipmi_pass)) || (!strlen($resource_ipmi_ip))) {
					$response_message .= "Incomplete parameters for IPMI of resource id $ident_id. Skipping update!<br>";
				} else {
					// prepare to add or update
					$ipmi_set = new ipmi();
					$ipmi_set->get_instance_by_resource_id($ident_id);
					$fields = array();
					$fields['ipmi_resource_id'] = $ident_id;
					$fields['ipmi_resource_ipmi_ip'] = $resource_ipmi_ip;
					$fields['ipmi_user'] = $resource_ipmi_user;
					$fields['ipmi_pass'] = $resource_ipmi_pass;
					$fields['ipmi_comment'] = $resource_ipmi_comment;
					if ($ipmi_set->id) {
						$fields["ipmi_id"] = $ipmi_set->id;
						$ipmi_set->update($ipmi_set->id, $fields);
						$response_message .= $this->lang['ipmi_updated_configuration']."<br>";
					} else{
						$fields["ipmi_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$ipmi_set->add($fields);
						$response_message .= $this->lang['ipmi_added_configuration']."<br>";
					}
				}
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


