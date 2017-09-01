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

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;


function htvcenter_ip_mgmt_external_dns_hook($cmd, $appliance_id, $appliance_external_ip) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RootDir;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	$resource = new resource();
	$resource->get_instance_by_id($appliance->resources);

	switch($cmd) {
		case "start":
			$event->log("htvcenter_ip_mgmt_external_dns_hook", $_SERVER['REQUEST_TIME'], 5, "htvcenter_ip_mgmt_external_dns_hook.php", "Handling ".$cmd." event ".$appliance_id, "", "", 0, 0, $appliance_id);
			$htvcenter_server = new htvcenter_server();

			// please notice !!
			// the external dns-hook is implemented as NOOP
			// to use it please create a script/tool on the htvcenter server to update $appliance->name with $appliance_external_ip on your external dns server
			// and run it here to add the hostname + ip to your external dns:w
			// $htvcenter_server->send_command($htvcenter_SERVER_BASE_DIR."/htvcenter/your-custom-dns-update-script start ".$appliance_id." ".$appliance->name." ".$appliance_external_ip." --htvcenter-cmd-mode background");
			// If you external dns server provides an API to udpate the dns records please feel free to use it directly from php
			break;
		case "stop":
			$event->log("htvcenter_ip_mgmt_external_dns_hook", $_SERVER['REQUEST_TIME'], 5, "htvcenter_ip_mgmt_external_dns_hook.php", "Handling ".$cmd." event ".$appliance_id, "", "", 0, 0, $appliance_id);
			$htvcenter_server = new htvcenter_server();
			// please notice !!
			// the external dns-hook is implemented as NOOP
			// to use it please create a script/tool on the htvcenter server to remove $appliance->name with $appliance_external_ip on your external dns server
			// and run it here to remove the hostname + ip to your external dns:w
			// $htvcenter_server->send_command($htvcenter_SERVER_BASE_DIR."/htvcenter/your-custom-dns-update-script stop ".$appliance_id." ".$appliance->name." ".$appliance_external_ip." --htvcenter-cmd-mode background");
			break;
	}
}


?>


