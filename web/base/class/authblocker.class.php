<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/


// This class represents a auth-blocker object in htvcenter

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";

$AUTH_BLOCKER_TABLE="auth_blocker_info";
global $AUTH_BLOCKER_TABLE;
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;


class authblocker {

var $id = '';
var $image_id = '';
var $image_name = '';
var $start_time = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function authblocker() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $AUTH_BLOCKER_TABLE, $htvcenter_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = "auth_blocker_info";
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a authblocker object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $image_id, $image_name) {
	global $AUTH_BLOCKER_TABLE;
	global $event;
	$db=htvcenter_get_db_connection();
	if ("$id" != "") {
		$authblocker_array = $db->Execute("select * from ".$this->_db_table." where ab_id=$id");
	} else if ("$image_id" != "") {
		$authblocker_array = $db->Execute("select * from ".$this->_db_table." where ab_image_id=$image_id");
	} else if ("$image_name" != "") {
		$authblocker_array = $db->Execute("select * from ".$this->_db_table." where ab_image_name='$image_name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", "Could not create instance of authblocker without data", "", "", 0, 0, 0);
		array_walk(debug_backtrace(),create_function('$a,$b','syslog(LOG_ERR, "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}); ");'));
		return;
	}

	foreach ($authblocker_array as $index => $authblocker) {
		$this->id = $authblocker["ab_id"];
		$this->image_id = $authblocker["ab_image_id"];
		$this->image_name = $authblocker["ab_image_name"];
		$this->start_time = $authblocker["ab_start_time"];
	}
	return $this;
}


// returns an authblocker from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns an authblocker from the db selected by the image-id
function get_instance_by_image_id($image_id) {
	$this->get_instance("", $image_id, "");
	return $this;
}

// returns an authblocker by image-name
function get_instance_by_image_name($image_name) {
	$this->get_instance("", "", $image_name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general authblocker methods
// ---------------------------------------------------------------------------------




// checks if given authblocker id is free in the db
function is_id_free($authblocker_id) {
	global $AUTH_BLOCKER_TABLE;
	global $event;
	$db=htvcenter_get_db_connection();
	$rs = $db->Execute("select ab_id from ".$this->_db_table." where ab_id=$authblocker_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds authblocker to the database
function add($authblocker_fields) {
	global $AUTH_BLOCKER_TABLE;
	global $event;
	if (!is_array($authblocker_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", "authblocker_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=htvcenter_get_db_connection();
	$result = $db->AutoExecute($this->_db_table, $authblocker_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", "Failed adding new authblocker to database", "", "", 0, 0, 0);
	}
}



// removes authblocker from the database
function remove($authblocker_id) {
	global $AUTH_BLOCKER_TABLE;
	$db=htvcenter_get_db_connection();
	$rs = $db->Execute("delete from ".$this->_db_table." where ab_id=$authblocker_id");
}


// updates a authblocker
function update($ab_id, $ab_fields) {
	global $AUTH_BLOCKER_TABLE;
	global $event;
	if ($ab_id < 0 || ! is_array($ab_fields)) {
		$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", "Unable to update authblocker $ab_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=htvcenter_get_db_connection();
	unset($ab_fields["ab_id"]);
	$result = $db->AutoExecute($this->_db_table, $ab_fields, 'UPDATE', "ab_id = $ab_id");
	if (! $result) {
		$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", "Failed updating authblocker $ab_id", "", "", 0, 0, 0);
	}
}



// returns the number of authblockers for an authblocker type
function get_count() {
	global $AUTH_BLOCKER_TABLE;
	$count=0;
	$db=htvcenter_get_db_connection();
	$rs = $db->Execute("select count(ab_id) as num from ".$this->_db_table);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all authblocker ids
function get_all_ids() {
	global $AUTH_BLOCKER_TABLE;
	global $event;
	$authblocker_list = array();
	$query = "select ab_id from ".$this->_db_table;
	$db=htvcenter_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "authblocker.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$authblocker_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $authblocker_list;

}




// ---------------------------------------------------------------------------------

}


?>