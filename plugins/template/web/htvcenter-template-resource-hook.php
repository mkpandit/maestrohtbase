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
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function htvcenter_template_resource($cmd, $resource_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	$resource_id=$resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$resource_ip=$resource->ip;
	$resource_name=$resource->hostname;

	$event->log("htvcenter_template_resource", $_SERVER['REQUEST_TIME'], 5, "htvcenter-template-resource-hook.php", "Handling $cmd event $resource_id/$resource_name/$resource_ip", "", "", 0, 0, $resource_id);

	// we do only care if we serving an appliance
	$appliance = new appliance();
	$appliance_record_set = array();
	$appliance_id_array = array();
	$appliance_record_set = $appliance->get_all_ids();
	// the appliance_array from getlist is a 2-dimensional array
	foreach ($appliance_record_set as $index => $appliance_id_array) {

		foreach ($appliance_id_array as $index => $id) {
			$tapp = new appliance();
			$tapp->get_instance_by_id($id);
			$tapp_state = $tapp->state;
			$tapp_resources = $tapp->resources;

			if (!strcmp($tapp_state, "active")) {

				if ($tapp_resources == $resource_id) {
					// we found the resources active appliance, running the cmd

					$appliance_name = $tapp->name;
					switch($cmd) {
						case "start":
							$htvcenter_server = new htvcenter_server();
							$htvcenter_server->send_command($htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template/bin/htvcenter-template-manager start ".$id." ".$appliance_name." ".$resource_ip." --htvcenter-cmd-mode background");
							break;
						case "stop":
							$htvcenter_server = new htvcenter_server();
							$htvcenter_server->send_command($htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template/bin/htvcenter-template-manager stop ".$id." ".$appliance_name." ".$resource_ip." --htvcenter-cmd-mode background");
							break;
					}
				}
			}
		}
	}
}



?>


