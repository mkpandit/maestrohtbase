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
function create_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $disk, $additional_nic_str, $vm_type, $vncpassword, $source_image_id=null) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	$vmware_mac_address_space = "00:50:56";
	$swap = "1048576";

	global $event;
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Creating VMware ESX VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_mac($mac);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// get guest_id from source image
	$image = new image();
	$guest_id = '';
	$datastore = '';
	if(isset($source_image_id) && $source_image_id !== '') {
		$image->get_instance_by_id($source_image_id);
		if($image->type ===  'esx-deployment') {
			if(isset($image->capabilities) && strpos($image->capabilities, 'TYPE=') !== false) {
				$guest_id = str_replace('TYPE=', '',$image->capabilities);
			}
			$datastore = substr($image->rootdevice, 0, strpos($image->rootdevice,':'));
		}
	} else {
		$error = '';
		foreach(debug_backtrace() as $key => $msg) {
			if($key === 1 || $key === 2) {
				$error .= '( '.basename($msg['file']).' '.$msg['line'].' )';
			}
		}
		$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-cloud-hook", "Missing source_image_id. $error", "", "", 0, 0, 0);
	}
	// set the vnc access
	$vncpassword_parameter = "";
	if ($vncpassword == '') {
		$vncpassword = $image->generatePassword(8);
	}
	// create the vnc port
	if ($vm_resource->vnc == '') {
		$vnc_port = $vm_resource->generate_vnc_port($host_resource_id);
		$resource_fields["resource_vname"] = $name;
		$resource_fields["resource_vnc"] = $vnc_port;
		$vm_resource->update_info($vm_resource->id, $resource_fields);
	} else {
		$vnc_port = $vm_resource->vnc;
	}
	$vncpassword_parameter = " -va ".$vncpassword." -vp ".$vnc_port;

	// send command to create vm
	if ($vm_type == "vmware-esx-vm-local") {
		$vm_create_cmd  = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm create";
		$vm_create_cmd .= " -i ".$host_resource->ip;
		$vm_create_cmd .= " -n ".$name;
		$vm_create_cmd .= " -m ".$mac;
		$vm_create_cmd .= " -r ".$memory;
		$vm_create_cmd .= " -c ".$cpu;
		$vm_create_cmd .= " -b local";
		$vm_create_cmd .= " --existing-vmdk none";
		$vm_create_cmd .= " ".$additional_nic_str;
		$vm_create_cmd .= " ".$vncpassword_parameter;
		$vm_create_cmd .= " -vmtype vmware-esx-vm-local";
		$vm_create_cmd .= " --htvcenter-cmd-mode background";
		if($guest_id !== '') {
			$vm_create_cmd .= " --guest-id ".$guest_id;
		}
		if($datastore !== '') {
			$vm_create_cmd .= " -l ".$datastore;
		}
	} else if ($vm_type == "vmware-esx-vm-net") {
		$vm_create_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm create";
		$vm_create_cmd .= " -i ".$host_resource->ip;
		$vm_create_cmd .= " -n ".$name;
		$vm_create_cmd .= " -m ".$mac;
		$vm_create_cmd .= " -r ".$memory;
		$vm_create_cmd .= " -c ".$cpu;
		$vm_create_cmd .= " -b net";
		$vm_create_cmd .= " -d ".$swap;
		$vm_create_cmd .= " ".$additional_nic_str;
		$vm_create_cmd .= " ".$vncpassword_parameter;
		$vm_create_cmd .= " -vmtype vmware-esx-vm-net";
		$vm_create_cmd .= " --htvcenter-cmd-mode background";
		if($guest_id !== '') {
			$vm_create_cmd .= " --guest-id ".$guest_id;
		}
		if($datastore !== '') {
			$vm_create_cmd .= " -l ".$datastore;
		}
	} else {
		$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-cloud-hook", "Can not create VM $name on Host resource $host_resource_id. Unknown type $vm_type.", "", "", 0, 0, 0);
		return;
	}
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_create_cmd, NULL, true);
}



// removes a cloud vm
function remove_vmware_esx_vm($host_resource_id, $name, $mac, $vm_type) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// remove the vm from host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("remove_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Removing VMware ESX VM $name/$mac from Host $host_resource_id", "", "", 0, 0, 0);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// send command to create the vm on the host
	$vm_remove_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm remove -i ".$host_resource->ip." -n ".$name." --htvcenter-cmd-mode background";
	$event->log("remove_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Running $vm_remove_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_remove_cmd, NULL, true);

}



// Cloud hook methods

function create_vmware_esx_vm_local($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id=null) {
	global $event;
	create_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "vmware-esx-vm-local", $vncpassword, $source_image_id);
}

function create_vmware_esx_vm_net($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id) {
	global $event;
	create_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "vmware-esx-vm-net", $vncpassword, $source_image_id=null);
}

function remove_vmware_esx_vm_local($host_resource_id, $name, $mac) {
	global $event;
	remove_vmware_esx_vm($host_resource_id, $name, $mac, "vmware-esx-vm-local");
}

function remove_vmware_esx_vm_net($host_resource_id, $name, $mac) {
	global $event;
	remove_vmware_esx_vm($host_resource_id, $name, $mac, "vmware-esx-vm-net");
}



// ---------------------------------------------------------------------------------


?>
