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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
// ip mgmt class
require_once "$RootDir/plugins/opsi/class/opsi.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;


function htvcenter_opsi_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	$htvcenter_server = new htvcenter_server();
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$resource_mac=$resource->mac;
	$resource_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	// check appliance values, maybe we are in update and they are incomplete
	if ($appliance->imageid == 1) {
		return;
	}
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}

	// check if image is type opsi-deployment
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	// opsi configured in image deployment parameters ?
	$opsi_auto_install_enabled = false;
	$opsi_deployment_parameters = trim($image->get_deployment_parameter("INSTALL_CONFIG"));
	if (strlen($opsi_deployment_parameters)) {
		$opsi_deployment_parameter_arr = explode(":", $opsi_deployment_parameters);
		$local_deployment_persistent = $opsi_deployment_parameter_arr[0];
		$local_deployment_type = $opsi_deployment_parameter_arr[1];
		if (strcmp($local_deployment_type, "opsi-deployment")) {
			$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance_name." image is not from type opsi-deployment", "", "", 0, 0, $resource->id);
			return;
		}
		$opsi_server_storage_id = $opsi_deployment_parameter_arr[2];
		$opsi_netboot_products = $opsi_deployment_parameter_arr[3];
		$opsi_product_key = $opsi_deployment_parameter_arr[4];
		$opsi_auto_install_enabled = true;
	}

	// get domain name from dns plugin
	$dns_plugin_conf_file=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/dns/etc/htvcenter-plugin-dns.conf";
	if (!file_exists($dns_plugin_conf_file)) {
		$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "The Opsi-Plugin depends on the DNS-Plugin. Please enable and start the DNS-Plugin", "", "", 0, 0, $resource->id);
		return;
	}
	$store = htvcenter_parse_conf($dns_plugin_conf_file);
	extract($store);
	$resource_domain = $store['htvcenter_SERVER_DOMAIN'];
	if (!strlen($resource_domain)) {
		$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Could not get Domain-Name from DNS-Plugin. Please configure the DNS-Plugin", "", "", 0, 0, $resource->id);
		return;
	}


	$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":
			$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "START event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);

			if ($opsi_auto_install_enabled) {
				// prepare automatic-installation

				// get the opsi-server resource
				$opsi_storage = new storage();
				$opsi_storage->get_instance_by_id($opsi_server_storage_id);
				$opsi_server_resource = new resource();
				$opsi_server_resource->get_instance_by_id($opsi_storage->resource_id);

				// add client to opsi server, get resource_id from image-deployment parameters, runs on Opsi server
				$opsi_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi add_opsi_client -i ".$resource_ip." -m ".$resource_mac." -d ".$resource_domain." -n ".$appliance->name." --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "add_opsi_client $resource_ip", "", "", 0, 0, $resource->id);
				$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);
				sleep(2);

				// add netboot product to client, get product name from image-deployment parameters, runs on Opsi server
				$opsi_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi add_opsi_product_to_client -n ".$appliance->name." -d ".$resource_domain." -o ".$opsi_netboot_products." --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "add_opsi_product_to_client $opsi_netboot_products $resource_ip", "", "", 0, 0, $resource->id);
				$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);
				sleep(2);

				// add product key to client netboot product (sets askbeforeinstall to false)
				$opsi_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi add_opsi_product_key -n ".$appliance->name." -d ".$resource_domain." -o ".$opsi_netboot_products." -k ".$opsi_product_key." --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "add_opsi_product_key $resource_ip", "", "", 0, 0, $resource->id);
				$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);
				sleep(2);

				// add htvcenter-client product to client
				$opsi_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi add_opsi_product_to_client -n ".$appliance->name." -d ".$resource_domain." -o htvcenter-client --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "add_opsi_product_to_client htvcenter-client $resource_ip", "", "", 0, 0, $resource->id);
				$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);

				// create the install-info file on the opsi server, the htvcenter client auto-instllation
				// gets the htvcenter ip + resource-id from this file
				$opsi_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi add_client_install_info -n ".$appliance->name." -d ".$resource_domain." -x ".$resource->id." --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "add_client_install_info $resource->id", "", "", 0, 0, $resource->id);
				$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);

				// transfer client to opsi server, runs on htvcenter, we have to use the hostname resource+id and not the appliance-name since the resource is in the dhcpd.conf with this name
				$opsi_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi-manager transfer_to_opsi -o ".$opsi_server_resource->ip." -i ".$resource_ip." -m ".$resource_mac." -n resource".$resource->id." --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "transfer_to_opsi $resource_ip", "", "", 0, 0, $resource->id);
				$htvcenter_server->send_command($opsi_command);

				// Remove image-deployment paramters, if auto-install is a single-shot actions
				if (!strcmp($local_deployment_persistent, "0")) {
					$image->set_deployment_parameters("INSTALL_CONFIG", "");
				}
				// Set virtual-resource-command in image to true
				$image->set_deployment_parameters("IMAGE_VIRTUAL_RESOURCE_COMMAND", "true");

				// create opsi-state object to allow to run a late setboot to local command on the vm host
				$opsi_state = new opsistate();
				$opsi_state->remove_by_resource_id($resource->id);
				$opsi_state_fields=array();
				$opsi_state_fields["opsi_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$opsi_state_fields["opsi_resource_id"]=$resource->id;
				$opsi_state_fields["opsi_install_start"]=$_SERVER['REQUEST_TIME'];
				$opsi_state_fields["opsi_timeout"]=$opsi_install_timeout;
				$opsi_state->add($opsi_state_fields);

			} else {

				if (strcmp($image->type, "opsi-deployment")) {
					$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type opsi-deployment", "", "", 0, 0, $resource->id);
				} else {
					$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Setting resource $resource_ip to local-boot", "", "", 0, 0, $resource->id);
					// we have auto-installed already, if it is VM the opsiresource object will care to set the boot-sequence on the VM Host to local boot
					$opsiresource = new opsiresource();
					$opsiresource->set_boot($resource->id, 1);
					// set pxe config to local-boot
					$opsi_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi-manager set_opsi_client_to_local_boot -m ".$resource_mac." --htvcenter-cmd-mode background";
					$htvcenter_server->send_command($opsi_command);
				}
			}
			break;



		case "stop":

			if (strcmp($image->type, "opsi-deployment")) {
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type opsi-deployment", "", "", 0, 0, $resource->id);
			} else {
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "STOP event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
				// transfer client to htvcenter again
				$opsi_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi-manager take_over_from_opsi -i ".$resource_ip." -m ".$resource_mac." -n resource".$resource->id." --htvcenter-cmd-mode background";
				$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Take_over_from_opsi $resource_ip", "", "", 0, 0, $resource->id);
				$htvcenter_server->send_command($opsi_command);

				// remove opsi-state object if existing
				$opsi_state = new opsistate();
				$opsi_state->remove_by_resource_id($resource->id);
				// if it is VM the opsiresource object will care to set the boot-sequence on the VM Host to network boot
				$opsiresource = new opsiresource();
				$opsiresource->set_boot($resource->id, 0);

				// remove  client from opsi server
	#			$opsi_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/opsi/bin/htvcenter-opsi remove_opsi_client -d ".$resource_domain." -n ".$appliance->name;
	#			$event->log("htvcenter_opsi_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-appliance-hook.php", "Remove_opsi_client $resource_ip", "", "", 0, 0, $resource->id);
	#			$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);

				break;
			}
	}


}


?>


