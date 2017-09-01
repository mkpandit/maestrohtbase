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


// This class represents a role-administration user in htvcenter

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/class/event.class.php";


class role_administration {

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct() {
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->event = new event();
	}

	//--------------------------------------------
	/**
	 * Get a list of role infos
	 *
	 * @access public
	 * @return array|null 
	 */
	//--------------------------------------------
	function get_role_infos() {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM role_info');
		if(isset($result->fields) && is_array($result->fields)) {
			while (!$result->EOF) {
				$fields[] = $result->fields;
				$result->MoveNext();
			}
			$result->Close();
			if(is_array($fields)) {
				return $fields;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Get role infos by role name
	 *
	 * @access public
	 * @param string $role_name
	 * @return array|null
	 */
	//--------------------------------------------
	function get_role_infos_by_name( $role_name ) {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM role_info where role_name = \''.$role_name.'\'');
		if(isset($result->fields) && is_array($result->fields)) {
			while (!$result->EOF) {
				$fields[] = $result->fields;
				$result->MoveNext();
			}
			$result->Close();
			if(is_array($fields)) {
				return $fields;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Get role infos by role id
	 *
	 * @access public
	 * @param integer $role_id
	 * @return array|null
	 */
	//--------------------------------------------
	function get_role_infos_by_id( $role_id ) {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM role_info where role_id = \''.$role_id.'\'');
		if(isset($result->fields) && is_array($result->fields)) {
			$return = $result->fields;
			$result->Close();
			return $return;
		}
	}

	//--------------------------------------------
	/**
	 * Remove role infos by role id
	 *
	 * @access public
	 * @param integer $role_id
	 * @return null
	 */
	//--------------------------------------------
	function remove_role_infos($role_id) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('delete from role_info where role_id = \''.$role_id.'\'');
	}

	//--------------------------------------------
	/**
	 * Insert role infos
	 *
	 * @access public
	 * @param array $fields
	 * @return false|null
	 */
	//--------------------------------------------
	function add_role_infos( $fields ) {
		if (!is_array($fields)) {
			$this->event->log("insert", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute('role_info', $fields, 'INSERT');
		if (! $result) {
			$this->event->log("insert", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Failed adding new role to database", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Update role infos
	 *
	 * @access public
	 * @param integer $role_id
	 * @param array $fields
	 * @return false|null
	 */
	//--------------------------------------------
	function update_role_infos( $role_id, $fields ) {
		if (!is_array($fields)) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute('role_info', $fields, 'UPDATE', 'role_id = '.$role_id);
		if (! $result) {
			$this->event->log("update", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Failed updating role", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * role to group
	 *
	 * @access public
	 * @param array $fields
	 * @param enum $mode select|insert|upate|delete
	 * @return false|null
	 */
	//--------------------------------------------
	function role2group( $fields, $mode ) {
		if (!is_array($fields)) {
			$this->event->log("role2group", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		switch($mode) {
			case 'select':
				if(isset($fields['role_id'])) {
					$result = $db->Execute('SELECT * FROM role_administration_role2group where role_id = \''.$fields['role_id'].'\'');
					if(isset($result->fields) && is_array($result->fields)) {
						while (!$result->EOF) {
							$field[] = $result->fields;
							$result->MoveNext();
						}
						$result->Close();
						if(is_array($field)) {
							return $field;
						}
					}
				}
			break;
			case 'insert':
				if(isset($fields['role_id']) && isset($fields['permission_group']) && is_array($fields['permission_group'])) {
					foreach($fields['permission_group'] as $group) {
						$values = array('role_id' => $fields['role_id'], 'permission_group_id' => $group);
						$result = $db->AutoExecute('role_administration_role2group', $values , 'INSERT');
					}
				}
			break;
			case 'update':
				// handle role to group
				if(isset($fields['role_id']) && isset($fields['permission_group']) && is_array($fields['permission_group'])) {
					// remove all old entries
					$rs = $db->Execute('delete from role_administration_role2group where role_id = \''.$fields['role_id'].'\'');
					foreach($fields['permission_group'] as $group) {
						$values = array('role_id' => $fields['role_id'], 'permission_group_id' => $group);
						$result = $db->AutoExecute('role_administration_role2group', $values , 'INSERT');
					}
				}
			break;
			case 'delete':
				// handle role to group
				if(isset($fields['role_id'])) {
					$result = $db->Execute('delete from role_administration_role2group where role_id = \''.$fields['role_id'].'\'');
				}
				// handle group to role
				else if(isset($fields['permission_group_id'])) {
					$result = $db->Execute('delete from role_administration_role2group where permission_group_id = \''.$fields['permission_group_id'].'\'');
				}
			break;
		}
		if (!isset($result)) {
			$this->event->log("role2group", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Failed adding role2group to database", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Get a list of permission groups
	 *
	 * @access public
	 * @return array|null
	 */
	//--------------------------------------------
	function get_permission_groups() {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM role_administration_permission_groups');
		if(isset($result->fields) && is_array($result->fields)) {
			while (!$result->EOF) {
				$fields[] = $result->fields;
				$result->MoveNext();
			}
			$result->Close();
			if(is_array($fields)) {
				return $fields;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Get permission groups by permission group name
	 *
	 * @access public
	 * @param string $permission_group_name
	 * @return array|null
	 */
	//--------------------------------------------
	function get_permission_groups_by_name( $permission_group_name ) {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM role_administration_permission_groups where permission_group_name = \''.$permission_group_name.'\'');
		if(isset($result->fields) && is_array($result->fields)) {
			while (!$result->EOF) {
				$fields[] = $result->fields;
				$result->MoveNext();
			}
			$result->Close();
			if(is_array($fields)) {
				return $fields;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Get permission groups by permission group id
	 *
	 * @access public
	 * @param string $permission_group_name
	 * @return array|null
	 */
	//--------------------------------------------
	function get_permission_groups_by_id( $permission_group_id ) {
		$db=htvcenter_get_db_connection();
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute('SELECT * FROM role_administration_permission_groups where permission_group_id = \''.$permission_group_id.'\'');
		if(isset($result->fields) && is_array($result->fields)) {
			$return = $result->fields;
			$result->Close();
			return $return;
		}
	}

	//--------------------------------------------
	/**
	 * Insert permission groups
	 *
	 * @access public
	 * @param array $fields
	 * @return false|null
	 */
	//--------------------------------------------
	function add_permission_groups( $fields ) {
		if (!is_array($fields)) {
			$this->event->log("insert", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute('role_administration_permission_groups', $fields, 'INSERT');
		if (! $result) {
			$this->event->log("insert", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Failed adding new permission group to database", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Update permission groups
	 *
	 * @access public
	 * @param string $group_id
	 * @param array $fields
	 * @return false|null
	 */
	//--------------------------------------------
	function update_permission_groups( $group_id, $fields ) {
		if (!is_array($fields)) {
			$this->event->log("insert", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute('role_administration_permission_groups', $fields, 'UPDATE', 'permission_group_id = '.$group_id);
		if (! $result) {
			$this->event->log("insert", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Failed updating permission group in database", "", "", 0, 0, 0);
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Remove permission groups by permission group id
	 *
	 * @access public
	 * @param integer $permission_group_id
	 * @return null
	 */
	//--------------------------------------------
	function remove_permission_groups($permission_group_id) {
		$db=htvcenter_get_db_connection();
		$rs = $db->Execute('delete from role_administration_permission_groups where permission_group_id = \''.$permission_group_id.'\'');
	}

	//--------------------------------------------
	/**
	 * Permissions
	 *
	 * @access public
	 * @param array $fields
	 * @param enum $mode select|insert|delete
	 * @return false|null
	 */
	//--------------------------------------------
	function permissions( $fields, $mode ) {
		if (!is_array($fields)) {
			$this->event->log("permissions", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return false;
		}
		$db=htvcenter_get_db_connection();
		switch($mode) {
			case 'select':
				if(isset($fields['permission_group_id'])) {
					$sql  = 'SELECT * FROM role_administration_permissions WHERE';
					$sql .= ' permission_group_id = \''.$fields['permission_group_id'].'\'';
					if(isset($fields['permission_controller'])) {
						$sql .= ' AND permission_controller = \''.$fields['permission_controller'].'\'';
					}
					$result = $db->Execute($sql);
					if(isset($result->fields) && is_array($result->fields)) {
						while (!$result->EOF) {
							$field[$result->fields['permission_controller']] = explode(',', $result->fields['permission_actions']);
							$result->MoveNext();
						}
						$result->Close();
						if(is_array($field)) {
							return $field;
						}
					}
				}
			break;
			case 'insert':
				if(isset($fields['permission_group_id'])) {
					$result = $db->AutoExecute('role_administration_permissions', $fields , 'INSERT');
				}
			break;
			case 'delete':
				// handle role to group
				if(isset($fields['permission_group_id'])) {
					$result = $db->Execute('delete from role_administration_permissions where permission_group_id = \''.$fields['permission_group_id'].'\'');
				}
			break;
		}
		if (!isset($result)) {
			$this->event->log("permissions", $_SERVER['REQUEST_TIME'], 2, "role-administration.class.php", "Failed adding permissions to database", "", "", 0, 0, 0);
			return false;
		}
	}


}
