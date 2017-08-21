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


// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/plugins/opsi/storage';

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "opsi-action", "Un-Authorized access to opsi-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$opsi_command = $request->get('opsi_command');

// main
$event->log("$opsi_command", $_SERVER['REQUEST_TIME'], 5, "opsi-action", "Processing opsi command $opsi_command", "", "", 0, 0, 0);
switch ($opsi_command) {

	case 'init':
		// create opsi_state
		// -> opsi_state
		// opsi_id BIGINT
		// opsi_resource_id BIGINT
		// opsi_install_start VARCHAR(20)
		// opsi_timeout BIGINT
		$create_opsi_state = "create table opsi_state(opsi_id BIGINT, opsi_resource_id BIGINT, opsi_install_start VARCHAR(20), opsi_timeout BIGINT)";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_opsi_state);
		// -> opsi_volumes
		// opsi_volume_id BIGINT
		// opsi_volume_name VARCHAR(50)
		// opsi_volume_size VARCHAR(50)
		// opsi_volume_description VARCHAR(255)
		$create_opsi_volume_table = "create table opsi_volumes(opsi_volume_id BIGINT, opsi_volume_name VARCHAR(50), opsi_volume_root VARCHAR(50), opsi_volume_description VARCHAR(255))";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_opsi_volume_table);
		break;

	case 'uninstall':
		// remove opsi_resource
		$remove_opsi_state = "drop table opsi_state;";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($remove_opsi_state);
		// remove volume table
		$drop_opsi_volume_table = "drop table opsi_volumes";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_opsi_volume_table);
		break;



	case 'get_netboot_products':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;



	default:
		$event->log("$opsi_command", $_SERVER['REQUEST_TIME'], 3, "opsi-action", "No such event command ($opsi_command)", "", "", 0, 0, 0);
		break;


}

?>
