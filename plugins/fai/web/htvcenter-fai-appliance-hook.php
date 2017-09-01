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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
require_once "$RootDir/plugins/fai/class/faistate.class.php";
require_once "$RootDir/plugins/fai/class/fairesource.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;


function htvcenter_fai_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	$htvcenter_server = new htvcenter_server();
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$resource_mac=$resource->mac;
	$resource_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	// check appliance values, maybe we are in update and they are incomplete
	if ($appliance->imageid == 1) {
		return;
	}
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}

	// check if image is type fai-deployment
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	// fai configured in image deployment parameters ?
	$fai_auto_install_enabled = false;
	$fai_deployment_parameters = trim($image->get_deployment_parameter("INSTALL_CONFIG"));
	if (strlen($fai_deployment_parameters)) {
		$fai_deployment_parameter_arr = explode(":", $fai_deployment_parameters);
		$local_deployment_persistent = $fai_deployment_parameter_arr[0];
		$local_deployment_type = $fai_deployment_parameter_arr[1];
		if (strcmp($local_deployment_type, "fai-deployment")) {
			$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance_name." image is not from type fai-deployment", "", "", 0, 0, $resource->id);
			return;
		}
		$fai_server_storage_id = $fai_deployment_parameter_arr[2];
		$fai_installation_profile_main = $fai_deployment_parameter_arr[3];
		$fai_installation_profile1 = $fai_deployment_parameter_arr[4];
		$fai_installation_profile2 = $fai_deployment_parameter_arr[5];
		$fai_installation_profile3 = $fai_deployment_parameter_arr[6];
		$fai_installation_profile4 = $fai_deployment_parameter_arr[7];
		$fai_installation_profile = "";
		if (strlen($fai_installation_profile_main)) {
			$fai_installation_profile = $fai_installation_profile_main;
		}
		if (strlen($fai_installation_profile1)) {
			$fai_installation_profile .= ",".$fai_installation_profile1;
		}
		if (strlen($fai_installation_profile2)) {
			$fai_installation_profile .= ",".$fai_installation_profile2;
		}
		if (strlen($fai_installation_profile3)) {
			$fai_installation_profile .= ",".$fai_installation_profile3;
		}
		if (strlen($fai_installation_profile4)) {
			$fai_installation_profile .= ",".$fai_installation_profile4;
		}
		$fai_auto_install_enabled = true;
	}


	$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":
			$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "START event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);

			if ($fai_auto_install_enabled) {
				// prepare automatic-installation

				// get the fai-server resource
				$fai_storage = new storage();
				$fai_storage->get_instance_by_id($fai_server_storage_id);
				$fai_server_resource = new resource();
				$fai_server_resource->get_instance_by_id($fai_storage->resource_id);

				// add client to fai server, get resource_id from image-deployment parameters, runs on Fai server
				$fai_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/fai/bin/htvcenter-fai add_fai_client -i ".$resource_ip." -x ".$resource->id." -m ".$resource_mac." -o ".$fai_installation_profile." -n ".$appliance->name;
				$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "add_fai_client $resource_ip", "", "", 0, 0, $resource->id);
				$fai_server_resource->send_command($fai_server_resource->ip, $fai_server_command);
				sleep(2);

				// transfer client to fai server, runs on htvcenter, we have to use the hostname resource+id and not the appliance-name since the resource is in the dhcpd.conf with this name
				$fai_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/fai/bin/htvcenter-fai-manager transfer_to_fai -o ".$fai_server_resource->ip." -i ".$resource_ip." -m ".$resource_mac." -n resource".$resource->id." --htvcenter-cmd-mode background";
				$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "transfer_to_fai $resource_ip", "", "", 0, 0, $resource->id);
				$htvcenter_server->send_command($fai_command);

				// Remove image-deployment paramters, if auto-install is a single-shot actions
				if (!strcmp($local_deployment_persistent, "0")) {
					$image->set_deployment_parameters("INSTALL_CONFIG", "");
				}

				// create fai-state object to allow to run a late setboot to local command on the vm host
				$fai_state = new faistate();
				$fai_state->remove_by_resource_id($resource->id);
				$fai_state_fields=array();
				$fai_state_fields["fai_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$fai_state_fields["fai_resource_id"]=$resource->id;
				$fai_state_fields["fai_install_start"]=$_SERVER['REQUEST_TIME'];
				$fai_state_fields["fai_timeout"]=$fai_install_timeout;
				$fai_state->add($fai_state_fields);

			} else {

				if (strcmp($image->type, "fai-deployment")) {
					$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type fai-deployment", "", "", 0, 0, $resource->id);
				} else {
					$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "Setting resource $resource_ip to local-boot", "", "", 0, 0, $resource->id);
					// we have auto-installed already, if it is VM the fairesource object will care to set the boot-sequence on the VM Host to local boot
					$fairesource = new fairesource();
					$fairesource->set_boot($resource->id, 1);
					// set pxe config to local-boot
					$fai_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/fai/bin/htvcenter-fai-manager set_fai_client_to_local_boot -m ".$resource_mac." --htvcenter-cmd-mode background";
					$htvcenter_server->send_command($fai_command);
				}
			}
			break;



		case "stop":

			if (strcmp($image->type, "fai-deployment")) {
				$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type fai-deployment", "", "", 0, 0, $resource->id);
			} else {
				$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "STOP event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
				// transfer client to htvcenter again
				$fai_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/fai/bin/htvcenter-fai-manager take_over_from_fai -i ".$resource_ip." -m ".$resource_mac." -n resource".$resource->id." --htvcenter-cmd-mode background";
				$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "take_over_from_fai $resource_ip", "", "", 0, 0, $resource->id);
				$htvcenter_server->send_command($fai_command);

				// remove fai-state object if existing
				$fai_state = new faistate();
				$fai_state->remove_by_resource_id($resource->id);
				// if it is VM the fairesource object will care to set the boot-sequence on the VM Host to network boot
				$fairesource = new fairesource();
				$fairesource->set_boot($resource->id, 0);

				// remove  client from fai server
	#			$fai_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/fai/bin/htvcenter-fai remove_fai_client -d ".$resource_domain." -n ".$appliance->name;
	#			$event->log("htvcenter_fai_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-appliance-hook.php", "remove_fai_client $resource_ip", "", "", 0, 0, $resource->id);
	#			$fai_server_resource->send_command($fai_server_resource->ip, $fai_server_command);
	#			break;
			}
	}


}


?>


