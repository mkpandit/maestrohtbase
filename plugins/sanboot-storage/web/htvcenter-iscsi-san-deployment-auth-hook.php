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
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;

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
		global $htvcenter_SERVER_BASE_DIR;
		global $htvcenter_EXEC_PORT;
		$IMAGE_AUTHENTICATION_TABLE="image_authentication_info";
		$event = new event();
		$htvcenter_server = new htvcenter_server();
		$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();

		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);

		$image = new image();
		$image->get_instance_by_id($appliance->imageid);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;
		// parse the volume group info in the identifier
		$ident_separate=strpos($image_rootdevice, ":");
		$volume_group=substr($image_rootdevice, 0, $ident_separate);
		$root_device=substr($image_rootdevice, $ident_separate);
		$image_location=dirname($root_device);
		$image_location_name=basename($image_location);

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
		$resource_id=$resource->id;

		switch($cmd) {
			case "start":
				// authenticate the rootfs / needs htvcenter user + pass
				$htvcenter_admin_user = new user("htvcenter");
				$htvcenter_admin_user->set_user();

				// generate a password for the image
				$image_password = $image->generatePassword(12);
				$image_deployment_parameter = $image->deployment_parameter;
				$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "htvcenter-iscsi-san-deployment-auth-hook.php", "Authenticating $image_name / $image_rootdevice to resource $resource_mac", "", "", 0, 0, $appliance_id);
				$auth_start_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/".$deployment_plugin_name."/bin/htvcenter-".$deployment_plugin_name." auth -n ".$image_name." -r ".$image_rootdevice." -i ".$image_password." -t iscsi-san-deployment  -u ".$htvcenter_admin_user->name." -p ".$htvcenter_admin_user->password." --htvcenter-cmd-mode background";
				$resource->send_command($storage_ip, $auth_start_cmd);

				// assign resource to boot from san via dhcpd.conf params
				// we need to run it here just before the resource reboots
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "htvcenter-iscsi-san-deployment-auth-hook.php", "Setting resource $resource_mac dhcpd-config to boot from san", "", "", 0, 0, $appliance_id);

				$sanboot_assing_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/".$deployment_plugin_name."/bin/htvcenter-".$deployment_plugin_name."-assign assign -n ".$image_location_name." -i ".$storage_ip." -p ".$image_password." -m ".$resource_mac." -r ".$resource_id." -z ".$resource_ip." -t iscsi-san-deployment --htvcenter-cmd-mode background";
				$htvcenter_server->send_command($sanboot_assing_cmd);


				break;

			case "stop":
				$image_authentication = new image_authentication();
				$ia_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_auth_ar = array(
					'ia_id' => $ia_id,
					'ia_image_id' => $appliance->imageid,
					'ia_resource_id' => $appliance->resources,
					'ia_auth_type' => 0,
				);
				$image_authentication->add($image_auth_ar);
				$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "htvcenter-iscsi-san-deployment-auth-hook.php", "Registered image $appliance->imageid for de-authentication the root-fs exports when resource $appliance->resources is idle again.", "", "", 0, 0, $appliance_id);
				// stopping sanboot assignment is in the appliance hook, must before the reboot of the resource

				// set IMAGE_VIRTUAL_RESOURCE_COMMAND to false here, after the reboot of the resource
				$image->set_deployment_parameters("IMAGE_VIRTUAL_RESOURCE_COMMAND", "false");
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

		global $htvcenter_SERVER_BASE_DIR;
		global $htvcenter_EXEC_PORT;
		$IMAGE_AUTHENTICATION_TABLE="image_authentication_info";
		$event = new event();
		$htvcenter_server = new htvcenter_server();
		$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();

		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;
		// generate a password for the image
		$image_password = $image->generatePassword(12);
		$image_deployment_parameter = $image->deployment_parameter;
		// parse the volume group info in the identifier
		$ident_separate=strpos($image_rootdevice, ":");
		$volume_group=substr($image_rootdevice, 0, $ident_separate);
		$root_device=substr($image_rootdevice, $ident_separate);
		$image_location=dirname($root_device);
		$image_location_name=basename($image_location);

		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;

		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;

		$auth_stop_cmd = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/".$deployment_plugin_name."/bin/htvcenter-".$deployment_plugin_name." auth -r ".$image_location_name." -i ".$image_password." -t iscsi-san-deployment --htvcenter-cmd-mode background";
		$resource = new resource();
		$resource->send_command($storage_ip, $auth_stop_cmd);
		// and update the image params
		$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);

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

		global $htvcenter_SERVER_BASE_DIR;
		global $htvcenter_EXEC_PORT;
		$IMAGE_AUTHENTICATION_TABLE="image_authentication_info";
		$event = new event();
		$htvcenter_server = new htvcenter_server();
		$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();

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

		// just for sending the commands
		$resource = new resource();
		// nothing todo

	}



?>


