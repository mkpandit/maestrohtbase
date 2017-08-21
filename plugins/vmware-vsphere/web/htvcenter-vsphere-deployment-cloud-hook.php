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
function create_clone_vsphere_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;
	
	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Creating clone ".$image_clone_name." of image ".$cloudimage->image_id." on the storage", "", "", 0, 0, 0);
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
	// For vmware-vsphere vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => $image_datastore.':'.$vm_resource->vname.'/'.$vm_resource->vname.'.vmdk',
	);
	
	// get the vSphere host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	
	// san backend ?
	if ($vm_host_resource->id != $resource->id) {
		$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this vSphere host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);

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
					'image_rootdevice' => $image_datastore.':'.$vm_resource->vname.'/'.$vm_resource->vname.'.vmdk',
					'image_storageid' => $tstorage->id,
				);
				$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Updating Image ".$image_id." / ".$image_name." with storage id ".$tstorage->id.".", "", "", 0, 0, $appliance_id);
				$found_image=1;
				break;
			}
		}
		if ($found_image == 0) {
			$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 2, "htvcenter-vsphere-deployment-cloud-hook.php", "SETUP ERROR: Could not find a storage server type ".$image_type." using resource ".$vm_host_resource->id.". Please create one!", "", "", 0, 0, $appliance_id);
			$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 2, "htvcenter-vsphere-deployment-cloud-hook.php", "SETUP ERROR: Not cloning image ".$image_id.".", "", "", 0, 0, $appliance_id);
			return;
		}

	} else {
		$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Image ".$image_id." IS available on this vmware_vsphere host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
	}
	

	$event->log("create_clone_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Updating rootdevice of image ".$image_id." / ".$image_name." with ".$image_location_name."/".$image_clone_name, "", "", 0, 0, 0);
	$image->update($image_id, $ar_image_update);
	return;
	
}



// removes the volume of an image
function remove_vsphere_deployment($cloud_image_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;
	
	// remove happens on behalf of VM create by cloning the VM 
	return;


	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("remove_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Removing image ".$cloudimage->image_id." from storage.", "", "", 0, 0, 0);
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
	// For vSphere VMs we assume that the image is located on the vm-host
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
		// get the vSphere host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("remove_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this vSphere host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("remove_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Image ".$image_id." IS available on this vSphere host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
		}
	}
	$image_remove_clone_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datastore remove_vmdk";
	$image_remove_clone_cmd    .= ' -i '.$resource_ip;
	$image_remove_clone_cmd    .= ' -n '.$image_datastore;
	$image_remove_clone_cmd    .= ' -f '.$image_name." --htvcenter-cmd-mode background";
	$event->log("remove_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "NOT Running : ".$image_remove_clone_cmd, "", "", 0, 0, 0);
//	$htvcenter_server->send_command($image_remove_clone_cmd, NULL, true);
}


// resizes the volume of an image
function resize_vsphere_deployment($cloud_image_id, $resize_value) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;
	$event->log("resize_vsphere_deployment", $_SERVER['REQUEST_TIME'], 2, "htvcenter-vsphere-deployment-cloud-hook.php", "Resize image ".$cloudimage->image_id." is not supported!", "", "", 0, 0, 0);
}



// creates a private copy of the volume of an image
function create_private_vsphere_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $htvcenter_server;
	global $event;
	
	// private image not supported yet
	return;
	

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_private_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Creating private image ".$cloudimage->image_id." on storage.", "", "", 0, 0, 0);
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
	// For vSphere VMs we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the vSphere host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the vSphere host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("create_private_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this vSphere host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("create_private_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "Image ".$image_id." IS available on this vSphere host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
		}
	}
	$image_private_clone_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datastore clone_vmdk";
	$image_private_clone_cmd    .= ' -i '.$resource_ip;
	$image_private_clone_cmd    .= ' -n '.$image_datastore;
	$image_private_clone_cmd    .= ' -f '.$image_vmdk_name;
	$image_private_clone_cmd    .= ' -c '.$private_image_name." --htvcenter-cmd-mode background";
		
	$event->log("create_private_vsphere_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-vsphere-deployment-cloud-hook.php", "NOT Running : $image_private_clone_cmd", "", "", 0, 0, 0);
//	$htvcenter_server->send_command($image_private_clone_cmd, NULL, true);
	// set the storage specific image root_device parameter
	$new_rootdevice = $image_datastore.':'.$private_image_name.'/'.$private_image_name.'.vmdk';
	return $new_rootdevice;
}




?>

