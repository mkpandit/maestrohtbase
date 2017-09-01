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



function htvcenter_kvm_appliance($cmd, $appliance_fields) {
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
	if (($resource->id == "-1") || ($resource->id == "") || (!isset($resource->vtype))) {
		return;
	}
	$htvcenter_admin_user = new user("htvcenter");
	$htvcenter_admin_user->set_user();

	$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	switch($cmd) {
		case "start":
			// send command to assign image and start vm

			// NOTICE : please enable this hook only if you are using the ip-mgmt plugin with vlans
			// check if resource type -> kvm-vm-net
//			$virtualization = new virtualization();
//			$virtualization->get_instance_by_type("kvm-vm-net");
//			$kvm_host_resource = new resource();
//			$kvm_host_resource->get_instance_by_id($resource->vhostid);
//			if ($resource->vtype != $virtualization->id) {
//				$kvm_command="$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/kvm/bin/htvcenter-kvm-vm reset_vlans_by_mac -b start -m $resource->mac";
//				$kvm_host_resource->send_command($kvm_host_resource->ip, $kvm_command);
//				return;
//			}


			// check resource type -> kvm-vm-local
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("kvm-vm-local");
			if ($resource->vtype != $virtualization->id) {
				$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "$appliance_id is not from type kvm-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
				return;
			}
			// check image is on the same storage server
			// get the kvm host resource
			$kvm_host_resource = new resource();
			$kvm_host_resource->get_instance_by_id($resource->vhostid);
			// get the kvm resource
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);
			$storage = new storage();
			$storage->get_instance_by_id($image->storageid);
			$kvm_resource = new resource();
			$kvm_resource->get_instance_by_id($storage->resource_id);
			if ($kvm_host_resource->id != $kvm_resource->id) {
				$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "Appliance $appliance_id image is not available on this kvm host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
			}
			$kvm_command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/kvm/bin/htvcenter-kvm-vm start_by_mac -m ".$resource->mac." -d ".$image->rootdevice." -u ".$htvcenter_admin_user->name." -p ".$htvcenter_admin_user->password;
			$kvm_host_resource->send_command($kvm_host_resource->ip, $kvm_command);
			break;
		case "stop":
			// send command to stop the vm and deassign image

			// NOTICE : please enable this hook only if you are using the ip-mgmt plugin with vlans
			// check if resource type -> kvm-vm-net
//			$virtualization = new virtualization();
//			$virtualization->get_instance_by_type("kvm-vm-net");
//			$kvm_host_resource = new resource();
//			$kvm_host_resource->get_instance_by_id($resource->vhostid);
//			if ($resource->vtype != $virtualization->id) {
//				$kvm_command="$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/kvm/bin/htvcenter-kvm reset_vlans_by_mac -b stop -m $resource->mac";
//				$kvm_host_resource->send_command($kvm_host_resource->ip, $kvm_command);
//				return;
//			}


			// check resource type -> kvm-vm-local
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("kvm-vm-local");
			if ($resource->vtype != $virtualization->id) {
				$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "$appliance_id is not from type kvm-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
				return;
			}
			// check image is on the same storage server
			// get the kvm host resource
			$kvm_host_resource = new resource();
			$kvm_host_resource->get_instance_by_id($resource->vhostid);
			// get the kvm resource
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);
			$storage = new storage();
			$storage->get_instance_by_id($image->storageid);
			$kvm_resource = new resource();
			$kvm_resource->get_instance_by_id($storage->resource_id);
			if ($kvm_host_resource->id != $kvm_resource->id) {
				$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "Appliance $appliance_id image is not available on this kvm host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
			}
			$kvm_command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/kvm/bin/htvcenter-kvm-vm restart_by_mac -m ".$resource->mac." -u ".$htvcenter_admin_user->name." -p ".$htvcenter_admin_user->password." --htvcenter-cmd-mode background";
			$kvm_host_resource->send_command($kvm_host_resource->ip, $kvm_command);
			break;

		case "update":
			// check if the appliance was set to a kvm Host, if yes, auto-create the storage objects
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("kvm");
			if ($appliance->virtualization == $virtualization->id)  {
				// KVM LVM Storage
				$deployment = new deployment();
				$deployment->get_instance_by_name('kvm-lvm-deployment');
				$storage = new storage();
				$kvm_id_list = $storage->get_ids_by_storage_type($deployment->id);
				$found_kvm = false;
				$found_kvm_id = -1;
				foreach ($kvm_id_list as $list) {
					foreach ($list as $kvm_id) {
						$storage->get_instance_by_id($kvm_id);
						if ($storage->resource_id == $appliance->resources) {
							$found_kvm = true;
							$found_kvm_id = $storage->id;
							break;
						}
					}
				}
				if (!$found_kvm) {
					$found_kvm_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$storage_fields['storage_id']=$found_kvm_id;
					$storage_fields['storage_name']=$appliance->name."-lvm";
					$storage_fields['storage_type']=$deployment->id;
					$storage_fields['storage_comment']='KVM LVM Storage Object for Appliance '.$appliance->name;
					$storage_fields['storage_resource_id']=$appliance->resources;
					$storage_fields['storage_capabilities'] = '';
					$storage->add($storage_fields);
					$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "Created KVM LVM Storage Object for Appliance ".$appliance_id."!", "", "", 0, 0, $appliance_id);
				} else {
					$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "KVM LVM Storage Object for Appliance ".$appliance_id." already existing.", "", "", 0, 0, $appliance_id);
				}
				// KVM Blockfile Storage
				$deployment = new deployment();
				$deployment->get_instance_by_name('kvm-bf-deployment');
				$storage = new storage();
				$kvm_id_list = $storage->get_ids_by_storage_type($deployment->id);
				$found_kvm = false;
				$found_kvm_id = -1;
				foreach ($kvm_id_list as $list) {
					foreach ($list as $kvm_id) {
						$storage->get_instance_by_id($kvm_id);
						if ($storage->resource_id == $appliance->resources) {
							$found_kvm = true;
							$found_kvm_id = $storage->id;
							break;
						}
					}
				}
				if (!$found_kvm) {
					$found_kvm_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$storage_fields['storage_id']=$found_kvm_id;
					$storage_fields['storage_name']=$appliance->name."-bf";
					$storage_fields['storage_type']=$deployment->id;
					$storage_fields['storage_comment']='KVM Blockfile Storage Object for Appliance '.$appliance->name;
					$storage_fields['storage_resource_id']=$appliance->resources;
					$storage_fields['storage_capabilities'] = '';
					$storage->add($storage_fields);
					$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "Created KVM Blockfile Storage Object for Appliance ".$appliance_id."!", "", "", 0, 0, $appliance_id);
				} else {
					$event->log("htvcenter_kvm_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-appliance-hook.php", "KVM Blockfile Storage Object for Appliance ".$appliance_id." already existing.", "", "", 0, 0, $appliance_id);
				}


			}
			break;


	}
}



?>


