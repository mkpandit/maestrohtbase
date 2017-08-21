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
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// ip mgmt class
require_once "$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php";

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// get command
$ip_mgmt_command = htmlobject_request('ip_mgmt_command');
$ip_mgmt_token = htmlobject_request('ip_mgmt_token');
$ip_mgmt_nic_id = htmlobject_request('ip_mgmt_nic_id');
$appliance_id = htmlobject_request('appliance_id');

// main
$event->log("$ip_mgmt_command", $_SERVER['REQUEST_TIME'], 5, "ip-mgmt-action", "Processing ip-mgmt command $ip_mgmt_command", "", "", 0, 0, 0);
switch ($ip_mgmt_command) {

	case 'get_config':
		$ipmgmt = new ip_mgmt();
		$ipmgmt_id = $ipmgmt->get_id_by_appliance_and_token($appliance_id, $ip_mgmt_token, $ip_mgmt_nic_id);
		if ($ipmgmt_id>0) {
			$ipmgmt_config_arr = $ipmgmt->get_config_by_id($ipmgmt_id);
			echo "NIC=".$ipmgmt_config_arr[0]['ip_mgmt_nic_id']."\n";
			echo "NETWORK=".$ipmgmt_config_arr[0]['ip_mgmt_network']."\n";
			echo "IPADDRESS=".$ipmgmt_config_arr[0]['ip_mgmt_address']."\n";
			echo "SUBNET=".$ipmgmt_config_arr[0]['ip_mgmt_subnet']."\n";
			echo "BROADCAST=".$ipmgmt_config_arr[0]['ip_mgmt_broadcast']."\n";
			echo "GATEWAY=".$ipmgmt_config_arr[0]['ip_mgmt_gateway']."\n";
			echo "DNS1=".$ipmgmt_config_arr[0]['ip_mgmt_dns1']."\n";
			echo "DNS2=".$ipmgmt_config_arr[0]['ip_mgmt_dns2']."\n";
			echo "DOMAIN=".$ipmgmt_config_arr[0]['ip_mgmt_domain']."\n";
			echo "VLAN_ID=".$ipmgmt_config_arr[0]['ip_mgmt_vlan_id']."\n";
		}
		break;



	default:
	case 'get_windows_config':
		$ipmgmt = new ip_mgmt();
		$appliance = new appliance();
		$appliance_id_list = $appliance->get_all_ids();
		foreach($appliance_id_list as $id) {
			$app_id = $id['appliance_id'];
			$app_caps = $appliance->get_capabilities($app_id);
			$pos = strpos($app_caps, $ip_mgmt_token);
			if ($pos !== false) {
				for ($nic=0; $nic<5; $nic++) {
					$ipmgmt_id = $ipmgmt->get_id_by_appliance_and_token($app_id, $ip_mgmt_token, $nic);
					if ($ipmgmt_id>0) {
						$ipmgmt_config_arr = $ipmgmt->get_config_by_id($ipmgmt_id);
						$win_nic = $nic;
						$win_ip = $ipmgmt_config_arr[0]['ip_mgmt_address'];
						$win_subnet = $ipmgmt_config_arr[0]['ip_mgmt_subnet'];
						$win_default_gw = $ipmgmt_config_arr[0]['ip_mgmt_gateway'];
						$win_dns1 = $ipmgmt_config_arr[0]['ip_mgmt_dns1'];
						$win_dns2 = $ipmgmt_config_arr[0]['ip_mgmt_dns2'];
						$win_domain = $ipmgmt_config_arr[0]['ip_mgmt_domain'];
						if ($win_nic == 1) {
							echo "netsh.exe interface ipv4 set address \"Lan-Verbindung\" static ".$win_ip." ".$win_subnet." ".$win_default_gw."\n\r";
						} else {
							echo "netsh.exe interface ipv4 set address \"Lan-Verbindung ".$win_nic."\" static ".$win_ip." ".$win_subnet." ".$win_default_gw."\n\r";
						}
					}
				}
				break;
			}
		}

}

?>
