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
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
// ip mgmt class
require_once "$RootDir/plugins/ipmi/class/ipmi.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;


function htvcenter_ipmi_fence_resource($resource_id) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $htvcenter_server;
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);

	// check if ipmi is configured
	$ipmi = new ipmi();
	$ipmi->get_instance_by_resource_id($resource_id);
	if (!strlen($ipmi->id)) {
		$event->log("htvcenter_ipmi_fence_resource", $_SERVER['REQUEST_TIME'], 2, "htvcenter-ipmi-resource-fence-hook.php", "IPMI is not configured for resource $resource_id. Cannot fence!", "", "", 0, 0, $resource_id);
		return;
	}
	$resource_can_start_from_off = $resource->get_resource_capabilities("SFO");
	if ($resource_can_start_from_off != 1) {
		$event->log("htvcenter_ipmi_fence_resource", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ipmi-resource-fence-hook.php", "IPMI is not enabled for resource $resource_id. Cannot fence!", "", "", 0, 0, $resource_id);
		return;
	}

	$event->log("htvcenter_ipmi_fence_resource", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ipmi-resource-fence-hook.php", "Fencing resource $resource_id !", "", "", 0, 0, $resource_id);
	$ipmi_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/ipmi/bin/htvcenter-ipmi sleep -i ".$ipmi->resource_ipmi_ip." -u ".$ipmi->user." -p ".$ipmi->pass." --htvcenter-cmd-mode background";
	$htvcenter_server->send_command($ipmi_command);

}


?>

