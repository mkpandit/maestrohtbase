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
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htvcenter-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
global $htvcenter_SERVER_BASE_DIR;

// global event for logging
$event = new event();
global $event;


function lvm_iscsi_deployment_wait_for_identfile($sfile) {
	$refresh_delay=1;
	$refresh_loop_max=20;
	$refresh_loop=0;
	while (!file_exists($sfile)) {
		sleep($refresh_delay);
		$refresh_loop++;
		flush();
		if ($refresh_loop > $refresh_loop_max)  {
			return false;
		}
	}
	return true;
}


function get_lvm_iscsi_deployment_image_rootdevice_identifier($lvm_iscsi_storage_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_ADMIN;
	global $event;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/plugins/lvm-storage/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($lvm_iscsi_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_resource_id = $storage_resource->id;
	$ident_file = "$StorageDir/$storage_resource_id.lv.lvm-iscsi-deployment.ident";
	if (file_exists($ident_file)) {
		unlink($ident_file);
	}
	// send command
	$resource_command="$htvcenter_SERVER_BASE_DIR/htvcenter/plugins/lvm-storage/bin/htvcenter-lvm-storage post_identifier -t lvm-iscsi-deployment -u $htvcenter_ADMIN->name -p $htvcenter_ADMIN->password";
	$storage_resource->send_command($storage_resource->ip, $resource_command);
	if (!lvm_iscsi_deployment_wait_for_identfile($ident_file)) {
		$event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.lvm-iscsi-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
		return;
	}
	$fcontent = file($ident_file);
	foreach($fcontent as $lun_info) {
		$tpos = strpos($lun_info, ",");
		$timage_name = trim(substr($lun_info, 0, $tpos));
		$troot_device = trim(substr($lun_info, $tpos+1));
		$rootdevice_identifier_array[] = array("value" => "$troot_device", "label" => "$timage_name");
	}
	return $rootdevice_identifier_array;
}

function get_lvm_iscsi_deployment_image_default_rootfs() {
	return "ext3";
}


function get_lvm_iscsi_deployment_rootfs_transfer_methods() {
	return true;
}

function get_lvm_iscsi_deployment_rootfs_set_password_method() {
	return true;
}

function get_lvm_iscsi_deployment_is_network_deployment() {
	return true;
}

function get_lvm_iscsi_deployment_local_deployment_enabled() {
	return false;
}


?>


