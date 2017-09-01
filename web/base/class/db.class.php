<?php
/**
 * db Class
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class db
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter $htvcenter
	 */
	//--------------------------------------------
	function __construct($htvcenter) {
		$this->htvcenter = $htvcenter;
		if (file_exists('/usr/share/php/adodb/adodb.inc.php')) {
			require_once('/usr/share/php/adodb/adodb.inc.php');
		} 
		else if (file_exists($this->htvcenter->get('basedir').'/include/adodb/adodb.inc.php')) {
			require_once($this->htvcenter->get('basedir').'/include/adodb/adodb.inc.php');
		}
		else if (file_exists('/usr/share/adodb/adodb.inc.php')) {
			require_once('/usr/share/adodb/adodb.inc.php');
		} else {
			echo 'ERROR: Could not find adodb on this system!';
		}
		if (!defined("ADODB_ASSOC_CASE")) {
			define('ADODB_ASSOC_CASE',0);
		}
		if ($this->htvcenter->get('config', 'ORACLE_HOME'))  {
			putenv('LD_LIBRARY_PATH='.$this->htvcenter->get('config', 'LD_LIBRARY_PATH'));
			putenv('ORACLE_HOME='.$this->htvcenter->get('config', 'ORACLE_HOME'));
			putenv('TNS_ADMIN='.$this->htvcenter->get('config', 'TNS_ADMIN'));
		}
	}

	//--------------------------------------------
	/**
	 * Connect to database
	 *
	 * @access public
	 * @return dbobject
	 */
	//--------------------------------------------
	function connect() {
		if ($this->htvcenter->get('config', 'DATABASE_TYPE') === "db2") {
			$db = &ADONewConnection('odbc');
			$db->PConnect(
					$this->htvcenter->get('config', 'DATABASE_NAME'),
					$this->htvcenter->get('config', 'DATABASE_USER'),
					$this->htvcenter->get('config', 'DATABASE_PASSWORD')
				);
			$db->SetFetchMode(ADODB_FETCH_ASSOC);
			return $db;
		} else if ($this->htvcenter->get('config', 'DATABASE_TYPE') === "oracle") {
			$db = NewADOConnection("oci8po");
			$db->Connect(
					$this->htvcenter->get('config', 'DATABASE_NAME'),
					$this->htvcenter->get('config', 'DATABASE_USER'),
					$this->htvcenter->get('config', 'DATABASE_PASSWORD')
				);
		} else {
			if (strlen($this->htvcenter->get('config', 'DATABASE_PASSWORD'))) {
				$dsn  = $this->htvcenter->get('config', 'DATABASE_TYPE').'://';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_USER').':';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_PASSWORD').'@';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_SERVER').'/';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_NAME').'?persist';
			} else {
				$dsn  = $this->htvcenter->get('config', 'DATABASE_TYPE').'://';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_USER').'@';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_SERVER').'/';
				$dsn .= $this->htvcenter->get('config', 'DATABASE_NAME').'?persist';
			}
			$db = &ADONewConnection($dsn);
		}
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		return $db;
	}

	//--------------------------------------------
	/**
	 * Get a free id from a table
	 *
	 * @access public
	 * @param string $fieldname
	 * @param string $tablename
	 * @return int
	 */
	//--------------------------------------------
	function get_free_id($fieldname, $tablename) {
		$db = $this->connect();
		$recordSet = $db->Execute("select $fieldname from $tablename");
		if (!$recordSet) {
			print $db->ErrorMsg();
			$db->Close();
			exit(0);
		} else {
			$ids = array();
			while ($arr = $recordSet->FetchRow()) {
				foreach($arr as $val) {
					$ids[] = $val;
				}
			}
			$i = 1;
			while($i > 0) {
				if(in_array($i, $ids) == false) {
					$db->Close();
					return $i;
					break;
				}
				$i++;
			}
		}
	}

}
?>
