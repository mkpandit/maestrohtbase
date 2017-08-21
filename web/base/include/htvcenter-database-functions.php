<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once ($RootDir.'include/htvcenter-server-config.php');

if (isset($htvcenter_ORACLE_HOME))  {
	PutEnv("LD_LIBRARY_PATH=$htvcenter_LD_LIBRARY_PATH");
	PutEnv("ORACLE_HOME=$htvcenter_ORACLE_HOME");
	PutEnv("TNS_ADMIN=$htvcenter_TNS_ADMIN");
}

if (!defined("ADODB_ASSOC_CASE")) {
	define('ADODB_ASSOC_CASE',0);
}
// different locations of adodb for suse/redhat/debian
if (file_exists('/usr/share/php/adodb/adodb.inc.php')) {
	require_once ('/usr/share/php/adodb/adodb.inc.php');
} else if (file_exists($RootDir.'include/adodb/adodb.inc.php')) {
	require_once ($RootDir.'include/adodb/adodb.inc.php');
} else if (file_exists('/usr/share/adodb/adodb.inc.php')) {
	require_once ('/usr/share/adodb/adodb.inc.php');
} else {
	echo 'ERROR: Could not find adodb on this system!';
}


global $htvcenter_DATABASE_TYPE, $htvcenter_DATABASE_USER;
$IMAGE_INFO_TABLE="image_info";
$DEPLOYMENT_INFO_TABLE="deployment_info";
$KERNEL_INFO_TABLE="kernel_info";
$RESOURCE_INFO_TABLE="resource_info";
$EVENT_INFO_TABLE="event_info";
$USER_INFO_TABLE="user_info";
$APPLIANCE_INFO_TABLE="appliance_info";
$VIRTUALIZATION_INFO_TABLE="virtualization_info";
$IMAGE_AUTHENTICATION_TABLE="image_authentication_info";
$STORAGE_INFO_TABLE="storage_info";
if ("$htvcenter_DATABASE_TYPE" == "db2") {
	$IMAGE_INFO_TABLE="$htvcenter_DATABASE_USER.$IMAGE_INFO_TABLE";
	$DEPLOYMENT_INFO_TABLE="$htvcenter_DATABASE_USER.$DEPLOYMENT_INFO_TABLE";
	$KERNEL_INFO_TABLE="$htvcenter_DATABASE_USER.$KERNEL_INFO_TABLE";
	$RESOURCE_INFO_TABLE="$htvcenter_DATABASE_USER.$RESOURCE_INFO_TABLE";
	$EVENT_INFO_TABLE="$htvcenter_DATABASE_USER.$EVENT_INFO_TABLE";
	$USER_INFO_TABLE="$htvcenter_DATABASE_USER.$USER_INFO_TABLE";
	$APPLIANCE_INFO_TABLE="$htvcenter_DATABASE_USER.$APPLIANCE_INFO_TABLE";
	$VIRTUALIZATION_INFO_TABLE="$htvcenter_DATABASE_USER.$VIRTUALIZATION_INFO_TABLE";
	$IMAGE_AUTHENTICATION_TABLE="$htvcenter_DATABASE_USER.$IMAGE_AUTHENTICATION_TABLE";
	$STORAGE_INFO_TABLE="$htvcenter_DATABASE_USER.$STORAGE_INFO_TABLE";

}

define('IMAGE_INFO_TABLE', $IMAGE_INFO_TABLE);
define('DEPLOYMENT_INFO_TABLE', $DEPLOYMENT_INFO_TABLE);
define('KERNEL_INFO_TABLE', $KERNEL_INFO_TABLE);
define('RESOURCE_INFO_TABLE', $RESOURCE_INFO_TABLE);
define('EVENT_INFO_TABLE', $EVENT_INFO_TABLE);
define('USER_INFO_TABLE', $USER_INFO_TABLE);
define('APPLIANCE_INFO_TABLE', $APPLIANCE_INFO_TABLE);
define('VIRTUALIZATION_INFO_TABLE', $VIRTUALIZATION_INFO_TABLE);
define('IMAGE_AUTHENTICATION_TABLE', $IMAGE_AUTHENTICATION_TABLE);
define('STORAGE_INFO_TABLE', $STORAGE_INFO_TABLE);

