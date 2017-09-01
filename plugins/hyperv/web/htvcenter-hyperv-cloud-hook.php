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


// This file implements the virtual machine abstraction in the cloud of htvcenter

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;

global $htvcenter_SERVER_BASE_DIR;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;

// ---------------------------------------------------------------------------------
// general hyperv cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm from a specificed virtualization type + parameters
function create_hyperv_vm($host_resource_id, $name, $mac, $memory, $cpu, $disk, $additional_nic_str, $vm_type, $vncpassword, $source_image_id=null) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	$hyperv_mac_address_space = "00:50:56";
	$swap = "1048576";

	global $event;
	$event->log("create_hyperv_vm", $_SERVER['REQUEST_TIME'], 5, "hyperv-cloud-hook", "Creating Hyper-V VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_mac($mac);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();

	// send command to create vm
	if ($vm_type == "hyperv-vm-local") {
		$vm_create_cmd  = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm create";
		$vm_create_cmd .= " -i ".$host_resource->ip;
		$vm_create_cmd .= " -n ".$name;
		$vm_create_cmd .= " -m ".$mac;
		$vm_create_cmd .= " -r ".$memory;
		$vm_create_cmd .= " -c ".$cpu;
		$vm_create_cmd .= " -b local";
		$vm_create_cmd .= " --existing-vhd none";
		$vm_create_cmd .= " ".$additional_nic_str;
		$vm_create_cmd .= " -vmtype hyperv-vm-local";
		$vm_create_cmd .= " --htvcenter-cmd-mode fork";
	} else if ($vm_type == "hyperv-vm-net") {
		$vm_create_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm create";
		$vm_create_cmd .= " -i ".$host_resource->ip;
		$vm_create_cmd .= " -n ".$name;
		$vm_create_cmd .= " -m ".$mac;
		$vm_create_cmd .= " -r ".$memory;
		$vm_create_cmd .= " -c ".$cpu;
		$vm_create_cmd .= " -b net";
		$vm_create_cmd .= " -d ".$swap;
		$vm_create_cmd .= " ".$additional_nic_str;
		$vm_create_cmd .= " -vmtype hyperv-vm-net";
		$vm_create_cmd .= " --htvcenter-cmd-mode fork";
	} else {
		$event->log("create_hyperv_vm", $_SERVER['REQUEST_TIME'], 2, "hyperv-cloud-hook", "Can not create VM $name on Host resource $host_resource_id. Unknown type $vm_type.", "", "", 0, 0, 0);
		return;
	}
	$event->log("create_hyperv_vm", $_SERVER['REQUEST_TIME'], 5, "hyperv-cloud-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_create_cmd, NULL, true);
}



// removes a cloud vm
function remove_hyperv_vm($host_resource_id, $name, $mac, $vm_type) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// remove the vm from host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("remove_hyperv_vm", $_SERVER['REQUEST_TIME'], 5, "hyperv-cloud-hook", "Removing Hyper-V VM $name/$mac from Host $host_resource_id", "", "", 0, 0, 0);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// send command to create the vm on the host
	$vm_remove_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm remove -i ".$host_resource->ip." -n ".$name." --htvcenter-cmd-mode fork";
	$event->log("remove_hyperv_vm", $_SERVER['REQUEST_TIME'], 5, "hyperv-cloud-hook", "Running $vm_remove_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_remove_cmd, NULL, true);

}



// Cloud hook methods

function create_hyperv_vm_local($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id=null) {
	global $event;
	create_hyperv_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "hyperv-vm-local", $vncpassword, $source_image_id);
}

function create_hyperv_vm_net($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id) {
	global $event;
	create_hyperv_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "hyperv-vm-net", $vncpassword, $source_image_id=null);
}

function remove_hyperv_vm_local($host_resource_id, $name, $mac) {
	global $event;
	remove_hyperv_vm($host_resource_id, $name, $mac, "hyperv-vm-local");
}

function remove_hyperv_vm_net($host_resource_id, $name, $mac) {
	global $event;
	remove_hyperv_vm($host_resource_id, $name, $mac, "hyperv-vm-net");
}



// ---------------------------------------------------------------------------------


?>
