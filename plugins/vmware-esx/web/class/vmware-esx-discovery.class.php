<?php
/**
 * ESX Host discovery
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

class vmware_esx_discovery {

	var $vmw_esx_ad_id = '';
	var $vmw_esx_ad_ip = '';
	var $vmw_esx_ad_mac = '';
	var $vmw_esx_ad_hostname = '';
	var $vmw_esx_ad_user = '';
	var $vmw_esx_ad_password = '';
	var $vmw_esx_ad_comment = '';
	var $vmw_esx_ad_is_integrated = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $htvcenter_SERVER_BASE_DIR;
		$VMWARE_ESX_DISCOVERY_TABLE="vmw_esx_auto_discovery";
		$this->event = new event();
		$this->_db_table = $VMWARE_ESX_DISCOVERY_TABLE;
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a vmware_esx_discovery object filled from the db
// ---------------------------------------------------------------------------------

	// returns an vmware_esx_discovery object from the db selected by id, mac or ip
	function get_instance($id, $mac, $ip) {

		$db=htvcenter_get_db_connection();
		if ("$id" != "") {
			$vmware_esx_discovery_array = $db->Execute("select * from $this->_db_table where vmw_esx_ad_id=$id");
		} else if ("$mac" != "") {
			$vmware_esx_discovery_array = $db->Execute("select * from $this->_db_table where vmw_esx_ad_mac='$mac'");
		} else if ("$ip" != "") {
			$vmware_esx_discovery_array = $db->Execute("select * from $this->_db_table where vmw_esx_ad_ip='$ip'");
		} else {
			$this->event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Could not create instance of vmware_esx_discovery without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($vmware_esx_discovery_array as $index => $vmware_esx_discovery) {
			$this->vmw_esx_ad_id = $vmware_esx_discovery["vmw_esx_ad_id"];
			$this->vmw_esx_ad_ip = $vmware_esx_discovery["vmw_esx_ad_ip"];
			$this->vmw_esx_ad_mac = $vmware_esx_discovery["vmw_esx_ad_mac"];
			$this->vmw_esx_ad_hostname = $vmware_esx_discovery["vmw_esx_ad_hostname"];
			$this->vmw_esx_ad_user = $vmware_esx_discovery["vmw_esx_ad_user"];
			$this->vmw_esx_ad_password = $vmware_esx_discovery["vmw_esx_ad_password"];
			$this->vmw_esx_ad_comment = $vmware_esx_discovery["vmw_esx_ad_comment"];
			$this->vmw_esx_ad_is_integrated = $vmware_esx_discovery["vmw_esx_ad_is_integrated"];
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
	// general vmware_esx_discovery methods
	// ---------------------------------------------------------------------------------




	// checks if given vmware_esx_discovery id is free in the db
	function is_id_free($vmware_esx_discovery_id) {

		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select vmw_esx_ad_id from $this->_db_table where vmw_esx_ad_id=$vmware_esx_discovery_id");
		if (!$rs)
			$this->event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// checks if given vmware_esx_discovery mac is already in the db
	function mac_discoverd_already($vmware_esx_discovery_mac) {

		$db=htvcenter_get_db_connection();

		$rs = $db->Execute("select vmw_esx_ad_id from $this->_db_table where vmw_esx_ad_mac='$vmware_esx_discovery_mac'");
		if (!$rs)
			$this->event->log("mac_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			$resource = new resource();
			$resource->get_instance_by_mac($vmware_esx_discovery_mac);
			if ($resource->id > 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	// checks if given vmware_esx_discovery ip is already in the db
	function ip_discoverd_already($vmware_esx_discovery_ip) {

		$db=htvcenter_get_db_connection();

		$rs = $db->Execute("select vmw_esx_ad_id from $this->_db_table where vmw_esx_ad_ip='$vmware_esx_discovery_ip'");
		if (!$rs)
			$this->event->log("ip_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			$resource = new resource();
			$resource->get_instance_by_ip($vmware_esx_discovery_ip);
			if ($resource->id > 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}


	// adds vmware_esx_discovery to the database
	function add($vmware_esx_discovery_fields) {

		if (!is_array($vmware_esx_discovery_fields)) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "vmware_esx_discoverygroup_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		if (!isset($vmware_esx_discovery_fields['vmw_esx_ad_id'])) {
			$vmware_esx_discovery_fields['vmw_esx_ad_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $vmware_esx_discovery_fields, 'INSERT');
		if (! $result) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Failed adding new vmware_esx_discoverygroup to database", "", "", 0, 0, 0);
		}
	}


	// updates vmware_esx_discovery in the database
	function update($vmware_esx_discovery_id, $vmware_esx_discovery_fields) {

		if (!is_array($vmware_esx_discovery_fields)) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Unable to update vmware_esx_discoverygroup $vmware_esx_discovery_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		unset($vmware_esx_discovery_fields["vmw_esx_ad_id"]);
		$result = $db->AutoExecute($this->_db_table, $vmware_esx_discovery_fields, 'UPDATE', "vmw_esx_ad_id = $vmware_esx_discovery_id");
		if (! $result) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Failed updating vmware_esx_discoverygroup $vmware_esx_discovery_id", "", "", 0, 0, 0);
		}
	}


	// removes vmware_esx_discovery from the database
	function remove($vmware_esx_discovery_id) {
		$this->get_instance_by_id($vmware_esx_discovery_id);
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where vmw_esx_ad_id=$vmware_esx_discovery_id");
	}

	// removes vmware_esx_discovery from the database by vmware_esx_discovery_mac
	function remove_by_name($vmware_esx_discovery_mac) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where vmw_esx_ad_mac='$vmware_esx_discovery_mac'");
	}


	// returns the number of vmware_esx_discoverys for an vmware_esx_discovery type
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select count(vmw_esx_ad_mac) as num from $this->_db_table");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all vmware_esx_discovery ids
	function get_all_ids() {

		$vmware_esx_discovery_list = array();
		$query = "select vmw_esx_ad_mac from $this->_db_table";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$this->event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$vmware_esx_discovery_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $vmware_esx_discovery_list;

	}



	// displays the vmware_esx_discovery-overview
	function display_overview($offset, $limit, $sort, $order) {

		$db=htvcenter_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$vmware_esx_discovery_array = array();
		if (!$recordSet) {
			$this->event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($vmware_esx_discovery_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $vmware_esx_discovery_array;
	}









// ---------------------------------------------------------------------------------

}

?>
