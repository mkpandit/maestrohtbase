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
# class for the hybrid cloud accounts
require_once $RootDir."/plugins/hybrid-cloud/class/hybrid-cloud.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function htvcenter_hybrid_cloud_resource_virtual_command($cmd, $resource_fields) {
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
		case "hybrid-cloud":
			$event->log("htvcenter_hybrid_cloud_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hybrid-cloud-resource-virtual-command-hook.php", "Handling ".$cmd." command of resource ".$resource->id, "", "", 0, 0, 0);
			// noop
			break;

		case "hybrid-cloud-vm-local":
			$event->log("htvcenter_hybrid_cloud_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hybrid-cloud-resource-virtual-command-hook.php", "Handling ".$cmd." command of resource ".$resource->id, "", "", 0, 0, 0);
			$htvcenter_server = new htvcenter_server();

			// get hybrid-cloud account
			$hybrid_cloud_acl_id = $resource->get_resource_capabilities("HCACL");
			if ($hybrid_cloud_acl_id == '') {
				$event->log("htvcenter_hybrid_cloud_resource_virtual_command", $_SERVER['REQUEST_TIME'], 2, "htvcenter-hybrid-cloud-resource-virtual-command-hook.php", "Could not find Hybrid-Cloud Account for resource ".$resource->id, "", "", 0, 0, $appliance_id);
				return;
			}
			$hc = new hybrid_cloud();
			$hc->get_instance_by_id($hybrid_cloud_acl_id);

			$hc_authentication = '';
			if (($hc->account_type == 'aws') || ($hc->account_type == 'euca')) {
				$hc_authentication .= ' -O '.$hc->access_key;
				$hc_authentication .= ' -W '.$hc->secret_key;
			}
			if ($hc->account_type == 'lc-openstack') {
				$hc_authentication .= ' -u '.$hc->username;
				$hc_authentication .= ' -p '.$hc->password;
				$hc_authentication .= ' -q '.$hc->host;
				$hc_authentication .= ' -x '.$hc->port;
				$hc_authentication .= ' -g '.$hc->tenant;
				$hc_authentication .= ' -e '.$hc->endpoint;
			}
			if ($hc->account_type == 'lc-azure') {
				$hc_authentication .= ' -s '.$hc->subscription_id;
				$hc_keyfile = $hc->keyfile;
				$account_file_dir = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hybrid-cloud/etc/acl";
				$random_file_name  = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$filename = $account_file_dir."/".$random_file_name;
				file_put_contents($filename, $hc_keyfile);
				$hc_authentication .= ' -k '.$filename;
			}

			switch($cmd) {
				case "reboot":
					$command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hybrid-cloud/bin/htvcenter-hybrid-cloud-vm restart ";
					$command .= ' -i '.$hc->id;
					$command .= ' -n '.$hc->account_name;
					$command .= ' -t '.$hc->account_type;
					$command .= ' -in '.$resource->hostname;
					$command .= $hc_authentication;
					$command .= ' --htvcenter-cmd-mode background';
					$htvcenter_server->send_command($command, NULL, true);
					break;
				case "halt":
					$command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hybrid-cloud/bin/htvcenter-hybrid-cloud-vm stop ";
					$command .= ' -i '.$hc->id;
					$command .= ' -n '.$hc->account_name;
					$command .= ' -t '.$hc->account_type;
					$command .= ' -in '.$resource->hostname;
					$command .= $hc_authentication;
					$command .= ' --htvcenter-cmd-mode background';
					$htvcenter_server->send_command($command, NULL, true);
					break;
			}
			break;


	}

}



?>