global $KERNEL_INFO_TABLE, $IMAGE_INFO_TABLE, $RESOURCE_INFO_TABLE, $EVENT_INFO_TABLE, $USER_INFO_TABLE, $DEPLOYMENT_INFO_TABLE, $APPLIANCE_INFO_TABLE, $STORAGE_INFO_TABLE, $VIRTUALIZATION_INFO_TABLE, $IMAGE_AUTHENTICATION_TABLE;


// returns a db-connection
function htvcenter_get_db_connection() {
	$htvcenter_db = new htvcenter_db();
	return $htvcenter_db;
}



class htvcenter_db {

	function __construct() {
		// to get lowercase column name form e.g. oracle
		global $htvcenter_DATABASE_TYPE;
		global $htvcenter_DATABASE_SERVER;
		global $htvcenter_DATABASE_NAME;
		global $htvcenter_DATABASE_USER;
		global $htvcenter_DATABASE_PASSWORD;

		if ("$htvcenter_DATABASE_TYPE" == "db2") {
			$db = ADONewConnection('odbc');
			$db->PConnect($htvcenter_DATABASE_NAME,$htvcenter_DATABASE_USER,$htvcenter_DATABASE_PASSWORD);
			$db->SetFetchMode(ADODB_FETCH_ASSOC);
			return $db;

		} else if ("$htvcenter_DATABASE_TYPE" == "oracle") {
			// we need to use the oci8po driver because it is the
			// only oracle driver supporting to set the column-names to lowercase
			// via define('ADODB_ASSOC_CASE',0);
			$db = NewADOConnection("oci8po");
			$db->Connect($htvcenter_DATABASE_NAME, $htvcenter_DATABASE_USER, $htvcenter_DATABASE_PASSWORD);

		} else {
			if (strlen($htvcenter_DATABASE_PASSWORD)) {
				$dsn = "$htvcenter_DATABASE_TYPE://$htvcenter_DATABASE_USER:$htvcenter_DATABASE_PASSWORD@$htvcenter_DATABASE_SERVER/$htvcenter_DATABASE_NAME?persist";
			} else {
				$dsn = "$htvcenter_DATABASE_TYPE://$htvcenter_DATABASE_USER@$htvcenter_DATABASE_SERVER/$htvcenter_DATABASE_NAME?persist";
			}
		$db = ADONewConnection($dsn);
		}

		// to get the column names in the resulting array
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->db = $db;

		$this->ha = false;

	}




	function get_ha_db() {
		// to get lowercase column name form e.g. oracle
		global $htvcenter_DATABASE_TYPE;
		global $htvcenter_DATABASE_SERVER;
		global $htvcenter_DATABASE_NAME;
		global $htvcenter_DATABASE_USER;
		global $htvcenter_DATABASE_PASSWORD;


// DEBUG

$htvcenter_HA_DATABASE_SERVER = "192.168.88.249";


		if ("$htvcenter_DATABASE_TYPE" == "db2") {
			$ha_db = ADONewConnection('odbc');
			$ha_db->PConnect($htvcenter_DATABASE_NAME,$htvcenter_DATABASE_USER,$htvcenter_DATABASE_PASSWORD);
			$ha_db->SetFetchMode(ADODB_FETCH_ASSOC);
			return $ha_db;

		} else if ("$htvcenter_DATABASE_TYPE" == "oracle") {
			// we need to use the oci8po driver because it is the
			// only oracle driver supporting to set the column-names to lowercase
			// via define('ADODB_ASSOC_CASE',0);
			$ha_db = NewADOConnection("oci8po");
			$ha_db->Connect($htvcenter_DATABASE_NAME, $htvcenter_DATABASE_USER, $htvcenter_DATABASE_PASSWORD);

		} else {
			if (strlen($htvcenter_DATABASE_PASSWORD)) {
				$ha_dsn = "$htvcenter_DATABASE_TYPE://$htvcenter_DATABASE_USER:$htvcenter_DATABASE_PASSWORD@$htvcenter_HA_DATABASE_SERVER/htvcenter?persist";
			} else {
				$ha_dsn = "$htvcenter_DATABASE_TYPE://$htvcenter_DATABASE_USER@$htvcenter_HA_DATABASE_SERVER/htvcenter?persist";
			}
		$ha_db = ADONewConnection($ha_dsn);
		}

		// to get the column names in the resulting array
		$ha_db->SetFetchMode(ADODB_FETCH_ASSOC);
		return $ha_db;
	}






