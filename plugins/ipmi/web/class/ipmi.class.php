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
 * This class represents an ipmi object
 *
 * @package htvcenter
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class ipmi
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


/**
* id
* @access protected
* @var object
*/
var $id;

/**
* resource_id
* @access protected
* @var object
*/
var $resource_id;


/**
* resource_ipmi_ip
* @access protected
* @var object
*/
var $resource_ipmi_ip;

/**
* user
* @access protected
* @var object
*/
var $user;

/**
* pass
* @access protected
* @var object
*/
	var $pass;

/**
* comment
* @access protected
* @var object
*/
var $comment;


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $htvcenter_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = "ipmi";
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}


	//--------------------------------------------------
	/**
	* get an instance of an ipmiobject from db
	* @access public
	* @param int $id
	* @param string $resource_id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $resource_id) {
		$db=htvcenter_get_db_connection();
		if ("$id" != "") {
			$ipmi_array = $db->Execute("select * from $this->_db_table where ipmi_id=$id");
		} else if ("$resource_id" != "") {
			$ipmi_array = $db->Execute("select * from $this->_db_table where ipmi_resource_id='$resource_id'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", "Could not create instance of ipmi without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($ipmi_array as $index => $ipmi) {
			$this->id = $ipmi["ipmi_id"];
			$this->resource_id = $ipmi["ipmi_resource_id"];
			$this->resource_ipmi_ip = $ipmi["ipmi_resource_ipmi_ip"];
			$this->user = $ipmi["ipmi_user"];
			$this->pass = $ipmi["ipmi_pass"];
			$this->comment = $ipmi["ipmi_comment"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an ipmi by id
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an ipmi by name
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_resource_id($resource_id) {
		$this->get_instance("", $resource_id);
		return $this;
	}




	//--------------------------------------------------
	/**
	* add a new ipmi
	* @access public
	* @param array $ipmi_fields
	*/
	//--------------------------------------------------
	function add($ipmi_fields) {
		if (!is_array($ipmi_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", "Ipmi_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $ipmi_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", "Failed adding new ipmi to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an ipmi
	* <code>
	* $fields = array();
	* $fields['ipmi_name'] = 'somename';
	* $fields['ipmi_uri'] = 'some-uri';
	* $ipmi = new ipmi();
	* $ipmi->update(1, $fields);
	* </code>
	* @access public
	* @param int $ipmi_id
	* @param array $ipmi_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($ipmi_id, $ipmi_fields) {
		if ($ipmi_id < 0 || ! is_array($ipmi_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", "Unable to update ipmi $ipmi_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		unset($ipmi_fields["ipmi_id"]);
		$result = $db->AutoExecute($this->_db_table, $ipmi_fields, 'UPDATE', "ipmi_id = $ipmi_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", "Failed updating ipmi $ipmi_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an ipmi by id
	* @access public
	* @param int $ipmi_id
	*/
	//--------------------------------------------------
	function remove($ipmi_id) {
		// remove from db
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where ipmi_id=$ipmi_id");
	}

	//--------------------------------------------------
	/**
	* remove an ipmi by resource_id
	* @access public
	* @param string $resource_id
	*/
	//--------------------------------------------------
	function remove_by_resource_id($resource_id) {
		// remove from db
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where ipmi_resource_id='$resource_id'");

	}


	//--------------------------------------------------
	/**
	* get an array of all ipmi ids
	* <code>
	* $ipmi = new ipmi();
	* $arr = $ipmi->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$ipmi_array = array();
		$query = "select ipmi_id from $this->_db_table";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_ids", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$ipmi_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $ipmi_array;
	}

	//--------------------------------------------------
	/**
	* get number of ipmi accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select count(ipmi_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of ipmis
	* @access public
	* @param int $offset
	* @param int $limit
	* @param string $sort
	* @param enum $order [ASC/DESC]
	* @return array
	*/
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order) {

		echo "!! $offset, $limit, $sort, $order <br>";

		$db=htvcenter_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$ipmi_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "ipmi.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($ipmi_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $ipmi_array;
	}
















}
?>
