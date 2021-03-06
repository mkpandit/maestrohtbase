<?php
/**
 * Hyper-V Host discovery
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

class hyperv_discovery {

	var $hyperv_ad_id = '';
	var $hyperv_ad_ip = '';
	var $hyperv_ad_mac = '';
	var $hyperv_ad_hostname = '';
	var $hyperv_ad_user = '';
	var $hyperv_ad_password = '';
	var $hyperv_ad_comment = '';
	var $hyperv_ad_is_integrated = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $htvcenter_SERVER_BASE_DIR;
		$HYPERV_DISCOVERY_TABLE="hyperv_auto_discovery";
		$this->event = new event();
		$this->_db_table = $HYPERV_DISCOVERY_TABLE;
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a hyperv_discovery object filled from the db
// ---------------------------------------------------------------------------------

	// returns an hyperv_discovery object from the db selected by id, mac or ip
	function get_instance($id, $mac, $ip) {

		$db=htvcenter_get_db_connection();
		if ("$id" != "") {
			$hyperv_discovery_array = $db->Execute("select * from $this->_db_table where hyperv_ad_id=$id");
		} else if ("$mac" != "") {
			$hyperv_discovery_array = $db->Execute("select * from $this->_db_table where hyperv_ad_mac='$mac'");
		} else if ("$ip" != "") {
			$hyperv_discovery_array = $db->Execute("select * from $this->_db_table where hyperv_ad_ip='$ip'");
		} else {
			$this->event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", "Could not create instance of hyperv_discovery without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($hyperv_discovery_array as $index => $hyperv_discovery) {
			$this->hyperv_ad_id = $hyperv_discovery["hyperv_ad_id"];
			$this->hyperv_ad_ip = $hyperv_discovery["hyperv_ad_ip"];
			$this->hyperv_ad_mac = $hyperv_discovery["hyperv_ad_mac"];
			$this->hyperv_ad_hostname = $hyperv_discovery["hyperv_ad_hostname"];
			$this->hyperv_ad_user = $hyperv_discovery["hyperv_ad_user"];
			$this->hyperv_ad_password = $hyperv_discovery["hyperv_ad_password"];
			$this->hyperv_ad_comment = $hyperv_discovery["hyperv_ad_comment"];
			$this->hyperv_ad_is_integrated = $hyperv_discovery["hyperv_ad_is_integrated"];
		}
		return $this;
	}


	// returns an appliance from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "", "");
		return $this;
	}

	// returns an appliance from the db selected by mac
	function get_instance_by_mac($mac) {
		$this->get_instance("", $mac, "");
		return $this;
	}

	// returns an appliance from the db selected by ip
	function get_instance_by_ip($ip) {
		$this->get_instance("", "", $ip);
		return $this;
	}


	// ---------------------------------------------------------------------------------
	// general hyperv_discovery methods
	// ---------------------------------------------------------------------------------




	// checks if given hyperv_discovery id is free in the db
	function is_id_free($hyperv_discovery_id) {

		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select hyperv_ad_id from $this->_db_table where hyperv_ad_id=$hyperv_discovery_id");
		if (!$rs)
			$this->event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// checks if given hyperv_discovery mac is already in the db
	function mac_discoverd_already($hyperv_discovery_mac) {

		$db=htvcenter_get_db_connection();

		$rs = $db->Execute("select hyperv_ad_id from $this->_db_table where hyperv_ad_mac='$hyperv_discovery_mac'");
		if (!$rs)
			$this->event->log("mac_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			$resource = new resource();
			$resource->get_instance_by_mac($hyperv_discovery_mac);
			if ($resource->id > 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	// checks if given hyperv_discovery ip is already in the db
	function ip_discoverd_already($hyperv_discovery_ip) {

		$db=htvcenter_get_db_connection();

		$rs = $db->Execute("select hyperv_ad_id from $this->_db_table where hyperv_ad_ip='$hyperv_discovery_ip'");
		if (!$rs)
			$this->event->log("ip_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			$resource = new resource();
			$resource->get_instance_by_ip($hyperv_discovery_ip);
			if ($resource->id > 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}


	// adds hyperv_discovery to the database
	function add($hyperv_discovery_fields) {

		if (!is_array($hyperv_discovery_fields)) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", "hyperv_discoverygroup_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		if (!isset($hyperv_discovery_fields['hyperv_ad_id'])) {
			$hyperv_discovery_fields['hyperv_ad_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $hyperv_discovery_fields, 'INSERT');
		if (! $result) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", "Failed adding new hyperv_discoverygroup to database", "", "", 0, 0, 0);
		}
	}


	// updates hyperv_discovery in the database
	function update($hyperv_discovery_id, $hyperv_discovery_fields) {

		if (!is_array($hyperv_discovery_fields)) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", "Unable to update hyperv_discoverygroup $hyperv_discovery_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		unset($hyperv_discovery_fields["hyperv_ad_id"]);
		$result = $db->AutoExecute($this->_db_table, $hyperv_discovery_fields, 'UPDATE', "hyperv_ad_id = $hyperv_discovery_id");
		if (! $result) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", "Failed updating hyperv_discoverygroup $hyperv_discovery_id", "", "", 0, 0, 0);
		}
	}


	// removes hyperv_discovery from the database
	function remove($hyperv_discovery_id) {
		$this->get_instance_by_id($hyperv_discovery_id);
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where hyperv_ad_id=$hyperv_discovery_id");
	}

	// removes hyperv_discovery from the database by hyperv_discovery_mac
	function remove_by_name($hyperv_discovery_mac) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where hyperv_ad_mac='$hyperv_discovery_mac'");
	}


	// returns the number of hyperv_discoverys for an hyperv_discovery type
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select count(hyperv_ad_mac) as num from $this->_db_table");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all hyperv_discovery ids
	function get_all_ids() {

		$hyperv_discovery_list = array();
		$query = "select hyperv_ad_mac from $this->_db_table";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$this->event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$hyperv_discovery_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $hyperv_discovery_list;

	}



	// displays the hyperv_discovery-overview
	function display_overview($offset, $limit, $sort, $order) {

		$db=htvcenter_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$hyperv_discovery_array = array();
		if (!$recordSet) {
			$this->event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "hyperv-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($hyperv_discovery_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $hyperv_discovery_array;
	}









// ---------------------------------------------------------------------------------

}

?>
