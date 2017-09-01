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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function htvcenter_template_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	// check appliance values, maybe we are in update and they are incomplete
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}


	$event->log("htvcenter_new_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-template-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
	switch($cmd) {
		case "start":
			$htvcenter_server = new htvcenter_server();
			$htvcenter_server->send_command($htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template/bin/htvcenter-template-manager start ".$appliance_id." ".$appliance_name." ".$appliance_ip." --htvcenter-cmd-mode background");
			break;
		case "stop":
			$htvcenter_server = new htvcenter_server();
			$htvcenter_server->send_command($htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template/bin/htvcenter-template-manager stop ".$appliance_id." ".$appliance_name." ".$appliance_ip." --htvcenter-cmd-mode background");
			break;
	}
}



?>


