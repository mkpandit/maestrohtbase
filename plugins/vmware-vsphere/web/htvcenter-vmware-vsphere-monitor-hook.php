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




function htvcenter_vmware_vsphere_string_to_array($string, $element_delimiter = '|', $value_delimiter = '=') {
	$results = array();
	$array = explode($element_delimiter, $string);
	foreach ($array as $result) {
		$element = explode($value_delimiter, $result);
		if (isset($element[1])) {
			$results[$element[0]] = $element[1];
		}
	}
	return $results;
}



// this function is going to be called by the monitor-hook in the resource-monitor

function htvcenter_vmware_vsphere_monitor() {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $htvcenter_server;
	global $BaseDir;
	global $RootDir;
	$now=$_SERVER['REQUEST_TIME'];

	// $event->log("vmware_vsphere_monitor", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-monitor-hook", "VMware vSphere monitor hook", "", "", 0, 0, 0);
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
		if (strcmp($virtualization->name, "vSphere Host")) {
			continue;
		}
		$vmware_vsphere_resource = new resource();
		$vmware_vsphere_resource->get_instance_by_id($appliance->resources);
		// lazy stats, keep the file if it is there
		$file = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/web/vmware-vsphere-stat/".$vmware_vsphere_resource->ip.".host_statistics";
		$last_stats = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/web/vmware-vsphere-stat/".$vmware_vsphere_resource->ip.".last_host_statistics";
		// read stats file
		$line_nubmer = 1;
		if (file_exists($file)) {
			$lines = explode("\n", file_get_contents($file));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = htvcenter_vmware_vsphere_string_to_array($line, '|', '=');
						// first line is the host info
						if ($line_nubmer == 1) {
							$vsphere_hostname = $appliance->name;
							// $vsphere_cpu_speed = $line[1];
							$vsphere_cpu_load = $line['overallCpuUsage'];
							// $vsphere_cpu_physical_mem = $line[3];
							$vsphere_cpu_used_mem = $line['overallMemoryUsage'];
							// $vsphere_cpu_network_cards = $line[5];
							$vsphere_resource_fields["resource_hostname"]=$vsphere_hostname;
							// $vsphere_resource_fields["resource_cpuspeed"]=$vsphere_cpu_speed;
							$vsphere_resource_fields["resource_load"]=$vsphere_cpu_load;
							// $vsphere_resource_fields["resource_memtotal"]=$vsphere_cpu_physical_mem;
							$vsphere_resource_fields["resource_memused"]=$vsphere_cpu_used_mem;
							// $vsphere_resource_fields["resource_nics"]=$vsphere_cpu_network_cards;
							$vsphere_resource_fields["resource_state"]='active';
							$vsphere_resource_fields["resource_lastgood"]=$now;
							$vmware_vsphere_resource->update_info($vmware_vsphere_resource->id, $vsphere_resource_fields);
							unset($vsphere_hostname);
							unset($vsphere_cpu_speed);
							unset($vsphere_cpu_load);
							unset($vsphere_cpu_physical_mem);
							unset($vsphere_cpu_used_mem);
							unset($vsphere_cpu_network_cards);
							unset($vsphere_resource_fields);
						} else {
							// vm infos
							$vm_name = $line['name'];
							$vm_status = $line['powerState'];
							$vm_cpu = $line['numCpu'];
							$vm_mem = $line['memorySizeMB'];
							$vm_first_nic_str = explode(',', $line['macAddress']);
							$vm_mac = strtolower($vm_first_nic_str[0]);
							if (!strlen($vm_mac)) {
								continue;
							}
							$vm_nic = $line['numEthernetCards'];
							$vm_first_disk = $line['fileName'];
							$vm_disk = strtolower($vm_first_disk[0]);

							
							// filter ds/vmdk
							// $ds_start_marker = strpos($line[8], '[');
							// $ds_start_marker++;
							// $ds_end_marker = strpos($line[8], ']');
							// $vm_datastore = substr($line[8], $ds_start_marker, $ds_end_marker - $ds_start_marker);
							// $vm_datastore = trim($vm_datastore);
							// $vm_datastore_filename = substr($line[8], $ds_end_marker+1);
							// $vm_datastore_filename = trim($vm_datastore_filename);
							// $vm_image_name = str_replace(".vmdk", "", basename($vm_datastore_filename));
							// $vm_boot = $line[12];

							$vm_resource = new resource();
							$vm_resource->get_instance_by_mac($vm_mac);
							if (!$vm_resource->exists($vm_mac)) {
								continue;
							}

							// networkboot VMs are sending stats on their own
							$vsphere_vm_local_virtualization = new virtualization();
							$vsphere_vm_local_virtualization->get_instance_by_type("vmware-vsphere-vm-local");
							if ($vm_resource->vtype == $vsphere_vm_local_virtualization->id) {
								
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
		$vmware_vsphere_host_monitor_cmd=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm post_host_statistics -i ".$vmware_vsphere_resource->ip." --htvcenter-cmd-mode background";
		if (file_exists($last_stats)) {
			$last_host_stats = file_get_contents($last_stats);
			$secs_after_last_host_stat = $now - $last_host_stats;
			if ($secs_after_last_host_stat > 60) {
				$event->log("vmware_vsphere_monitor", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-monitor-hook", "VMware vSphere monitor hook - checking appliance ".$appliance_id, "", "", 0, 0, 0);
				$htvcenter_server->send_command($vmware_vsphere_host_monitor_cmd, NULL, true);
				file_put_contents($last_stats, $now);
			}
		} else {
			$htvcenter_server->send_command($vmware_vsphere_host_monitor_cmd, NULL, true);
			file_put_contents($last_stats, $now);
		}
	}
}

?>

