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

$network_manager_command = $request->get('network_manager_command');

global $htvcenter_SERVER_BASE_DIR;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// place for the storage stat files
$device_statdir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/plugins/network-manager/storage';


// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "network-manager-action", "Un-Authorized access to network-manager-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}


// main
$event->log("$network_manager_command", $_SERVER['REQUEST_TIME'], 5, "network-manager-action", "Processing network-manager command $network_manager_command", "", "", 0, 0, 0);

	switch ($network_manager_command) {

		case 'get_network_config':
			if (!file_exists($device_statdir)) {
				mkdir($device_statdir);
			}
			$filename = $device_statdir."/".$_POST['filename'];
			$filedata = base64_decode($_POST['filedata']);
			echo "<h1>$filename</h1>";
			$fout = fopen($filename,"wb");
			fwrite($fout, $filedata);
			fclose($fout);
			break;


		default:
			$event->log("$network_manager_command", $_SERVER['REQUEST_TIME'], 3, "network-manager-action", "No such event command ($network_manager_command)", "", "", 0, 0, 0);
			break;


	}


?>
