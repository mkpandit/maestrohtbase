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

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "ipmi-action", "Un-Authorized access to ipmi-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$ipmi_command = $request->get('ipmi_command');

// main
$event->log("$ipmi_command", $_SERVER['REQUEST_TIME'], 5, "ipmi-action", "Processing ipmi command $ipmi_command", "", "", 0, 0, 0);
switch ($ipmi_command) {

	case 'init':
		// this command creates the following table
		// -> ipmi_locations
		// ipmi_id BIGINT
		// ipmi_resource_id BIGINT
		// ipmi_resource_ipmi_ip VARCHAR(50)
		// ipmi_user VARCHAR(50)
		// ipmi_pass VARCHAR(50)
		// ipmi_comment VARCHAR(255)

		$create_ipmi_table = "create table ipmi(ipmi_id BIGINT, ipmi_resource_id BIGINT, ipmi_resource_ipmi_ip VARCHAR(50), ipmi_user VARCHAR(50), ipmi_pass VARCHAR(50), ipmi_comment VARCHAR(255))";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_ipmi_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_ipmi_table = "drop table ipmi";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_ipmi_table);
		$db->Close();
		break;


	default:
		$event->log("$ipmi_command", $_SERVER['REQUEST_TIME'], 3, "ipmi-action", "No such event command ($ipmi_command)", "", "", 0, 0, 0);
		break;


}

?>
