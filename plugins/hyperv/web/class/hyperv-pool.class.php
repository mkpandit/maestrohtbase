<?php
/**
 * Hyper-V Host pool
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


// This class represents a cloud user in htvcenter

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/event.class.php";

class hyperv_pool {

	var $hyperv_pool_id = '';
	var $hyperv_pool_name = '';
	var $hyperv_pool_path = '';
	var $hyperv_pool_comment = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $htvcenter_SERVER_BASE_DIR;
		$HYPERV_DISCOVERY_TABLE="hyperv_pool";
		$this->event = new event();
		$this->_db_table = $HYPERV_DISCOVERY_TABLE;
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a hyperv_pool object filled from the db
// ---------------------------------------------------------------------------------

	// returns an hyperv_pool object from the db selected by id, mac or ip
	function get_instance($id, $name) {

		$db=htvcenter_get_db_connection();
		if ("$id" != "") {
			$hyperv_pool_array = $db->Execute("select * from $this->_db_table where hyperv_pool_id=$id");
		} else if ("$name" != "") {
			$hyperv_pool_array = $db->Execute("select * from $this->_db_table where hyperv_pool_name='$name'");
		} else {
			$this->event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", "Could not create instance of hyperv_pool without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($hyperv_pool_array as $index => $hyperv_pool) {
			$this->hyperv_pool_id = $hyperv_pool["hyperv_pool_id"];
			$this->hyperv_pool_name = $hyperv_pool["hyperv_pool_name"];
			$this->hyperv_pool_path = $hyperv_pool["hyperv_pool_path"];
			$this->hyperv_pool_comment = $hyperv_pool["hyperv_pool_comment"];
		}
		return $this;
	}


	// returns an appliance from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	// returns an appliance from the db selected by name
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}


	// ---------------------------------------------------------------------------------
	// general hyperv_pool methods
	// ---------------------------------------------------------------------------------




	// checks if given hyperv_pool name is free in the db
	function is_name_free($hyperv_pool_name) {

		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select hyperv_pool_id from ".$this->_db_table." where hyperv_pool_name='".$hyperv_pool_name."'");
		if (!$rs)
			$this->event->log("is_name_free", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// checks if given hyperv_pool name is empty, no images belong to the pool any more
	function is_pool_empty($hyperv_pool_id) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select image_rootdevice from image_info where image_type='hyperv-deployment'");
		if (!$rs)
			$this->event->log("is_pool_empty", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$image_root_device_arr = explode('%', $rs->fields['image_rootdevice']);
			$image_pool_id = $image_root_device_arr[0];
			if ($image_pool_id == $hyperv_pool_id) {
				return false;
			}
			$rs->MoveNext();
		}
		return true;
	}

	

	// adds hyperv_pool to the database
	function add($hyperv_pool_fields) {

		if (!is_array($hyperv_pool_fields)) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", "hyperv_poolgroup_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		if (!isset($hyperv_pool_fields['hyperv_pool_id'])) {
			$hyperv_pool_fields['hyperv_pool_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $hyperv_pool_fields, 'INSERT');
		if (! $result) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", "Failed adding new hyperv_poolgroup to database", "", "", 0, 0, 0);
		}
	}


	// updates hyperv_pool in the database
	function update($hyperv_pool_id, $hyperv_pool_fields) {

		if (!is_array($hyperv_pool_fields)) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", "Unable to update hyperv_poolgroup $hyperv_pool_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		unset($hyperv_pool_fields["hyperv_pool_id"]);
		$result = $db->AutoExecute($this->_db_table, $hyperv_pool_fields, 'UPDATE', "hyperv_pool_id = $hyperv_pool_id");
		if (! $result) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", "Failed updating hyperv_poolgroup $hyperv_pool_id", "", "", 0, 0, 0);
		}
	}


	// removes hyperv_pool from the database
	function remove($hyperv_pool_id) {
		$this->get_instance_by_id($hyperv_pool_id);
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where hyperv_pool_id=$hyperv_pool_id");
	}

	// removes hyperv_pool from the database by hyperv_pool_name
	function remove_by_name($hyperv_pool_name) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where hyperv_pool_name='$hyperv_pool_name'");
	}


	// returns the number of hyperv_pools for an hyperv_pool type
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select count(hyperv_pool_id) as num from $this->_db_table");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all hyperv_pool ids
	function get_all_ids() {

		$hyperv_pool_list = array();
		$query = "select hyperv_pool_id from $this->_db_table";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$this->event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$hyperv_pool_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $hyperv_pool_list;

	}



	// displays the hyperv_pool-overview
	function display_overview($offset, $limit, $sort, $order) {

		$db=htvcenter_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$hyperv_pool_array = array();
		if (!$recordSet) {
			$this->event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "hyperv-pool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($hyperv_pool_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $hyperv_pool_array;
	}









// ---------------------------------------------------------------------------------

}

?>
