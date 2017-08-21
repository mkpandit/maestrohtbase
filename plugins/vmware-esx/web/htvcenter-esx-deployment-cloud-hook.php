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
global $IMAGE_AUTHENTICATION_TABLE;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;


// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_esx_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;

	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Creating clone ".$image_clone_name." of image ".$cloudimage->image_id." on the storage", "", "", 0, 0, 0);
	// get image, this is already the new logical clone
	// we just need to physical snapshot it and update the rootdevice
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// refresh image
	$image->get_instance_by_id($image_id);
	// parse the identifiers
	// origin image volume name
	$image_rootdevice_array = explode(':', $image_rootdevice);
	$image_datastore = $image_rootdevice_array[0];
	$image_vmdk_path = $image_rootdevice_array[1];
	$image_vmdk_name = basename($image_vmdk_path);
	$image_vmdk_name = str_replace(".vmdk", "", $image_vmdk_name);
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => $image_datastore.':'.$image_clone_name.'/'.$image_clone_name.'.vmdk',
	);
	// For vmware-esx vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the ESX host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	// san backend ?
	if ($vm_host_resource->id != $resource->id) {
		$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this ESX host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);

		// update the image storage id with the vm-host-resource
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_type);
		// loop over all storage id from type $image_type
		$found_image=0;
		$storage_list_by_type = new storage();
		$storage_id_list_by_type = $storage_list_by_type->get_ids_by_storage_type($image_deployment->id);
		foreach($storage_id_list_by_type as $storage_id_list) {
			$storage_id = $storage_id_list['storage_id'];
			$tstorage = new storage();
			$tstorage->get_instance_by_id($storage_id);
			if ($tstorage->resource_id == $vm_host_resource->id) {
				// re-create update array + new storage id
				$ar_image_update = array(
					'image_rootdevice' => $image_datastore.':'.$image_clone_name.'/'.$image_clone_name.'.vmdk',
					'image_storageid' => $tstorage->id,
				);
				$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Updating Image ".$image_id." / ".$image_name." with storage id ".$tstorage->id.".", "", "", 0, 0, $appliance_id);
				$found_image=1;
				break;
			}
		}
		if ($found_image == 0) {
			$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 2, "htvcenter-esx-deployment-cloud-hook.php", "SETUP ERROR: Could not find a storage server type ".$image_type." using resource ".$vm_host_resource->id.". Please create one!", "", "", 0, 0, $appliance_id);
			$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 2, "htvcenter-esx-deployment-cloud-hook.php", "SETUP ERROR: Not cloning image ".$image_id.".", "", "", 0, 0, $appliance_id);
			return;
		}

	} else {
		$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Image ".$image_id." IS available on this vmware_esx host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
	}

	$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Updating rootdevice of image ".$image_id." / ".$image_name." with ".$image_location_name."/".$image_clone_name, "", "", 0, 0, 0);
	$image->update($image_id, $ar_image_update);
	$image_clone_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-datastore clone_vmdk";
	$image_clone_cmd    .= ' -i '.$vm_host_resource->ip;
	$image_clone_cmd    .= ' -n '.$image_datastore;
	$image_clone_cmd    .= ' -f '.$image_vmdk_name;
	$image_clone_cmd    .= ' -c '.$image_clone_name." --htvcenter-cmd-mode background";
		
	$event->log("create_clone_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Running : ".$image_clone_cmd, "", "", 0, 0, 0);
	$htvcenter_server->send_command($image_clone_cmd, NULL, true);
}



// removes the volume of an image
function remove_esx_deployment($cloud_image_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("remove_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Removing image ".$cloudimage->image_id." from storage.", "", "", 0, 0, 0);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// parse the identifiers
	// origin image volume name
	$image_rootdevice_array = explode(':', $image_rootdevice);
	$image_datastore = $image_rootdevice_array[0];
	$image_vmdk = $image_rootdevice_array[1];
	// For ESX VMs we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the ESX host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("remove_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this ESX host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("remove_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Image ".$image_id." IS available on this ESX host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
		}
	}
	$image_remove_clone_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-datastore remove_vmdk";
	$image_remove_clone_cmd    .= ' -i '.$resource_ip;
	$image_remove_clone_cmd    .= ' -n '.$image_datastore;
	$image_remove_clone_cmd    .= ' -f '.$image_name." --htvcenter-cmd-mode background";
	$event->log("remove_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Running : ".$image_remove_clone_cmd, "", "", 0, 0, 0);
	$htvcenter_server->send_command($image_remove_clone_cmd, NULL, true);
}


// resizes the volume of an image
function resize_esx_deployment($cloud_image_id, $resize_value) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;
	$event->log("resize_esx_deployment", $_SERVER['REQUEST_TIME'], 2, "htvcenter-esx-deployment-cloud-hook.php", "Resize image ".$cloudimage->image_id." is not supported!", "", "", 0, 0, 0);
}



// creates a private copy of the volume of an image
function create_private_esx_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_private_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Creating private image ".$cloudimage->image_id." on storage.", "", "", 0, 0, 0);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// create an admin user to post when cloning has finished
	$htvcenter_admin_user = new user("htvcenter");
	$htvcenter_admin_user->set_user();
	// parse the identifiers
	// origin image volume name
	$image_rootdevice_array = explode(':', $image_rootdevice);
	$image_datastore = $image_rootdevice_array[0];
	$image_vmdk_path = $image_rootdevice_array[1];
	$image_vmdk_name = basename($image_vmdk_path);
	$image_vmdk_name = str_replace(".vmdk", "", $image_vmdk_name);
	// For ESX VMs we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the ESX host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the ESX host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("create_private_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this ESX host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("create_private_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Image ".$image_id." IS available on this ESX host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
		}
	}
	$image_private_clone_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-datastore clone_vmdk";
	$image_private_clone_cmd    .= ' -i '.$resource_ip;
	$image_private_clone_cmd    .= ' -n '.$image_datastore;
	$image_private_clone_cmd    .= ' -f '.$image_vmdk_name;
	$image_private_clone_cmd    .= ' -c '.$private_image_name." --htvcenter-cmd-mode background";
		
	$event->log("create_private_esx_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-esx-deployment-cloud-hook.php", "Running : $image_private_clone_cmd", "", "", 0, 0, 0);
	$htvcenter_server->send_command($image_private_clone_cmd, NULL, true);
	// set the storage specific image root_device parameter
	$new_rootdevice = $image_datastore.':'.$private_image_name.'/'.$private_image_name.'.vmdk';
	return $new_rootdevice;
}




?>

