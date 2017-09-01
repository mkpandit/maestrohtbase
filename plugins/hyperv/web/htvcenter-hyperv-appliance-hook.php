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
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";


global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;



function htvcenter_hyperv_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $RootDir;

	$htvcenter_server = new htvcenter_server();

	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	// check appliance values, maybe we are in update and they are incomplete
	if ($appliance->imageid == 1) {
		return;
	}
	if (($resource->id == "-1") || ($resource->id == "") || (!isset($resource->vtype))) {
		return;
	}

	$event->log("htvcenter_hyperv_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	// check resource type -> hyperv-strorage-vm
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($resource->vtype);

	switch($virtualization->type) {
		case "hyperv":
			switch($cmd) {
				case "start":
					// send command to assign image and start vm
					$event->log("htvcenter_hyperv_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-appliance-hook.php", "Setting ".$appliance_id."/".$appliance_name."/".$appliance_ip." to localboot", "", "", 0, 0, $appliance_id);
					$hyperv_command = "htvcenter_server_set_boot local ".$resource->id." ".$resource->mac." ".$resource->ip;
					$htvcenter_server->send_command($hyperv_command, NULL, true);
					break;
				case "stop":
					// send command to stop the vm and deassign image
					$event->log("htvcenter_hyperv_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-appliance-hook.php", "Setting ".$appliance_id."/".$appliance_name."/".$appliance_ip." to networkboot", "", "", 0, 0, $appliance_id);
					$hyperv_command = "htvcenter_server_set_boot net ".$resource->id." ".$resource->mac." ".$resource->ip;
					$htvcenter_server->send_command($hyperv_command, NULL, true);
					break;
			}
			break;
		
		case "hyperv-vm-local":
			// check image is on the same storage server
			// get the citrix host resource
			$hyperv_host_resource = new resource();
			$hyperv_host_resource->get_instance_by_id($resource->vhostid);
			// get the hyperv resource
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);
			$image_root_device = explode('%', $image->rootdevice);
			$image_datastore = $image_root_device[0];
			$image_vmdk = str_replace(" ", "@", $image_root_device[1]);
			$storage = new storage();
			$storage->get_instance_by_id($image->storageid);
			$hyperv_resource = new resource();
			$hyperv_resource->get_instance_by_id($storage->resource_id);
			if ($hyperv_host_resource->id != $hyperv_resource->id) {
				$event->log("htvcenter_hyperv_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-appliance-hook.php", "Appliance $appliance_id image is not available on this hyperv host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
			}
			switch($cmd) {
				case "start":
					// send command to assign image and start vm
					$hyperv_command="$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm assign_by_mac -i ".$hyperv_host_resource->ip." -m ".$resource->mac." -d ".$image_vmdk." -l ".$image_datastore." --htvcenter-cmd-mode fork";
					$htvcenter_server->send_command($hyperv_command, NULL, true);
					break;
				case "stop":
					// send command to stop the vm and deassign image
					$hyperv_command="$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm deassign_by_mac -i ".$hyperv_host_resource->ip." -m ".$resource->mac." -d ".$image_vmdk." --htvcenter-cmd-mode fork";
					$htvcenter_server->send_command($hyperv_command, NULL, true);
					break;
			}
			break;
	}
}



?>


