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
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
# class for the hybrid cloud accounts
require_once $RootDir."/plugins/hybrid-cloud/class/hybrid-cloud.class.php";


global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;



function htvcenter_hybrid_cloud_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $RootDir;

	$htvcenter_server = new htvcenter_server();

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

	$event->log("htvcenter_hybrid_cloud_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-hybrid-cloud-appliance-hook.php", "Handling ".$cmd." event ".$appliance_id."/".$appliance_name."/".$appliance_ip, "", "", 0, 0, $appliance_id);

	// check resource type -> hybrid-cloud-strorage-vm
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($resource->vtype);

	switch($virtualization->type) {
		case "hybrid-cloud-vm-local":
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);

			// get hybrid-cloud account
			$hybrid_cloud_acl_id = $resource->get_resource_capabilities("HCACL");
			if ($hybrid_cloud_acl_id == '') {
				$event->log("htvcenter_hybrid_cloud_appliance", $_SERVER['REQUEST_TIME'], 2, "htvcenter-hybrid-cloud-appliance-hook.php", "Could not find Hybrid-Cloud Account for resource ".$resource->id, "", "", 0, 0, $appliance_id);
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
				$account_file_dir = $htvcenter_SERVER_BASE_DIR.'/htvcenter/plugins/hybrid-cloud/etc/acl';
				$random_file_name  = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$filename = $account_file_dir."/".$random_file_name;
				file_put_contents($filename, $hc_keyfile);
				$hc_authentication .= ' -k '.$filename;
			}

			$statfile = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hybrid-cloud/web/hybrid-cloud-stat/".$hybrid_cloud_acl_id.".run_instances.hostname";

			switch($cmd) {
				case "start":
					// send command to assign image and start instance
					$command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hybrid-cloud/bin/htvcenter-hybrid-cloud-vm run ";
					$command .= ' -i '.$hc->id;
					$command .= ' -n '.$hc->account_name;
					$command .= ' -t '.$hc->account_type;
					$command .= $hc_authentication;
					$command .= ' -in '.$resource->hostname;
					$command .= ' -a '.$image->rootdevice;
					$command .= ' -ii '.$image->id;
					$command .= ' -ia '.$appliance->name;
					$command .= ' --htvcenter-cmd-mode background';
					// wait for hostname statfile
					if (file_exists($statfile))	{
						unlink($statfile);
					}
					$htvcenter_server->send_command($command, NULL, true);
					while (!file_exists($statfile))	{
					  usleep(10000);
					  clearstatcache();
					}
					// special hostname handling for aws + euca
					if (($hc->account_type == 'aws') || ($hc->account_type == 'euca')) {
						$resource_new_hostname = file_get_contents($statfile);
						$resource_new_hostname = trim($resource_new_hostname);
						unlink($statfile);
						// update hostname in resource
						$resource_fields["resource_hostname"]=$resource_new_hostname;
						$resource->update_info($resource->id, $resource_fields);
					}
					// reset image_isactive -> AMI are cloned anyway
					$image->set_active(1);
					break;

				case "stop":
					// send command to stop the vm and deassign image
					$command=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hybrid-cloud/bin/htvcenter-hybrid-cloud-vm terminate ";
					$command .= ' -i '.$hc->id;
					$command .= ' -n '.$hc->account_name;
					$command .= ' -t '.$hc->account_type;
					$command .= $hc_authentication;
					$command .= ' -in '.$resource->hostname;
					$command .= ' --htvcenter-cmd-mode background';
					$htvcenter_server->send_command($command, NULL, true);

					// special hostname handling for aws + euca
					if (($hc->account_type == 'aws') || ($hc->account_type == 'euca')) {
						$resource_new_hostname = $hc->account_type.$resource->id;
						// update hostname in resource
						$resource_fields["resource_hostname"]=$resource_new_hostname;
						$resource->update_info($resource->id, $resource_fields);
					}
					break;
			}
			break;
	}
}



?>


