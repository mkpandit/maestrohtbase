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


// this hook allows to run custom actions when user activates themselves

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-fortis/';
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/image.class.php";
require_once $RootDir."/class/image_authentication.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/virtualization.class.php";
require_once $RootDir."/class/appliance.class.php";
require_once $RootDir."/class/storage.class.php";
require_once $RootDir."/class/deployment.class.php";
require_once $RootDir."/class/htvcenter_server.class.php";
require_once $RootDir."/class/event.class.php";
// special cloud classes
require_once $RootDir."/plugins/cloud/class/clouduser.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

function htvcenter_cloud_user($cloud_user_id, $action) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $htvcenter_server;
	global $BaseDir;
	global $RootDir;

	$cloud_user = new clouduser();
	$cloud_user->get_instance_by_id($cloud_user_id);
	if (!strlen($cloud_user->name)) {
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "zones-user-hook", "No such Cloud User with ID ".$cloud_user_id, "", "", 0, 0, 0);
		return;
	}

	switch($action) {
		case 'activate':
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "zones-user-hook", "Activating Cloud User ".$cloud_user->name, "", "", 0, 0, 0);

			
			
			
			break;
	}





}


?>