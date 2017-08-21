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


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$role_administration_command = $request->get('role_administration_command');
$role_administration_domain = $request->get('role_administration_domain');

global $htvcenter_SERVER_BASE_DIR;
$refresh_delay=5;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "role-administration-action", "Un-Authorized access to role-administration-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}


// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}



// main
$event->log("$role_administration_command", $_SERVER['REQUEST_TIME'], 5, "role-administration-action", "Processing role-administration command $role_administration_command", "", "", 0, 0, 0);

	switch ($role_administration_command) {

		case 'init':
			$sql  = 'create table role_administration_role2group(';
			$sql .= ' role_id BIGINT,';
			$sql .= ' permission_group_id BIGINT';
			$sql .= ')';
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($sql);
			$db->Close();
			$sql  = 'create table role_administration_permission_groups(';
			$sql .= ' permission_group_id BIGINT,';
			$sql .= ' permission_group_name VARCHAR(50),';
			$sql .= ' permission_group_comment VARCHAR(255)';
			$sql .= ')';
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($sql);
			$db->Close();
			$sql  = 'create table role_administration_permissions(';
			$sql .= ' permission_group_id BIGINT,';
			$sql .= ' permission_controller VARCHAR(50),';
			$sql .= ' permission_actions VARCHAR(512)';
			$sql .= ')';
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($sql);
			$db->Close();
		break;

		case 'uninstall':
			$sql = "drop table role_administration_role2group";
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($sql);
			$db->Close();
			$sql = "drop table role_administration_permission_groups";
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($sql);
			$db->Close();
			$sql = "drop table role_administration_permissions";
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($sql);
			$db->Close();
		break;

		default:
			$event->log("$role_administration_command", $_SERVER['REQUEST_TIME'], 3, "role_administration-action", "No such event command ($role_administration_command)", "", "", 0, 0, 0);
			break;

	}

?>