	function GetAll($sql) {
		return $this->db->GetAll($sql);
	}

	function SelectLimit($sql, $limit = NULL, $offset = NULL) {
		return $this->db->SelectLimit($sql, $limit, $offset);
	}

	function SetFetchMode($mode) {
		$this->db->SetFetchMode($mode);
	}



	function Execute($sql) {
		$ret = $this->db->Execute($sql);
		if ($this->ha) {
			$ha_db = $this->get_ha_db();
			$ha_db->Execute($sql);
		}
		return $ret;
	}

	function AutoExecute($table, $fields, $mode, $clause = NULL) {
		if (isset($clause)) {
			$ret = $this->db->AutoExecute($table, $fields, $mode, $clause);
			if ($this->ha) {
				$ha_db = $this->get_ha_db();
				$ha_db->AutoExecute($table, $fields, $mode, $clause);
			}
			return $ret;
		} else {
			$ret = $this->db->AutoExecute($table, $fields, $mode);
			if ($this->ha) {
				$ha_db = $this->get_ha_db();
				$ha_db->AutoExecute($table, $fields, $mode);
			}
			return $ret;
		}
	}


	function Close() {
		$this->db->Close();
	}


	function ErrorMsg() {
		$this->db->ErrorMsg();
	}

}








// function to print arrays
function print_array($item, $key) {
	if (!is_int($key)) {
		echo "$key=\"$item\"\n";
	}
}

//-----------------------------------------------------------------------------------
function htvcenter_db_get_free_id($fieldname, $tablename) {

	$db=htvcenter_get_db_connection();
	$recordSet = $db->Execute("select $fieldname from $tablename");
	if (!$recordSet)
		print $db->ErrorMsg();
	else {
		$ar_ids = array();

		while ($arr = $recordSet->FetchRow()) {
		foreach($arr as $val) {
			$ar_ids[] = $val;
		}
		}

		$i=1;
		while($i > 0) {
			if(in_array($i, $ar_ids) == false) {
				return $i;
				break;
			}
		 $i++;
		}
	}
	$db->Close();
}
//-----------------------------------------------------------------------------------
function htvcenter_db_get_result($query) {
	$ar = array();
	$db = htvcenter_get_db_connection();
	$db->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $db->Execute($query);
	if(is_object($result)) {
		while ($arr = $result->FetchRow()) {
			$tmp = array();
			foreach ($arr as $key=>$val) {
				if(is_string($key)) {
					$tmp[] = array("value" => $val, "label" => $key);
				}
			}
			$ar[] = $tmp;
		}
	}
	return $ar;
}
//-----------------------------------------------------------------------------------
function htvcenter_db_get_result_single ($query) {
	$result = htvcenter_db_get_result($query);
	if(isset($result[0][0]["value"])) {
		return array("value" => $result[0][0]["value"], "label" => $result[0][0]["label"]);
	}
}
//-----------------------------------------------------------------------------------
function htvcenter_db_get_result_double ($query) {
	$ar_Return = array();
	$result = htvcenter_db_get_result($query);
	foreach ( $result as $res) {
		$ar_Return[] = array("value" => $res[0]["value"], "label" => $res[1]["value"]);
	}
	return $ar_Return;
}
?>
