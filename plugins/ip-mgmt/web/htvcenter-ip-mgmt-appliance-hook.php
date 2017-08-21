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
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
// ip mgmt class
require_once "$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php";
// external dns hook
require_once "$RootDir/plugins/ip-mgmt/htvcenter-ip-mgmt-external-dns-hook.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;




//--------------------------------------------------
/**
* set the appliance capabilities field
* @access public
* @param int $appliance_id
* @param string $key
* @param string $value
*/
//--------------------------------------------------
function set_appliance_capabilities($appliance_id, $key, $value) {
	$appliance_cap = new appliance();
	$appliance_cap->get_instance_by_id($appliance_id);
	$appliance_capabilities = $appliance_cap->capabilities;
	$key=trim($key);
	$cp1=trim($appliance_capabilities);
	$cp2 = strstr($cp1, $key);
	$keystr="$key=\"";
	$endmark="\"";
	$cp3=str_replace($keystr, "", $cp2);
	$endpos=strpos($cp3, $endmark);
	$cp=substr($cp3, 0, $endpos);

	if (strlen($value)) {
		if (strstr($appliance_capabilities, $key)) {
			// change
			$new_appliance_capabilities = str_replace("$key=\"$cp\"", "$key=\"$value\"", $appliance_capabilities);
		} else {
			// add
			$new_appliance_capabilities = "$appliance_capabilities $key=\"$value\"";
		}
	} else {
		// we remove the complete key+value
		$new_appliance_capabilities = str_replace("$key=\"$cp\"", "", $appliance_capabilities);
		$new_appliance_capabilities = str_replace("$key=", "", $new_appliance_capabilities);
	}

	$appliance_fields=array();
	$appliance_fields["appliance_capabilities"]="$new_appliance_capabilities";
	$appliance_cap->update($appliance_id, $appliance_fields);

}



function htvcenter_ip_mgmt_appliance($cmd, $appliance_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RootDir;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	// check appliance values, maybe we are in update and they are incomplete
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}

	$event->log("htvcenter_ip_mgmt_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ip-mgmt-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	switch($cmd) {
		case "start":
			$event->log("htvcenter_ip_mgmt_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ip-mgmt-appliance-hook.php", "START event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
			// do we have nics to configure ?
			if ($resource->nics > 0) {
				$gen_token="(date; cat /proc/interrupts) | md5sum | awk {' print $1 '}";
				$ip_mgmt_token=exec($gen_token);
				for ($n=1; $n<=$resource->nics; $n++) {
					$ipmgmt = new ip_mgmt();
					$ipmgmt_id = $ipmgmt->get_id_by_appliance($appliance_id, $n);
					if ($ipmgmt_id>0) {
						$ipmgmt->set_state($ipmgmt_id, 1, $ip_mgmt_token);
						// external dns hook
						if ($n == 1) {
							$ipmgmt_config_arr = $ipmgmt->get_config_by_id($ipmgmt_id);
							$appliance_external_ip = $ipmgmt_config_arr[0]['ip_mgmt_address'];
							htvcenter_ip_mgmt_external_dns_hook($cmd, $appliance_id, $appliance_external_ip);
						}
					}
				}
				// set appliance capabilies
				set_appliance_capabilities($appliance_id, "IPT", $ip_mgmt_token);
			}
			break;
		case "remove":
		case "stop":
			$event->log("htvcenter_ip_mgmt_appliance", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ip-mgmt-appliance-hook.php", "STOP event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
			// do we have nics to un-configure ?
			if ($resource->nics > 0) {
				for ($n=1; $n<=$resource->nics; $n++) {
					$ipmgmt = new ip_mgmt();
					$ipmgmt_id = $ipmgmt->get_id_by_appliance($appliance_id, $n);
					if ($ipmgmt_id>0) {
						$ipmgmt->set_state($ipmgmt_id, 0, 0);
						// external dns hook
						if ($n == 1) {
							$ipmgmt_config_arr = $ipmgmt->get_config_by_id($ipmgmt_id);
							$appliance_external_ip = $ipmgmt_config_arr[0]['ip_mgmt_address'];
							htvcenter_ip_mgmt_external_dns_hook($cmd, $appliance_id, $appliance_external_ip);
						}
					}
				}
				// set appliance capabilies
				set_appliance_capabilities($appliance_id, "IPT", "");
			}
			break;

	}
}

// debug
//$appliance_fields["appliance_id"]=3;
//$appliance_fields["appliance_name"]="ubuntu64";
//$appliance_fields["appliance_resources"]=7;
//htvcenter_ip_mgmt_appliance("start", $appliance_fields)

?>


