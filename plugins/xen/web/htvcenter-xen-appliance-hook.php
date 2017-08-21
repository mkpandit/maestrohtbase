<?php
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
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



function htvcenter_xen_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
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
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}

	$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	switch($cmd) {
		case "start":
			// check resource type -> xen-strorage-vm-local
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("xen-vm-local");
			if ($resource->vtype != $virtualization->id) {
				$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "$appliance_id is not from type xen-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
				return;
			}

			// check image is on the same storage server
			// get the xen host resource
			$xen_host_resource = new resource();
			$xen_host_resource->get_instance_by_id($resource->vhostid);
			// get the xen resource
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);
			$storage = new storage();
			$storage->get_instance_by_id($image->storageid);
			$xen_resource = new resource();
			$xen_resource->get_instance_by_id($storage->resource_id);
			if ($xen_host_resource->id != $xen_resource->id) {
				$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Appliance $appliance_id image is not available on this xen host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
			}
			// send command to assign image and start vm
			$xen_command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/xen/bin/htvcenter-xen-vm start_by_mac -m ".$resource->mac." -d ".$image->rootdevice." -y xen-vm-local --htvcenter-cmd-mode background";
			$xen_host_resource->send_command($xen_host_resource->ip, $xen_command);
			break;
		case "stop":
			// check resource type -> xen-strorage-vm-local
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("xen-vm-local");
			if ($resource->vtype != $virtualization->id) {
				$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "$appliance_id is not from type xen-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
				return;
			}

			// check image is on the same storage server
			// get the xen host resource
			$xen_host_resource = new resource();
			$xen_host_resource->get_instance_by_id($resource->vhostid);
			// get the xen resource
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);
			$storage = new storage();
			$storage->get_instance_by_id($image->storageid);
			$xen_resource = new resource();
			$xen_resource->get_instance_by_id($storage->resource_id);
			if ($xen_host_resource->id != $xen_resource->id) {
				$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Appliance $appliance_id image is not available on this xen host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
			}
			// send command to stop the vm and deassign image
			$xen_command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/xen/bin/htvcenter-xen-vm restart_by_mac -m ".$resource->mac." -y xen-vm-local --htvcenter-cmd-mode background";
			$xen_host_resource->send_command($xen_host_resource->ip, $xen_command);
			break;

			
		case "update":
			// check if the appliance was set to a xen Host, if yes, auto-create the storage objects
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("xen");
			if ($appliance->virtualization == $virtualization->id)  {
				// Xen LVM Storage
				$deployment = new deployment();
				$deployment->get_instance_by_name('xen-lvm-deployment');
				$storage = new storage();
				$xen_id_list = $storage->get_ids_by_storage_type($deployment->id);
				$found_xen = false;
				$found_xen_id = -1;
				foreach ($xen_id_list as $list) {
					foreach ($list as $xen_id) {
						$storage->get_instance_by_id($xen_id);
						if ($storage->resource_id == $appliance->resources) {
							$found_xen = true;
							$found_xen_id = $storage->id;
							break;
						}
					}
				}
				if (!$found_xen) {
					$found_xen_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$storage_fields['storage_id']=$found_xen_id;
					$storage_fields['storage_name']=$appliance->name."-lvm";
					$storage_fields['storage_type']=$deployment->id;
					$storage_fields['storage_comment']='Xen LVM Storage Object for Appliance '.$appliance->name;
					$storage_fields['storage_resource_id']=$appliance->resources;
					$storage_fields['storage_capabilities'] = '';
					$storage->add($storage_fields);
					$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Created Xen LVM Storage Object for Appliance ".$appliance_id."!", "", "", 0, 0, $appliance_id);
				} else {
					$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Xen LVM Storage Object for Appliance ".$appliance_id." already existing.", "", "", 0, 0, $appliance_id);
				}
				// Xen Blockfile Storage
				$deployment = new deployment();
				$deployment->get_instance_by_name('xen-bf-deployment');
				$storage = new storage();
				$xen_id_list = $storage->get_ids_by_storage_type($deployment->id);
				$found_xen = false;
				$found_xen_id = -1;
				foreach ($xen_id_list as $list) {
					foreach ($list as $xen_id) {
						$storage->get_instance_by_id($xen_id);
						if ($storage->resource_id == $appliance->resources) {
							$found_xen = true;
							$found_xen_id = $storage->id;
							break;
						}
					}
				}
				if (!$found_xen) {
					$found_xen_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$storage_fields['storage_id']=$found_xen_id;
					$storage_fields['storage_name']=$appliance->name."-bf";
					$storage_fields['storage_type']=$deployment->id;
					$storage_fields['storage_comment']='Xen Blockfile Storage Object for Appliance '.$appliance->name;
					$storage_fields['storage_resource_id']=$appliance->resources;
					$storage_fields['storage_capabilities'] = '';
					$storage->add($storage_fields);
					$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Created Xen Blockfile Storage Object for Appliance ".$appliance_id."!", "", "", 0, 0, $appliance_id);
				} else {
					$event->log("htvcenter_xen_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-xen-appliance-hook.php", "Xen Blockfile Storage Object for Appliance ".$appliance_id." already existing.", "", "", 0, 0, $appliance_id);
				}
			}
			break;
			
	}
}



?>


