<?php
/*
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
*/


// This file implements the virtual machine abstraction in the cloud of HyperTask

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
// general vmware-vsphere cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm from a specificed virtualization type + parameters
function create_vmware_vsphere_vm($host_resource_id, $name, $mac, $memory, $cpu, $disk, $additional_nic_str, $vm_type, $vncpassword, $source_image_id=null) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	$vmware_mac_address_space = "00:50:56";
	$swap = "1048576";

	global $event;
	$event->log("create_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "Creating VMware vSphere VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_mac($mac);
	// we need to have an HyperTask server object too since some of the
	// virtualization commands are sent from HyperTask directly
	$htvcenter = new htvcenter_server();
	// get guest_id from source image
	$image = new image();
	$guest_id = '';
	$datastore = '';
	if(isset($source_image_id) && $source_image_id !== '') {
		$image->get_instance_by_id($source_image_id);
		if($image->type ===  'vsphere-deployment') {
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
		$event->log("create_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 2, "vmware-vsphere-cloud-hook", "Missing source_image_id. $error", "", "", 0, 0, 0);
	}
	// set the vnc access
	$vncpassword_parameter = "";
	if ($vncpassword == '') {
		$vncpassword = $image->generatePassword(8);
	}
	// create the vnc port
	$vnc_port = $vm_resource->generate_vnc_port($host_resource_id);
	$vnc_port = $vnc_port + 5900;
	$resource_fields["resource_vname"] = $name;
	// we ensure to remove any vnc infos, novnc will dynamically get it
	$resource_fields["resource_vnc"] = '';
	$vm_resource->update_info($vm_resource->id, $resource_fields);
	$vncpassword_parameter = " -va ".$vncpassword." -vp ".$vnc_port;

	// get data from source image
	$source_image = new image();
	$source_image->get_instance_by_id($source_image_id);
	
	$image_rootdevice_array = explode(':', $source_image->rootdevice);
	$image_datastore = $image_rootdevice_array[0];
	$image_vmdk_path = $image_rootdevice_array[1];
	$image_vmdk_name = basename($image_vmdk_path);
	$source_vm = str_replace(".vmdk", "", $image_vmdk_name);

	// get VM config of source VM
	$source_vm_config = $_SERVER["DOCUMENT_ROOT"]."/htvcenter/base/plugins/vmware-vsphere/vmware-vsphere-stat/".$host_resource->ip.".".$source_vm.".vm_config";
	$command  = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm post_vm_config";
	$command .=  ' -i '.$host_resource->ip;
	$command .=  ' -n '.$source_vm;
	if(file_exists($source_vm_config)) {
		unlink($source_vm_config);
	}
	$htvcenter->send_command($command, NULL, true);

	while (!file_exists($source_vm_config))	{
	  usleep(10000); // sleep 10ms to unload the CPU
	  clearstatcache();
	}
	
	$vm_configuration = array();
	if (file_exists($source_vm_config)) {
		$lines = explode("\n", file_get_contents($source_vm_config));
		if(count($lines) >= 1) {
			foreach($lines as $line) {
				if($line !== '') {
					$vm_configuration = parse_string_to_array($line, '|', '=');
				}
			}
		}
	}
	
	
	// get mac config of source VM
	$source_vm_mac_arr = explode(",", $vm_configuration['macAddress']);
	$source_vm_first_mac = $source_vm_mac_arr[0];
	// get mac config of source VM
	$source_vm_network_arr = explode(",", $vm_configuration['network']);
	$source_vm_first_network = $source_vm_network_arr[0];
	$source_vm_first_network_arr = explode("@", $source_vm_first_network);
	$nictype = $source_vm_first_network_arr[1];
	$vswitch = $source_vm_first_network_arr[2];
	$vswitch = str_replace(" ", "@", $vswitch);

	// $nictype = "e1000";
	// $vswitch = "Management Network";
	
	// if it has no disk attach disk, set flag detach
	$source_vm_vmdk_arr = explode(',', $vm_configuration['fileName']);
	$source_vm_first_vmdk = $source_vm_vmdk_arr[0];

	if (!strlen($source_vm_first_vmdk)) {
		$event->log("create_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "Origin VM  ".$source_vm." has no disk - attaching disk ".$image_datastore." - ".$image_vmdk_path, "", "", 0, 0, 0);
		$assign_disk_command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm assign_by_mac -i ".$host_resource->ip." -m ".$source_vm_first_mac." -d ".$image_vmdk_path." -l ".$image_datastore." --htvcenter-cmd-mode background";
		// $event->log("create_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "!!!!!!!!!!!!!!! running $assign_disk_command", "", "", 0, 0, 0);
		$htvcenter->send_command($assign_disk_command, NULL, true);
		sleep(5);
	}

	
	// put the root-disk infos in the resource capabilites for remove later
	$vm_resource->set_resource_capabilities('VMDK', $image_datastore.":".$name."/".$name.".vmdk");
	// set lastgood to -1 prevents resource error during clone
	$resource_fields1["resource_lastgood"]="-1";
	$vm_resource->update_info($vm_resource->id, $resource_fields1);
	
	// send command to create vm
	if ($vm_type == "vmware-vsphere-vm-local") {
		$vm_create_cmd  = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm clone";
		$vm_create_cmd .= " -i ".$host_resource->ip;
		$vm_create_cmd .= " -l ".$image_datastore;
		$vm_create_cmd .= " --datacenter ".$vm_configuration['datacenter'];
		$vm_create_cmd .= " --resourcepool ".$vm_configuration['resourcepool'];
		$vm_create_cmd .= " -n ".$name;
		$vm_create_cmd .= " --vm-template ".$source_vm;
		$vm_create_cmd .= " -m ".$mac;
		$vm_create_cmd .= " -t ".$nictype;
		$vm_create_cmd .= " -v ".$vswitch;
		$vm_create_cmd .= " ".$vncpassword_parameter;
		$vm_create_cmd .= " -r ".$memory;
		$vm_create_cmd .= " -c ".$cpu;
		$vm_create_cmd .= " ".$additional_nic_str;
		$vm_create_cmd .= " -vmtype vmware-vsphere-vm-local";
		$vm_create_cmd .= " --htvcenter-cmd-mode background";
		
		$event->log("create_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
		$htvcenter->send_command($vm_create_cmd, NULL, true);
		
		
	} else {
		$event->log("create_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 2, "vmware-vsphere-cloud-hook", "Can not create VM $name on Host resource $host_resource_id. Unknown type $vm_type.", "", "", 0, 0, 0);
		return;
	}
}



// removes a cloud vm
function remove_vmware_vsphere_vm($host_resource_id, $name, $mac, $vm_type) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// remove the vm from host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("remove_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "Removing VMware vSphere VM $name/$mac from Host $host_resource_id", "", "", 0, 0, 0);
	// we need to have an HyperTask server object too since some of the
	// virtualization commands are sent from HyperTask directly
	$htvcenter = new htvcenter_server();
	
	$vm_resource = new resource();
	$vm_resource->get_instance_by_mac($mac);
	$vmdk = $vm_resource->get_resource_capabilities('VMDK');
	if (strlen($vmdk)) {
		$image_rootdevice_array = explode(':', $vmdk);
		$image_datastore = $image_rootdevice_array[0];
		$image_vmdk_path = $image_rootdevice_array[1];

		// re-attach the vmdk because it was detached by applicance stop
		// next this will also remove the vmdk during VM remove
		$reattach_disk_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm assign_by_mac -i ".$host_resource->ip." -m ".$mac." -l ".$image_datastore." -d ".$image_vmdk_path." --htvcenter-cmd-mode background";
		$event->log("remove_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "!!!!!!!!!!!!!!!Running $reattach_disk_cmd", "", "", 0, 0, 0);
		$htvcenter->send_command($reattach_disk_cmd, NULL, true);
		sleep(5);
	} else {
		$event->log("remove_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 2, "vmware-vsphere-cloud-hook", "!!!!!!!!!!!!!!! No image info in resource capabilites!", "", "", 0, 0, 0);
	}
	
	// send command to remove the vm on the host
	$vm_remove_cmd = "$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm remove -i ".$host_resource->ip." -n ".$name." --htvcenter-cmd-mode background";
	$event->log("remove_vmware_vsphere_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-cloud-hook", "@@@@@@@@@@@@Running $vm_remove_cmd", "", "", 0, 0, 0);
	$htvcenter->send_command($vm_remove_cmd, NULL, true);

}



// Cloud hook methods

function create_vmware_vsphere_vm_local($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id=null) {
	global $event;
	create_vmware_vsphere_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "vmware-vsphere-vm-local", $vncpassword, $source_image_id);
}

function create_vmware_vsphere_vm_net($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id) {
	global $event;
	create_vmware_vsphere_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "vmware-vsphere-vm-net", $vncpassword, $source_image_id=null);
}

function remove_vmware_vsphere_vm_local($host_resource_id, $name, $mac) {
	global $event;
	remove_vmware_vsphere_vm($host_resource_id, $name, $mac, "vmware-vsphere-vm-local");
}

function remove_vmware_vsphere_vm_net($host_resource_id, $name, $mac) {
	global $event;
	remove_vmware_vsphere_vm($host_resource_id, $name, $mac, "vmware-vsphere-vm-net");
}



// ---------------------------------------------------------------------------------


function parse_string_to_array($string, $element_delimiter = '|', $value_delimiter = '=') {
	$results = array();
	$array = explode($element_delimiter, $string);
	foreach ($array as $result) {
		$element = explode($value_delimiter, $result);
		if (isset($element[1])) {
			$results[$element[0]] = $element[1];
		}
	}
	return $results;
}


?>
