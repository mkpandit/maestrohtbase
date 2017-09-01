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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/class/event.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;



// this function is going to be called by the monitor-hook in the resource-monitor

function htvcenter_vmware_esx_monitor() {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $htvcenter_server;
	global $BaseDir;
	global $RootDir;
	$now=$_SERVER['REQUEST_TIME'];

	// $event->log("vmware_esx_monitor", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-monitor-hook", "VMware ESX monitor hook", "", "", 0, 0, 0);
	$appliance = new appliance();
	$appliance_id_arr = $appliance->get_all_ids();
	foreach ($appliance_id_arr as $appliance_arr) {
		$appliance_id = $appliance_arr['appliance_id'];
		$appliance->get_instance_by_id($appliance_id);
		// check appliance values, maybe we are in update and they are incomplete
		if ($appliance->imageid == 1) {
			continue;
		}
		if (($appliance->resources == "-1") || ($appliance->resources == "")) {
			continue;
		}
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance->virtualization);
		if (strcmp($virtualization->name, "ESX Host")) {
			continue;
		}
		$vmware_esx_resource = new resource();
		$vmware_esx_resource->get_instance_by_id($appliance->resources);
		// lazy stats, keep the file if it is there
		$file = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_esx_resource->ip.".host_statistics";
		$last_stats = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_esx_resource->ip.".last_host_statistics";
		// read stats file
		$line_nubmer = 1;
		if (file_exists($file)) {
			$lines = explode("\n", file_get_contents($file));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						// first line is the host info
						if ($line_nubmer == 1) {
							$esx_hostname = $line[0];
							$esx_cpu_speed = $line[1];
							$esx_cpu_load = $line[2];
							$esx_cpu_physical_mem = $line[3];
							$esx_cpu_used_mem = $line[4];
							$esx_cpu_network_cards = $line[5];
							$esx_resource_fields["resource_hostname"]=$esx_hostname;
							$esx_resource_fields["resource_cpuspeed"]=$esx_cpu_speed;
							$esx_resource_fields["resource_load"]=$esx_cpu_load;
							$esx_resource_fields["resource_memtotal"]=$esx_cpu_physical_mem;
							$esx_resource_fields["resource_memused"]=$esx_cpu_used_mem;
							$esx_resource_fields["resource_nics"]=$esx_cpu_network_cards;
							$esx_resource_fields["resource_state"]='active';
							$esx_resource_fields["resource_lastgood"]=$now;
							$vmware_esx_resource->update_info($vmware_esx_resource->id, $esx_resource_fields);
							unset($esx_hostname);
							unset($esx_cpu_speed);
							unset($esx_cpu_load);
							unset($esx_cpu_physical_mem);
							unset($esx_cpu_used_mem);
							unset($esx_cpu_network_cards);
							unset($esx_resource_fields);
						} else {
							// vm infos
							$vm_name = $line[0];
							$vm_status = $line[1];
							$vm_cpu = $line[2];
							$vm_mem = $line[3];
							$vm_first_nic_str = explode(',', $line[4]);
							$vm_mac = strtolower($vm_first_nic_str[0]);
							if (!strlen($vm_mac)) {
								continue;
							}
							$vm_nic = 1;
							$vm_disk = $line[8];
							// filter ds/vmdk
							$ds_start_marker = strpos($line[8], '[');
							$ds_start_marker++;
							$ds_end_marker = strpos($line[8], ']');
							$vm_datastore = substr($line[8], $ds_start_marker, $ds_end_marker - $ds_start_marker);
							$vm_datastore = trim($vm_datastore);
							$vm_datastore_filename = substr($line[8], $ds_end_marker+1);
							$vm_datastore_filename = trim($vm_datastore_filename);
							$vm_image_name = str_replace(".vmdk", "", basename($vm_datastore_filename));
							$vm_boot = $line[12];

							if (!strlen($vm_mac)) {
								continue;
							}
							$vm_resource = new resource();
							$vm_resource->get_instance_by_mac($vm_mac);
							if (!$vm_resource->exists($vm_mac)) {
								continue;
							}

							// networkboot VMs are sending stats on their own
							$esx_vm_local_virtualization = new virtualization();
							$esx_vm_local_virtualization->get_instance_by_type("vmware-esx-vm-local");
							if ($vm_resource->vtype == $esx_vm_local_virtualization->id) {
								
								if (strlen($vm_image_name)) {
									// assigned
									if ($vm_status == 'active') {
										$vm_resource_fields["resource_state"]='active';
										$vm_resource_fields["resource_lastgood"]=$now;
									}
									if ($vm_status == 'inactive') {
										$vm_resource_fields["resource_state"]='active';
										$vm_resource_fields["resource_lastgood"]=$now;
									}
								} else {
									// idle
									$vm_resource_fields["resource_state"]='active';
									$vm_resource_fields["resource_lastgood"]=$now;
								}
								
								// additional nics
								$vm_nic = 1;
								$add_nic_arr = explode('/', $line[5]);
								foreach($add_nic_arr as $add_nic) {
									if (strlen($add_nic)) {
										$vm_nic++;
									}
								}
								$vm_resource_fields["resource_event"]='statistics';
								$vm_resource_fields["resource_hostname"]=$vm_name;
								$vm_resource_fields["resource_cpunumber"]=$vm_cpu;
								$vm_resource_fields["resource_memtotal"]=$vm_mem;
								$vm_resource_fields["resource_memused"]=$vm_mem;
								$vm_resource_fields["resource_nics"]=$vm_nic;
								$vm_resource->update_info($vm_resource->id, $vm_resource_fields);

								unset($vm_name);
								unset($vm_status);
								unset($vm_first_nic_str);
								unset($vm_mac);
								unset($vm_nic);
								unset($vm_cpu);
								unset($vm_mem);
								unset($vm_resource_fields);
							}
						}
						$line_nubmer++;
					}
				}
			}
			unlink($file);
		}
		// send command
		$vmware_esx_host_monitor_cmd=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-esx/bin/htvcenter-vmware-esx-vm post_host_statistics -i ".$vmware_esx_resource->ip." --htvcenter-cmd-mode background";
		if (file_exists($last_stats)) {
			$last_host_stats = file_get_contents($last_stats);
			$secs_after_last_host_stat = $now - $last_host_stats;
			if ($secs_after_last_host_stat > 60) {
				$event->log("vmware_esx_monitor", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-monitor-hook", "VMware ESX monitor hook - checking appliance ".$appliance_id, "", "", 0, 0, 0);
				$htvcenter_server->send_command($vmware_esx_host_monitor_cmd, NULL, true);
				file_put_contents($last_stats, $now);
			}
		} else {
			$htvcenter_server->send_command($vmware_esx_host_monitor_cmd, NULL, true);
			file_put_contents($last_stats, $now);
		}
	}
}

?>

