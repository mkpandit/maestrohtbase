<?php
/**
 * @package htvcenter
 */
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
	require_once "$RootDir/include/htvcenter-server-config.php";
	require_once "$RootDir/include/htvcenter-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * Event Mailer
 *
 * @package htvcenter
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class event_mailer
{
/**
* name of database table
* @access protected
* @var string
*/
var $_db_table;
/**
* path to htvcenter basedir
* @access protected
* @var string
*/
var $_base_dir;
/**
* event object
* @access protected
* @var object
*/
var $_event;

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $htvcenter_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = "event_mailer";
		$this->_db_template = "event_mailer_template";
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get result by user id
	* @access public
	* @param string $user_id
	* @return array | null
	*/
	//--------------------------------------------------
	function get_result_by_user($user_id) {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM '.$this->_db_table.' WHERE user_id='.$user_id);
		if(isset($result->fields) && is_array($result->fields)) {
			return $result->fields;
		}
	}

	//--------------------------------------------------
	/**
	* get template
	* @access public
	* @param string $name
	* @return array | null
	*/
	//--------------------------------------------------
	function get_template() {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM '.$this->_db_template);
		if(is_array($result->fields)) {
			return $result->fields;
		}
	}

	//--------------------------------------------------
	/**
	* update template
	* @access public
	* @param string $user_name
	* @param array $fields
	* @return bool
	*/
	//--------------------------------------------------
	function update_template($fields) {
		if (! is_array($fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "event-mailer.class.php", "Unable to update event-mailer template", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_template, $fields, 'UPDATE', 'event_mailer_template = 1');
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "event-mailer.class.php", "Failed updating template", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------------
	/**
	* get result by event name
	* @access public
	* @param string $name
	* @return array | null
	*/
	//--------------------------------------------------
	function get_result_by_event($name) {
		$db=htvcenter_get_db_connection();
		$result = $db->Execute('SELECT * FROM '.$this->_db_table.' WHERE event_'.$name.'=1');
		if(is_array($result->fields)) {
			while (!$result->EOF) {
				$fields[] = $result->fields;
				$result->MoveNext();
			}
			$result->Close();
			if(is_array($fields)) {
				return $fields;
			}
		}
	}

	//--------------------------------------------------
	/**
	* add a new user
	* @access public
	* @param array $fields
	*/
	//--------------------------------------------------
	function insert($fields) {
		if (!is_array($fields)) {
			$this->_event->log("insert", $_SERVER['REQUEST_TIME'], 2, "event-mailer.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $fields, 'INSERT');
		if (! $result) {
			$this->_event->log("insert", $_SERVER['REQUEST_TIME'], 2, "event-mailer.class.php", "Failed adding new user to database", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------------
	/**
	* update an user
	* @access public
	* @param string $user_name
	* @param array $fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($user_id, $fields) {
		if (! is_array($fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "event-mailer.class.php", "Unable to update event-mailer $user_name", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $fields, 'UPDATE', 'user_id = '.$user_id);
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "event-mailer.class.php", "Failed updating user ".$user_id, "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------------
	/**
	* remove an user
	* @access public
	* @param string $user_name
	*/
	//--------------------------------------------------
	function remove_by_user($user_id) {
		// remove from db
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('delete from '.$this->_db_table.' where user_id = \''.$user_id.'\'');
	}

}
?>
