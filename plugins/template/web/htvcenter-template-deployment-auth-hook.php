<?php
/**
 * @package htvcenter
 */
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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";

/**
 * @package htvcenter
 * @author htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 * @version 1.0
 */

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;



	//--------------------------------------------------
	/**
	* authenticates the storage volume for the appliance resource
	* <code>
	* storage_auth_function("start", 2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_function($cmd, $appliance_id) {
		global $event;
		global $htvcenter_SERVER_BASE_DIR;
		global $htvcenter_SERVER_IP_ADDRESS;
		global $htvcenter_EXEC_PORT;
		global $IMAGE_AUTHENTICATION_TABLE;
		global $htvcenter_server;
		global $RootDir;

		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);

		$image = new image();
		$image->get_instance_by_id($appliance->imageid);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;

		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;

		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;

		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$resource_mac=$resource->mac;
		$resource_ip=$resource->ip;

		// For template vms we assume that the image is located on the vm-host
		// so we send the auth command to the vm-host instead of the image storage.
		// This enables using a SAN backend with dedicated volumes per vm-host which all
		// contain all "golden-images" which are used for snapshotting.
		// We do this to overcome the current lvm limitation of not supporting cluster-wide snapshots
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($resource->vhostid);
		if ($vm_host_resource->id != $storage_resource->id) {
			$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "htvcenter-lxc-lvm-deployment-auth-hook.php", "Appliance $appliance_id image IS NOT available on this template host, $storage_resource->id not equal $vm_host_resource->id !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "htvcenter-lxc-lvm-deployment-auth-hook.php", "Appliance $appliance_id image IS available on this template host, $storage_resource->id equal $vm_host_resource->id", "", "", 0, 0, $appliance_id);
		}

		switch($cmd) {
			case "start":
				// authenticate the rootfs / needs htvcenter user + pass
				$htvcenter_admin_user = new user("htvcenter");
				$htvcenter_admin_user->set_user();
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "htvcenter-lxc-lvm-deployment-auth-hook.php", "Authenticating ".$image_name." / ".$image_rootdevice." on ".$vm_host_resource->ip." to resource ".$resource_ip.".", "", "", 0, 0, $appliance_id);
				$auth_start_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/template/bin/htvcenter-template auth -n ".$image_name." -r ".$image_rootdevice." -i ".$resource_ip." -u ".$htvcenter_admin_user->name." -p ".$htvcenter_admin_user->password." --htvcenter-cmd-mode background";
				$resource->send_command($vm_host_resource->ip, $auth_start_cmd);
				break;
		}

	}



	//--------------------------------------------------
	/**
	* de-authenticates the storage volume for the appliance resource
	* (runs via the image_authentication class)
	* <code>
	* storage_auth_stop(2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_stop($image_id) {

		global $event;
		global $htvcenter_SERVER_BASE_DIR;
		global $htvcenter_SERVER_IP_ADDRESS;
		global $htvcenter_EXEC_PORT;

		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;

		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;

		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;


	}



	//--------------------------------------------------
	/**
	* de-authenticates the storage deployment volumes for the appliance resource
	* (runs via the image_authentication class)
	* <code>
	* storage_auth_deployment_stop(2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_deployment_stop($image_id) {

		global $event;
		global $htvcenter_SERVER_BASE_DIR;
		global $htvcenter_SERVER_IP_ADDRESS;
		global $htvcenter_EXEC_PORT;

		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;

		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;

		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;


	}






?>


