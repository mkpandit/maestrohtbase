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
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "wakeuponlan-action", "Un-Authorized access to wakeuponlan-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$wakeuponlan_command = $request->get('wakeuponlan_command');

// main
$event->log("$wakeuponlan_command", $_SERVER['REQUEST_TIME'], 5, "wakeuponlan-action", "Processing wakeuponlan command $wakeuponlan_command", "", "", 0, 0, 0);
switch ($wakeuponlan_command) {

	case 'init':
		// this command creates the following table
		// -> wakeuponlan
		// wakeuponlan_id BIGINT
		// wakeuponlan_token VARCHAR(50)
		// wakeuponlan_name VARCHAR(50)
		// wakeuponlan_user_id BIGINT
		// wakeuponlan_appliance_id BIGINT
		// wakeuponlan_nic_id BIGINT
		// wakeuponlan_state BIGINT
		// wakeuponlan_network VARCHAR(50)
		// wakeuponlan_address VARCHAR(50)
		// wakeuponlan_subnet VARCHAR(50)
		// wakeuponlan_broadcast VARCHAR(50)
		// wakeuponlan_gateway VARCHAR(50)
		// wakeuponlan_dns1 VARCHAR(50)
		// wakeuponlan_dns2 VARCHAR(50)
		// wakeuponlan_domain VARCHAR(255)
		// wakeuponlan_vlan_id VARCHAR(50)
		// wakeuponlan_vlan1 VARCHAR(50)
		// wakeuponlan_vlan2 VARCHAR(50)
		// wakeuponlan_vlan3 VARCHAR(50)
		// wakeuponlan_vlan4 VARCHAR(50)
		// wakeuponlan_comment VARCHAR(255)

		$create_wakeuponlan_table = "create table wakeuponlan(wakeuponlan_id BIGINT, wakeuponlan_token VARCHAR(255), wakeuponlan_name VARCHAR(50), wakeuponlan_user_id BIGINT, wakeuponlan_appliance_id BIGINT, wakeuponlan_nic_id BIGINT, wakeuponlan_state BIGINT, wakeuponlan_network VARCHAR(50), wakeuponlan_address VARCHAR(50), wakeuponlan_subnet VARCHAR(50), wakeuponlan_broadcast VARCHAR(50), wakeuponlan_gateway VARCHAR(50), wakeuponlan_dns1 VARCHAR(50), wakeuponlan_dns2 VARCHAR(50), wakeuponlan_domain VARCHAR(255), wakeuponlan_vlan_id VARCHAR(50), wakeuponlan_vlan1 VARCHAR(50), wakeuponlan_vlan2 VARCHAR(50), wakeuponlan_vlan3 VARCHAR(50), wakeuponlan_vlan4 VARCHAR(50), wakeuponlan_comment VARCHAR(255))";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_wakeuponlan_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_wakeuponlan_table = "drop table wakeuponlan";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_wakeuponlan_table);
		$db->Close();
		break;


	default:
		$event->log("$wakeuponlan_command", $_SERVER['REQUEST_TIME'], 3, "wakeuponlan-action", "No such event command ($wakeuponlan_command)", "", "", 0, 0, 0);
		break;


}

?>
