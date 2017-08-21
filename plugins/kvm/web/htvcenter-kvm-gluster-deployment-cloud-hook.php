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


// This file implements the cloud storage methods

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";

$event = new event();
global $event;

global $htvcenter_SERVER_BASE_DIR;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;


// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_kvm_gluster_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_clone_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Creating clone ".$image_clone_name." of image ".$cloudimage->image_id." on the storage", "", "", 0, 0, 0);
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
	// kvm-gluster-deployment
	$image->get_instance_by_id($image_id);
	$gluster_uri_arr = parse_url($image_rootdevice);
	// origin image volume name
	$origin_volume_name=basename($gluster_uri_arr['path']);
	// location of the volume (path)
	$image_location_name=str_replace('/', '', dirname($gluster_uri_arr['path']));
	// set default snapshot size
	if (!strlen($disk_size)) {
		$disk_size=5000;
	}
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => "gluster:".$resource->ip."//".$image_location_name."/".$image_clone_name,
	);
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the VM host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);

	$event->log("create_clone_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Updating rootdevice of image ".$image_id." / ".$image_name." with ".$image_location_name."/".$image_clone_name, "", "", 0, 0, 0);
	$image->update($image_id, $ar_image_update);
	$image_clone_cmd="$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/kvm/bin/htvcenter-kvm snap -n ".$origin_volume_name." -v ".$image_location_name." -s ".$image_clone_name." -m ".$disk_size." -t ".$deployment->type." --htvcenter-cmd-mode background";
	$event->log("create_clone_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Running : ".$image_clone_cmd, "", "", 0, 0, 0);
	$resource->send_command($resource->ip, $image_clone_cmd);
}



// removes the volume of an image
function remove_kvm_gluster_deployment($cloud_image_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("remove_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Removing image ".$cloudimage->image_id." from storage.", "", "", 0, 0, 0);
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
	$gluster_uri_arr = parse_url($image_rootdevice);
	// origin image volume name
	$origin_volume_name=basename($gluster_uri_arr['path']);
	// location of the volume (path)
	$image_location_name=str_replace('/', '', dirname($gluster_uri_arr['path']));
	$image_remove_clone_cmd=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/kvm/bin/htvcenter-kvm remove -n ".$origin_volume_name." -v ".$image_location_name." -t ".$deployment->type." --htvcenter-cmd-mode background";
	$event->log("remove_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Running : ".$image_remove_clone_cmd, "", "", 0, 0, 0);
	$resource->send_command($resource->ip, $image_remove_clone_cmd);
}


// resizes the volume of an image
function resize_kvm_gluster_deployment($cloud_image_id, $resize_value) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("resize_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Resize image ".$cloudimage->image_id." on storage.", "", "", 0, 0, 0);
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
	$gluster_uri_arr = parse_url($image_rootdevice);
	// origin image volume name
	$origin_volume_name=basename($gluster_uri_arr['path']);
	// location of the volume (path)
	$image_location_name=str_replace('/', '', dirname($gluster_uri_arr['path']));
	$image_resize_cmd=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/kvm/bin/htvcenter-kvm resize -n ".$origin_volume_name." -v ".$image_location_name." -m ".$resize_value." -t ".$deployment->type." --htvcenter-cmd-mode background";
	$event->log("resize_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Running : ".$image_resize_cmd, "", "", 0, 0, 0);
	$resource->send_command($resource->ip, $image_resize_cmd);
}



// creates a private copy of the volume of an image
function create_private_kvm_gluster_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_private_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Creating private image ".$cloudimage->image_id." on storage.", "", "", 0, 0, 0);
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
	$gluster_uri_arr = parse_url($image_rootdevice);
	// origin image volume name
	$origin_volume_name=basename($gluster_uri_arr['path']);
	// location of the volume (path)
	$image_location_name=str_replace('/', '', dirname($gluster_uri_arr['path']));
	$image_clone_cmd=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/kvm/bin/htvcenter-kvm clone -n ".$origin_volume_name." -s ".$private_image_name." -v ".$image_location_name." -m ".$private_disk." -u ".$htvcenter_admin_user->name." -p ".$htvcenter_admin_user->password." -t ".$deployment->type." --htvcenter-cmd-mode background";
	$event->log("create_private_kvm_gluster_deployment", $_SERVER['REQUEST_TIME'], 5, "htvcenter-kvm-gluster-deployment-cloud-hook.php", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource->ip, $image_clone_cmd);
	// set the storage specific image root_device parameter
	$new_rootdevice = "gluster:".$resource->ip."//".$image_location_name."/".$private_image_name;
	return $new_rootdevice;
}



// ---------------------------------------------------------------------------------


?>