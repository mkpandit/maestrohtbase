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
// general vmware-esx cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm from a specificed virtualization type + parameters
function create_ha_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $disk, $additional_nic_str, $vm_type, $origin_resource_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$vmware_mac_address_space = "00:50:56";
	$swap = "1048576";

	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Creating VMware ESX VM $name on Host $host_resource_ip", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// get the new vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_mac($mac);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// set the vnc access
	$image = new image();
	$vncpassword = $image->generatePassword(8);
	$vnc_port = $vm_resource->generate_vnc_port($host_resource_id);
	$resource_fields["resource_vnc"] = $vnc_port;
	$vm_resource->update_info($vm_resource->id, $resource_fields);
	$vncpassword_parameter = " -va ".$vncpassword." -vp ".$vnc_port;
	
	if ($vm_type == "vmware-esx-vm-local") {
		$vm_create_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm create -i ".$host_resource->ip." -n ".$name." -m ".$mac." -r ".$memory." -c ".$cpu." -b local --existing-vmdk none ".$additional_nic_str." ".$vncpassword_parameter." -vmtype vmware-esx-vm-local --htvcenter-cmd-mode background";
	} else if ($vm_type == "vmware-esx-vm-net") {
		$vm_create_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm create -i ".$host_resource->ip." -n ".$name." -m ".$mac." -r ".$memory." -c ".$cpu." -b net -d ".$swap." ".$additional_nic_str." ".$vncpassword_parameter." -vmtype vmware-esx-vm-net --htvcenter-cmd-mode background";
	} else {
		$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-ha-hook", "Do not know how to create VMware ESX VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
		return;
	}
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_create_cmd, NULL, true);
}



// fences a vm
function fence_ha_vmware_esx_vm($host_resource_id, $mac, $vm_type) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// fences the vm on its host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("fence_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Fencing VMware ESX VM $mac from Host $host_resource_id", "", "", 0, 0, 0);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// send command to fence the vm on the host
	$vm_fence_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm fence -i ".$host_resource->ip." -m ".$mac." --htvcenter-cmd-mode background";
	$event->log("fence_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Running $vm_fence_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_fence_cmd, NULL, true);
}


// HA hook methods

function create_vmware_esx_vm_local($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $origin_resource_id) {
	create_ha_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "vmware-esx-vm-local", $origin_resource_id);
}

function create_vmware_esx_vm_net($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $origin_resource_id) {
	create_ha_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "vmware-esx-vm-net", $origin_resource_id);
}

function fence_vmware_esx_vm_local($host_resource_id, $mac) {
	fence_ha_vmware_esx_vm($host_resource_id, $mac, "vmware-esx-vm-local");
}

function fence_vmware_esx_vm_net($host_resource_id, $mac) {
	fence_ha_vmware_esx_vm($host_resource_id, $mac, "vmware-esx-vm-net");
}



// ---------------------------------------------------------------------------------


?>
