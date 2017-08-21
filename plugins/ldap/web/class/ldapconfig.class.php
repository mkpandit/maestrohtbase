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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;

class ldapconfig {

	var $id = '';
	var $key = '';
	var $value = '';


	function __construct() {
		$this->table = "ldap_config";
	}


	// ---------------------------------------------------------------------------------
	// methods to create an instance of a ldapconfig object filled from the db
	// ---------------------------------------------------------------------------------

	// returns an appliance from the db selected by id or name
	function get_instance($id, $name) {
		global $event;
		$db=htvcenter_get_db_connection();
		if ("$id" != "") {
			$ldapconfig_array = $db->Execute('select * from '.$this->table.' where csc_id='.$id);
		} else if ("$name" != "") {
			$ldapconfig_array = $db->Execute('select * from '.$this->table.' where csc_key=\''.$name.'\'');
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			return;
		}

		foreach ($ldapconfig_array as $index => $ldapconfig) {
			$this->id = $ldapconfig["csc_id"];
			$this->key = $ldapconfig["csc_key"];
			$this->value = $ldapconfig["csc_value"];
		}
		return $this;
	}

	// returns an appliance from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	// returns an appliance from the db selected by key
	function get_instance_by_key($name) {
		$this->get_instance("", $name);
		return $this;
	}


	// ---------------------------------------------------------------------------------
	// general ldapconfig methods
	// ---------------------------------------------------------------------------------




	// checks if given ldapconfig id is free in the db
	function is_id_free($ldapconfig_id) {
		global $event;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('select csc_id from '.$this->table.' where csc_id='.$ldapconfig_id);
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// adds ldapconfig to the database
	function add($ldapconfig_fields) {
		global $event;
		if (!is_array($ldapconfig_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", "ldapconfig_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->table, $ldapconfig_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", "Failed adding new ldapconfig to database", "", "", 0, 0, 0);
		}
	}



	// removes ldapconfig from the database
	function remove($ldapconfig_id) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('delete from '.$this->table.' where csc_id='.$ldapconfig_id);
	}

	// removes ldapconfig from the database by key
	function remove_by_name($ldapconfig_key) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('delete from '.$this->table.' where csc_key=\''.$ldapconfig_key.'\'');
	}


	// returns ldapconfig value by ldapconfig_id
	function get_value($ldapconfig_id) {
		global $event;
		$db=htvcenter_get_db_connection();
		$ldapconfig_set = $db->Execute('select csc_value from '.$this->table.' where csc_id='.$ldapconfig_id);
		if (!$ldapconfig_set) {
			$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$ldapconfig_set->EOF) {
				return $ldapconfig_set->fields["csc_value"];
			} else {
				return "";
			}
		}
	}


	// sets a  ldapconfig value by ldapconfig_id
	function set_value($ldapconfig_id, $ldapconfig_value) {
		global $event;
		$db=htvcenter_get_db_connection();
		$ldapconfig_set = $db->Execute('UPDATE '.$this->table.' SET csc_value=\''.$ldapconfig_value.'\' WHERE csc_id=\''.$ldapconfig_id.'\'');
		if (!$ldapconfig_set) {
			$event->log("set_value", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		}
	}

	// sets a  ldapconfig value by ldapconfig_key
	function set_value_by_key($ldapconfig_key, $ldapconfig_value) {
		global $event;
		$db=htvcenter_get_db_connection();
		$ldapconfig_set = $db->Execute('UPDATE '.$this->table.' SET csc_value=\''.$ldapconfig_value.'\' WHERE csc_key=\''.$ldapconfig_key.'\'');
		if (!$ldapconfig_set) {
			$event->log("set_value_by_key", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		}
	}


	// returns the number of ldapconfigs for an ldapconfig type
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('select count(csc_id) as num from '.$this->table);
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all ldapconfig names
	function get_list() {
		$query = 'select csc_id, csc_value from '.$this->table;
		$ldapconfig_name_array = array();
		$ldapconfig_name_array = htvcenter_db_get_result_double ($query);
		return $ldapconfig_name_array;
	}


	// returns a list of all ldapconfig ids
	function get_all_ids() {
		global $event;
		$ldapconfig_list = array();
		$query = 'select csc_id from '.$this->table;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$ldapconfig_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $ldapconfig_list;

	}




	// displays the ldapconfig-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $event;
		$db=htvcenter_get_db_connection();
		$recordSet = $db->SelectLimit('select * from '.$this->table.' order by '.$sort.' '.$order, $limit, $offset);
		$ldapconfig_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "ldapconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($ldapconfig_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $ldapconfig_array;
	}









// ---------------------------------------------------------------------------------

}

