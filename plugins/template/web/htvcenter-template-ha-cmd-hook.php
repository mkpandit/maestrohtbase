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
// general template_deployment cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm
function create_template_deployment_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $origin_resource_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_template_deployment_vm", $_SERVER['REQUEST_TIME'], 5, "template_deployment-ha-hook", "Creating OpenVZ-Storage VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// send command to create vm
	$vm_create_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template_deployment/bin/htvcenter-template_deployment-vm create -n ".$name." -m ".$mac." -i 0.0.0.0 ".$additional_nic_str." --htvcenter-cmd-mode background";
	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
	$event->log("create_template_deployment_vm", $_SERVER['REQUEST_TIME'], 5, "template_deployment-ha-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
}



// fences a vm
function fence_template_deployment_vm($host_resource_id, $mac) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// fences the vm on its host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("fence_template_deployment_vm", $_SERVER['REQUEST_TIME'], 5, "template_deployment-ha-hook", "Fencing OpenVZ VM $mac from Host resource $host_resource_id", "", "", 0, 0, 0);
	// we need to have an htvcenter server object too since some of the
	// virtualization commands are sent from htvcenter directly
	$htvcenter = new htvcenter_server();
	// send command to fence the vm on the host
	$vm_fence_cmd =$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template_deployment/bin/htvcenter-template_deployment-vm fence -m ".$mac." --htvcenter-cmd-mode background";
	$event->log("fence_template_deployment_vm", $_SERVER['REQUEST_TIME'], 5, "template_deployment-ha-hook", "Running $vm_fence_cmd", "", "", 0, 0, 0);
	$host_resource->send_command($host_resource->ip, $vm_fence_cmd);
}




// ---------------------------------------------------------------------------------


?>
