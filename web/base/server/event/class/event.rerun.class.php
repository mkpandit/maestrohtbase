<?php
/**
 * Rerun event
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class event_rerun
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'event_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "event_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'event_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'event_identifier';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		global $htvcenter_SERVER_BASE_DIR;
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$token = $this->response->html->request()->get('token');
		$event_id = $this->response->html->request()->get('event_id');
		$msg = '';
		if($token !== '' && $event_id !== '') {
			$event = new event();
			$event->get_instance_by_id($event_id);
			$event->log("event-action", $_SERVER['REQUEST_TIME'], 5, "event-overview.php", "Re-Running command $token", "", "", 0, 0, 0);
			$command = "mv -f ".$htvcenter_SERVER_BASE_DIR."/htvcenter/web/base/server/event/errors/".$token.".cmd ".$htvcenter_SERVER_BASE_DIR."/htvcenter/var/spool/htvcenter-queue.".$token." && rm -f ".$htvcenter_SERVER_BASE_DIR."/htvcenter/web/base/server/event/errors/".$token.".out";
			shell_exec($command);
			$fields = array();
			$fields["event_priority"] = 4;
			$event->update($event_id, $fields);
			$msg .= "Re-running token ".$token." / Event ID ".$event_id."<br>";
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
		);
	}

}
?>
