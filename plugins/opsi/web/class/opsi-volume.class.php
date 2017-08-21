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
 * This class represents an opsi_volume object
 *
 * @package htvcenter
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class opsi_volume
{

/**
* opsi_volume id
* @access protected
* @var int
*/
var $id = '';
/**
* opsi_volume name
* @access protected
* @var string
*/
var $name = '';
/**
* opsi_volume root
* @access protected
* @var string
*/
var $root = '';
/**
* opsi_volume description
* @access protected
* @var string
*/
var $description = '';


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
	function opsi_volume() {
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
		$this->_db_table = "opsi_volumes";
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an opsi_volume object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$opsi_volume_array = array();
		$db=htvcenter_get_db_connection();
		if ("$id" != "") {
			$opsi_volume_array = $db->Execute("select * from ".$this->_db_table." where opsi_volume_id=".$id);
		} else if ("$name" != "") {
			$opsi_volume_array = $db->Execute("select * from ".$this->_db_table." where opsi_volume_name='".$name."'");
		}
		foreach ($opsi_volume_array as $index => $opsi_volume) {
			$this->id = $opsi_volume["opsi_volume_id"];
			$this->name = $opsi_volume["opsi_volume_name"];
			$this->root = $opsi_volume["opsi_volume_root"];
			$this->description = $opsi_volume["opsi_volume_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an opsi_volume by id
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
	* get an instance of an opsi_volume by name
	* @access public
	* @param int $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}



	//--------------------------------------------------
	/**
	* add a new opsi_volume
	* @access public
	* @param array $opsi_volume_fields
	*/
	//--------------------------------------------------
	function add($opsi_volume_fields) {
		if (!is_array($opsi_volume_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $opsi_volume_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", "Failed adding new opsi_volume to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an opsi_volume
	* <code>
	* $fields = array();
	* $fields['opsi_volume_name'] = 'somename';
	* $fields['opsi_volume_uri'] = 'some-uri';
	* $opsi_volume = new opsi_volume();
	* $opsi_volume->update(1, $fields);
	* </code>
	* @access public
	* @param int $opsi_volume_id
	* @param array $opsi_volume_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($opsi_volume_id, $opsi_volume_fields) {
		if ($opsi_volume_id < 0 || ! is_array($opsi_volume_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", "Unable to update opsi_volume $opsi_volume_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		unset($opsi_volume_fields["opsi_volume_id"]);
		$result = $db->AutoExecute($this->_db_table, $opsi_volume_fields, 'UPDATE', "opsi_volume_id = $opsi_volume_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", "Failed updating opsi_volume $opsi_volume_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an opsi_volume by id
	* @access public
	* @param int $opsi_volume_id
	*/
	//--------------------------------------------------
	function remove($opsi_volume_id) {
		// remove from db
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where opsi_volume_id=".$opsi_volume_id);
	}

	//--------------------------------------------------
	/**
	* remove an opsi_volume by name
	* @access public
	* @param string $opsi_volume_name
	*/
	//--------------------------------------------------
	function remove_by_name($opsi_volume_name) {
		// remove from db
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where opsi_volume_name='".$opsi_volume_name."'");

	}


	//--------------------------------------------------
	/**
	* get opsi_volume name by id
	* @access public
	* @param int $opsi_volume_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($opsi_volume_id) {
		$db=htvcenter_get_db_connection();
		$opsi_volume_set = $db->Execute("select opsi_volume_name from ".$this->_db_table." where opsi_volume_id=".$opsi_volume_id);
		if (!$opsi_volume_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$opsi_volume_set->EOF) {
				return $opsi_volume_set->fields["opsi_volume_name"];
			} else {
				return "not found";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all opsi_volume names
	* <code>
	* $opsi_volume = new opsi_volume();
	* $arr = $opsi_volume->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select opsi_volume_id, opsi_volume_name from ".$this->_db_table." order by opsi_volume_id ASC";
		$opsi_volume_name_array = array();
		$opsi_volume_name_array = htvcenter_db_get_result_double ($query);
		return $opsi_volume_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all opsi_volume ids
	* <code>
	* $opsi_volume = new opsi_volume();
	* $arr = $opsi_volume->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$opsi_volume_array = array();
		$query = "select opsi_volume_id from ".$this->_db_table;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$opsi_volume_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $opsi_volume_array;
	}

	//--------------------------------------------------
	/**
	* get number of opsi_volume accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select count(opsi_volume_id) as num from ".$this->_db_table);
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of opsi_volumes
	* @access public
	* @param int $offset
	* @param int $limit
	* @param string $sort
	* @param enum $order [ASC/DESC]
	* @return array
	*/
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order) {
		$db=htvcenter_get_db_connection();
		$recordSet = $db->SelectLimit("select * from ".$this->_db_table." order by $sort $order", $limit, $offset);
		$opsi_volume_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "opsi-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($opsi_volume_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $opsi_volume_array;
	}


}
?>
