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
 * This class represents an ip_mgmt object
 *
 * @package htvcenter
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class ip_mgmt
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
		global $htvcenter_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = "ip_mgmt";
		$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an ip_mgmt object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($mode, $value) {
		$db    = htvcenter_get_db_connection();
		$array = '';
		switch($mode) {
			case 'id':
				$array = $db->Execute("select * from $this->_db_table where ip_mgmt_id='$value'");
			break;
			case 'name';
				$array = $db->Execute("select * from $this->_db_table where ip_mgmt_name='$value'");
			break;
			case 'ip':
				$array = $db->Execute("select * from $this->_db_table where ip_mgmt_address='$value'");
			break;
			default:
				$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", "Could not create instance of ip_mgmt without data", "", "", 0, 0, 0);
			break;
		}

		if(isset($array->fields)) {
			return $array->fields;
		} else {
			return false;
		}
	}

	//--------------------------------------------------
	/**
	* get an instance of an ip_mgmt by id
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_id($id) {
		$this->get_instance('id', $id);
		return $this;
	}

	//--------------------------------------------------
	/**
	* add a new ip_mgmt
	* @access public
	* @param array $ip_mgmt_fields
	*/
	//--------------------------------------------------
	function add($ip_mgmt_fields) {
		//$ip_mgmt_fields['ip_mgmt_id'] = $this->get_free_id('ip_mgmt_id', $this->_db_table);
		if (!is_array($ip_mgmt_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", "ip_mgmt_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// create id
		$ip_mgmt_fields['ip_mgmt_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $ip_mgmt_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", "Failed adding new ip_mgmt to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an ip_mgmt
	* <code>
	* $fields = array();
	* $fields['ip_mgmt_name'] = 'somename';
	* $fields['ip_mgmt_uri'] = 'some-uri';
	* $ip_mgmt = new ip_mgmt();
	* $ip_mgmt->update(1, $fields);
	* </code>
	* @access public
	* @param int $ip_mgmt_id
	* @param array $ip_mgmt_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($ip_mgmt_name, $ip_mgmt_fields) {
		if ($ip_mgmt_name === '' || ! is_array($ip_mgmt_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", "Unable to update ip_mgmt $ip_mgmt_name", "", "", 0, 0, 0);
			return 1;
		}
		$db=htvcenter_get_db_connection();
		unset($ip_mgmt_fields["ip_mgmt_name"]);
		$result = $db->AutoExecute($this->_db_table, $ip_mgmt_fields, 'UPDATE', "ip_mgmt_name = '$ip_mgmt_name'");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", "Failed updating ip_mgmt $ip_mgmt_name", "", "", 0, 0, 0);
		}
	}


	function update_ip($id, $fields) {
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $fields, 'UPDATE', "ip_mgmt_id = $id");
	}


	//--------------------------------------------------
	/**
	* remove an ip_mgmt by id
	* @access public
	* @param int $ip_mgmt_id
	*/
	//--------------------------------------------------
	function remove($ip_mgmt_id) {
		// remove from db
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where ip_mgmt_id=$ip_mgmt_id");
	}

	//--------------------------------------------------
	/**
	* remove an ip_mgmt by name
	* @access public
	* @param string $ip_mgmt_name
	*/
	//--------------------------------------------------
	function remove_by_name($ip_mgmt_name) {
		// remove from db
		$ret = "";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where ip_mgmt_name='$ip_mgmt_name'");
		return $ret;
	}




	//--------------------------------------------------
	/**
	* set ip_mgmt state by id
	* @access public
	* @param int $ip_mgmt_id
	* @param int $ip_mgmt_state
	* @param string $ip_mgmt_token
	* @return string
	*/
	//--------------------------------------------------
	function set_state($ip_mgmt_id, $ip_mgmt_state, $ip_mgmt_token) {
		$db = htvcenter_get_db_connection();
		$ip_mgmt_state = $db->Execute("update $this->_db_table set ip_mgmt_state=$ip_mgmt_state, ip_mgmt_token='$ip_mgmt_token' where ip_mgmt_id=$ip_mgmt_id");
	}

	//--------------------------------------------------
	/**
	* get ip_mgmt name by id
	* @access public
	* @param int $ip_mgmt_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($ip_mgmt_id) {
		$db = htvcenter_get_db_connection();
		$ip_mgmt_set = $db->Execute("select ip_mgmt_name from $this->_db_table where ip_mgmt_id=$ip_mgmt_id");
		if (!$ip_mgmt_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$ip_mgmt_set->EOF) {
				return $ip_mgmt_set->fields["ip_mgmt_name"];
			} else {
				return "idle";
			}
		}
	}


	function get_names( $name = null ) {
		$db  = htvcenter_get_db_connection();
		$arr = array();
		$set = $db->Execute("select ip_mgmt_name from $this->_db_table");
		if (!$set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while(!$set->EOF) {
				$arr[] = $set->fields["ip_mgmt_name"];
				$set->MoveNext();
			}
			return array_unique($arr);
		}
	}

	//--------------------------------------------------
	/**
	* get an array of all ip_mgmt names
	* <code>
	* $ip_mgmt = new ip_mgmt();
	* $arr = $ip_mgmt->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list( $name = null ) {
		$result = null;
		$db=htvcenter_get_db_connection();
		if(isset($name)) {
			$names = array($name);
		} else {
			$names = $this->get_names( $name );
		}
		foreach($names as $name) {
			$recordSet = $db->SelectLimit("SELECT * FROM $this->_db_table WHERE ip_mgmt_name='$name' order by ip_mgmt_id", -1, -1);
			$ip_mgmt_array = array();
			if (!$recordSet) {
				$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
			} else {
				while (!$recordSet->EOF) {
					array_push($ip_mgmt_array, $recordSet->fields);
					$recordSet->MoveNext();
				}
			$recordSet->Close();
			}
			$first = array_shift($ip_mgmt_array);
			$last  = array_pop($ip_mgmt_array);
			$result[$name] = array('first' => $first, 'last' => $last);
		}
		return $result;
	}

	//--------------------------------------------------
	/**
	* get an array of all ip_mgmt ids
	* <code>
	* $ip_mgmt = new ip_mgmt();
	* $arr = $ip_mgmt->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$ip_mgmt_array = array();
		$query = "select ip_mgmt_id from $this->_db_table";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$ip_mgmt_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $ip_mgmt_array;
	}

	//--------------------------------------------------
	/**
	* get number of ip_mgmt accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute("select count(ip_mgmt_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	function get_ips($appliance_id, $idsonly = false) {
		$db = htvcenter_get_db_connection();
		$array = array();
		$rs = $db->Execute("select ip_mgmt_id, ip_mgmt_name, ip_mgmt_address from $this->_db_table WHERE ip_mgmt_appliance_id IS NULL OR ip_mgmt_appliance_id='$appliance_id'");
		if(isset($rs->fields)) {
			while (!$rs->EOF) {
				if($idsonly === false) {
					$array[] = array( 'id' => $rs->fields['ip_mgmt_id'], 'ip' => $rs->fields['ip_mgmt_name'].' - '. $rs->fields['ip_mgmt_address']);
				}
				if($idsonly === true) {
					$array[] = $rs->fields['ip_mgmt_id'];
				}
				$rs->MoveNext();
			}
			return $array;
		} else {
			return false;
		}
	}

	function get_ips_by_name($ip_mgmt_name) {
		$db = htvcenter_get_db_connection();
		$array = array();
		$rs = $db->Execute("select ip_mgmt_id from $this->_db_table WHERE ip_mgmt_name='$ip_mgmt_name'");
		if(isset($rs->fields)) {
			while (!$rs->EOF) {
				$array[] = $rs->fields['ip_mgmt_id'];
				$rs->MoveNext();
			}
			return $array;
		} else {
			return false;
		}
	}



	function get_id_by_appliance($appliance_id, $nic) {
		$db = htvcenter_get_db_connection();
		$array = array();
		$rs = $db->Execute("select ip_mgmt_id from $this->_db_table WHERE ip_mgmt_appliance_id='$appliance_id' AND ip_mgmt_nic_id='$nic'");
		if(isset($rs->fields)) {
			return $rs->fields['ip_mgmt_id'];
		} else {
			return false;
		}
	}


	function get_id_by_appliance_and_token($appliance_id, $appliance_token, $nic) {
		$db = htvcenter_get_db_connection();
		$array = array();
		$rs = $db->Execute("select ip_mgmt_id from $this->_db_table WHERE ip_mgmt_appliance_id='$appliance_id' AND ip_mgmt_token='$appliance_token' AND ip_mgmt_state='1' AND ip_mgmt_nic_id='$nic'");
		if(isset($rs->fields)) {
			return $rs->fields['ip_mgmt_id'];
		} else {
			return false;
		}
	}


	// all ids for one user
	function get_list_by_user($ip_mgmt_user_id) {
		$db = htvcenter_get_db_connection();
		$array = array();
		$rs = $db->Execute("select ip_mgmt_id, ip_mgmt_name, ip_mgmt_address from $this->_db_table WHERE ip_mgmt_user_id=$ip_mgmt_user_id AND ip_mgmt_appliance_id IS NULL");
		if(isset($rs->fields)) {
			while (!$rs->EOF) {
				array_push($array, $rs->fields);
				$rs->MoveNext();
			}
			return $array;
		} else {
			return false;
		}
	}

	//--------------------------------------------------
	/**
	* get an array of the complete config for one id
	* <code>
	* $ip_mgmt = new ip_mgmt();
	* $arr = $ip_mgmt->get_ids($id);
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_config_by_id($id) {
		$ip_mgmt_array = array();
		$query = "select * from $this->_db_table where ip_mgmt_id='$id'";
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_config_by_id", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$ip_mgmt_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $ip_mgmt_array;
	}

	//--------------------------------------------------
	/**
	* get an array of ip_mgmts
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
		$recordSet = $db->SelectLimit("SELECT * FROM $this->_db_table ORDER BY $sort $order", $limit, $offset);
		$ip_mgmt_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "ip_mgmt.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($ip_mgmt_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $ip_mgmt_array;
	}



/*

	// why not using the generic database function provided by htvcenter ?
	// guess there is no need to have this function here
	function get_free_id($fieldname, $tablename) {

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
*/


	//--------------------------------------------------
	/**
	* Check IP exists in DB
	* @access public
	* @param string $ip
	* @return mixed array|false
	*/
	//--------------------------------------------------
	function ip_exists( $ip ) {
		$db  = htvcenter_get_db_connection();
		$arr = array();
		$set = $db->Execute('SELECT * FROM '.$this->_db_table.' WHERE ip_mgmt_address=\''.$ip.'\'');
		if ($set) {
			while(!$set->EOF) {
				$arr[] = $set->fields;
				$set->MoveNext();
			}
			return $arr;
		} else {
			return false;
		}
	}

	//--------------------------------------------------
	/**
	* Ip Adress to binary
	* @access public
	* @param string $ip
	* @return string
	*/
	//--------------------------------------------------
	function ip2bin($ip)
	{
		$return = '';
		if(!preg_match("/^\d+\.\d+\.\d+\.\d+$/", $ip)) return -1;
		$ar = explode(".", $ip);
		foreach($ar as $a)
		{
			$return .= str_pad(decbin($a), 8, 0, STR_PAD_LEFT);
		}
		return $return;
	}

	//--------------------------------------------------
	/**
	* Binary to Ip Adress
	* @access public
	* @param string $bit
	* @return string
	*/
	//--------------------------------------------------
	function bin2ip($bit)
	{
		if(!preg_match("/^([0-9]{8})([0-9]{8})([0-9]{8})([0-9]{8})$/", $bit, $matches)) return -1;
		return sprintf("%s.%s.%s.%s", bindec($matches[1]), bindec($matches[2]), bindec($matches[3]), bindec($matches[4]));
	}

	//--------------------------------------------------
	/**
	* Broadcast adress
	* @access public
	* @param string $ip
	* @param string $mask
	* @return string
	*/
	//--------------------------------------------------
	function broadcast($ip, $mask)
	{
		$return = '';
		$bit = $this->ip2bin($ip);
		$bitmask = $this->ip2bin($mask);
		for($i = 0; $i < 32; $i++)
		{
			if($bitmask{$i} == '1')
				$return .= $bit{$i};
			else
				$return .= '1';
		}
		return $this->bin2ip($return);
	}

	//--------------------------------------------------
	/**
	* Network Adress
	* @access public
	* @param string $ip
	* @param string $mask
	* @return string
	*/
	//--------------------------------------------------
	function network($ip, $mask)
	{
		$ret = '';
		$bitip = $this->ip2bin($ip);
		$bitmask = $this->ip2bin($mask);
		for($i = 0; $i < 32; $i++)
		{
			if($bitmask{$i} == '1')
				$ret .= $bitip{$i};
			else
				$ret .= '0';
		}
		return $this->bin2ip($ret);
	}


}
?>
