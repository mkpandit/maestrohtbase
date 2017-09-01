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




function string_to_mac($m) {
	$b = str_split($m, 2);	
	$c = implode(':', $b);
	return $c;
}

function string_to_array($string, $element_delimiter = '|', $value_delimiter = '=') {
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

function htvcenter_hyperv_monitor() {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $htvcenter_server;
	global $BaseDir;
	global $RootDir;
	$now=$_SERVER['REQUEST_TIME'];

	$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "Hyper-V monitor hook", "", "", 0, 0, 0);
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
		if (strcmp($virtualization->name, "Hyper-V Host")) {
			continue;
		}
		
		$hyperv_resource = new resource();
		$hyperv_resource->get_instance_by_id($appliance->resources);
		// lazy stats, keep the file if it is there
		$file = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/web/hyperv-stat/".$hyperv_resource->ip.".host_statistics";
		$last_stats = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/web/hyperv-stat/".$hyperv_resource->ip.".last_host_statistics";
		// read stats file
		$line_nubmer = 1;
		if (file_exists($file)) {
			$lines = explode("\n", file_get_contents($file));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = string_to_array($line, '|', '=');
						// first line is the host info
						if ($line_nubmer == 1) {
							$hyperv_hostname = $line['name'];
							$hyperv_cpunumber = $line['cpu'];
							$hyperv_physical_mem = $line['memory'];
							$hyperv_resource_fields["resource_hostname"]=$hyperv_hostname;
							$hyperv_resource_fields["resource_cpunumber"]=$hyperv_cpunumber;
							$hyperv_resource_fields["resource_memtotal"]=$hyperv_physical_mem;
							$hyperv_resource_fields["resource_state"]='active';
							$hyperv_resource_fields["resource_lastgood"]=$now;
							$hyperv_resource->update_info($hyperv_resource->id, $hyperv_resource_fields);
							unset($hyperv_hostname);
							unset($hyperv_cpu_number);
							unset($hyperv_physical_mem);
							unset($hyperv_resource_fields);

$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "-------- in Host list ".$appliance_id, "", "", 0, 0, 0);
							
						} else {
$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "-------- in VM list ".$appliance_id, "", "", 0, 0, 0);
							// vm infos
							$vm_status = $line['state'];
							$vm_mac = strtolower($line['mac']);
							if (!strlen($vm_mac)) {
								continue;
							}
							$vm_mac = string_to_mac($vm_mac);
$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "-------- in VM mac ".$vm_mac, "", "", 0, 0, 0);

							$vm_resource = new resource();
							$vm_resource->get_instance_by_mac($vm_mac);
							if (!$vm_resource->exists($vm_mac)) {
$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "-------- in VM does not exists ".$vm_mac, "", "", 0, 0, 0);
								continue;
							}
$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "-------- in VM does exists ".$vm_mac, "", "", 0, 0, 0);

							// networkboot VMs are sending stats on their own
							$hyperv_vm_local_virtualization = new virtualization();
							$hyperv_vm_local_virtualization->get_instance_by_type("hyperv-vm-local");
							if ($vm_resource->vtype == $hyperv_vm_local_virtualization->id) {
								
								$vm_resource_fields["resource_lastgood"]=$now;
								$vm_resource_fields["resource_event"]='statistics';
								if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
									if ($vm_status == 'active') {
										$vm_resource_fields["resource_state"]='active';
									} else {
										$vm_resource_fields["resource_state"]='active';
									}

								} else {
									if ($vm_status == 'active') {
										$vm_resource_fields["resource_state"]='active';
									} else {
										$vm_resource_fields["resource_state"]='error';
									}
								}		
								$vm_resource->update_info($vm_resource->id, $vm_resource_fields);
							}
							unset($vm_status);
							unset($vm_mac);
							unset($vm_resource_fields);
						}
						$line_nubmer++;
					}
				}
			}
		}
		unlink($file);
//		// send command
		$hyperv_host_monitor_cmd=$htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/hyperv/bin/htvcenter-hyperv-vm post_host_statistics -i ".$hyperv_resource->ip." --htvcenter-cmd-mode fork";
		if (file_exists($last_stats)) {
			$last_host_stats = file_get_contents($last_stats);
			$secs_after_last_host_stat = $now - $last_host_stats;
			if ($secs_after_last_host_stat > 60) {
				$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "Hyper-V monitor hook - checking appliance ".$appliance_id, "", "", 0, 0, 0);
				$event->log("hyperv_monitor", $_SERVER['REQUEST_TIME'], 5, "hyperv-monitor-hook", "Hyper-V ---- ".$hyperv_host_monitor_cmd, "", "", 0, 0, 0);
				$htvcenter_server->send_command($hyperv_host_monitor_cmd, NULL, true);
				file_put_contents($last_stats, $now);
			}
		} else {
			$htvcenter_server->send_command($hyperv_host_monitor_cmd, NULL, true);
			file_put_contents($last_stats, $now);
		}
	}
}


// for debugging
// htvcenter_hyperv_monitor();

?>

