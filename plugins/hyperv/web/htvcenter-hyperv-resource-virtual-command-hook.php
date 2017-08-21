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
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function htvcenter_hyperv_resource_virtual_command($cmd, $resource_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$resource_id = $resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($resource->vtype);
	
	
	switch($virtualization->type) {
		case "hyperv":
			$event->log("htvcenter_hyperv_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id", "", "", 0, 0, 0);
			$htvcenter_server = new htvcenter_server();
			switch($cmd) {
				case "reboot":
					$virtual_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm host_reboot -i ".$resource->ip;
					$htvcenter_server->send_command($virtual_command, NULL, true);
					$resource_reboot_fields=array();
					$resource_reboot_fields["resource_state"]="transition";
					$resource_reboot_fields["resource_event"]="reboot";
					$resource->update_info($resource->id, $resource_reboot_fields);
					break;
				case "halt":
					$virtual_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm host_shutdown -i ".$resource->ip;
					$htvcenter_server->send_command($virtual_command, NULL, true);
					$resource_reboot_fields=array();
					$resource_reboot_fields["resource_state"]="off";
					$resource_reboot_fields["resource_event"]="reboot";
					$resource->update_info($resource->id, $resource_reboot_fields);
					break;
			}
			break;
		
		case "hyperv-vm-local":
			$host_resource = new resource();
			$host_resource->get_instance_by_id($resource->vhostid);
			$event->log("htvcenter_hyperv_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id on host $host_resource->id", "", "", 0, 0, 0);
			$htvcenter_server = new htvcenter_server();
			switch($cmd) {
				case "reboot":
					// find out if this is coming from the resource-list reboot
					// only reboot non-idle VMs
					if ($resource->imageid != 1) {
						$virtual_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm reboot_by_mac -i ".$host_resource->ip." -m ".$resource->mac." -vmtype hyperv-vm-local --htvcenter-cmd-mode fork";
						$htvcenter_server->send_command($virtual_command, NULL, true);
						$resource_reboot_fields=array();
						$resource_reboot_fields["resource_state"]="transition";
						$resource_reboot_fields["resource_event"]="reboot";
						$resource->update_info($resource->id, $resource_reboot_fields);
					}
					break;
				case "halt":
					$virtual_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm stop_by_mac -i ".$host_resource->ip." -m ".$resource->mac." -vmtype hyperv-vm-local --htvcenter-cmd-mode fork";
					$htvcenter_server->send_command($virtual_command, NULL, true);
					$resource_reboot_fields=array();
					$resource_reboot_fields["resource_state"]="off";
					$resource_reboot_fields["resource_event"]="reboot";
					$resource->update_info($resource->id, $resource_reboot_fields);
					break;
			}
			break;
		
		case "hyperv-vm-net":
			$host_resource = new resource();
			$host_resource->get_instance_by_id($resource->vhostid);
			$event->log("htvcenter_hyperv_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hyperv-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id on host $host_resource->id", "", "", 0, 0, 0);
			switch($cmd) {
				case "reboot":
					// simply add to cmd queue. do not use resource->send_command(ip, reboot) since this will re-trigger this hook
					$cmd_token = md5(uniqid(rand(), true));
					$resource_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/sbin/htvcenter-exec -i ".$resource->ip." -t ".$cmd_token." -c reboot";
					shell_exec($resource_command);
					$resource_reboot_fields=array();
					$resource_reboot_fields["resource_state"]="transition";
					$resource_reboot_fields["resource_event"]="reboot";
					$resource->update_info($resource->id, $resource_reboot_fields);
					break;
				case "halt":
					$cmd_token = md5(uniqid(rand(), true));
					$resource_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/sbin/htvcenter-exec -i ".$resource->ip." -t ".$cmd_token." -c halt";
					shell_exec($resource_command);
					$resource_reboot_fields=array();
					$resource_reboot_fields["resource_state"]="off";
					$resource_reboot_fields["resource_event"]="reboot";
					$resource->update_info($resource->id, $resource_reboot_fields);
					break;
			}
			break;
		
	}
	
}



?>