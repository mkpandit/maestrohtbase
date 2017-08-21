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
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;
$cloud_product_hook = $RootDir.'/plugins/ip-mgmt/htvcenter-ip-mgmt-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';
$cloud_usergroup_class = $RootDir.'/plugins/cloud/class/cloudusergroup.class.php';

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "ip-mgmt-action", "Un-Authorized access to ip-mgmt-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$ip_mgmt_command = $request->get('ip_mgmt_command');

// main
$event->log("$ip_mgmt_command", $_SERVER['REQUEST_TIME'], 5, "ip-mgmt-action", "Processing ip-mgmt command $ip_mgmt_command", "", "", 0, 0, 0);
switch ($ip_mgmt_command) {

	case 'init':
		// this command creates the following table
		// -> ip_mgmt_locations
		// ip_mgmt_id BIGINT
		// ip_mgmt_token VARCHAR(50)
		// ip_mgmt_name VARCHAR(50)
		// ip_mgmt_user_id BIGINT
		// ip_mgmt_appliance_id BIGINT
		// ip_mgmt_nic_id BIGINT
		// ip_mgmt_state BIGINT
		// ip_mgmt_network VARCHAR(50)
		// ip_mgmt_address VARCHAR(50)
		// ip_mgmt_subnet VARCHAR(50)
		// ip_mgmt_broadcast VARCHAR(50)
		// ip_mgmt_gateway VARCHAR(50)
		// ip_mgmt_dns1 VARCHAR(50)
		// ip_mgmt_dns2 VARCHAR(50)
		// ip_mgmt_domain VARCHAR(255)
		// ip_mgmt_vlan_id VARCHAR(50)
		// ip_mgmt_comment VARCHAR(255)

		$create_ip_mgmt_table = "create table ip_mgmt(ip_mgmt_id BIGINT, ip_mgmt_token VARCHAR(255), ip_mgmt_name VARCHAR(50), ip_mgmt_user_id BIGINT, ip_mgmt_appliance_id BIGINT, ip_mgmt_nic_id BIGINT, ip_mgmt_state BIGINT, ip_mgmt_network VARCHAR(50), ip_mgmt_address VARCHAR(50), ip_mgmt_subnet VARCHAR(50), ip_mgmt_broadcast VARCHAR(50), ip_mgmt_gateway VARCHAR(50), ip_mgmt_dns1 VARCHAR(50), ip_mgmt_dns2 VARCHAR(50), ip_mgmt_domain VARCHAR(255), ip_mgmt_vlan_id VARCHAR(50), ip_mgmt_comment VARCHAR(255))";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_ip_mgmt_table);
	
		$create_private_default_net = "INSERT INTO ip_mgmt VALUES (14037823711524,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.2','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,'A private network'),(14037823711600,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.3','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711642,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.4','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711676,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.5','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711692,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.6','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711718,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.7','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711754,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.8','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711790,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.9','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711817,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.10','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711849,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.11','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711878,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.12','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711956,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.13','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823711999,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.14','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712030,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.15','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712049,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.16','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712081,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.17','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712112,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.18','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712127,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.19','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712153,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.20','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712169,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.21','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712198,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.22','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712229,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.23','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712248,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.24','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712278,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.25','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712309,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.26','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712327,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.27','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712356,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.28','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712386,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.29','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712406,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.30','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712437,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.31','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712471,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.32','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712489,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.33','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712519,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.34','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712548,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.35','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712568,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.36','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712605,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.37','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712636,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.38','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712666,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.39','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712684,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.40','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712713,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.41','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712732,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.42','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712761,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.43','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712791,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.44','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712809,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.45','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712838,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.46','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712868,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.47','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712886,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.48','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712928,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.49','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL),(14037823712972,NULL,'Private',NULL,NULL,NULL,NULL,'10.10.149.0','10.10.149.50','255.255.255.0','10.10.149.255',NULL,NULL,NULL,NULL,NULL,NULL);";
		$recordSet = $db->Execute($create_private_default_net);
		$db->Close();

		// add cloud products
		if (file_exists($cloud_usergroup_class)) {
			require_once $cloud_usergroup_class;
			$cloud_project = new cloudusergroup();
			$cloud_project->get_instance_by_name('Admin');
			if (file_exists($cloud_product_hook)) {
				$cloud_hook_config = array();
				$cloud_hook_config['cloud_admin_procect'] = $cloud_project->id;
				require_once $cloud_product_hook;
				htvcenter_ip_mgmt_cloud_product("add", $cloud_hook_config);
			}
		}
		break;

	case 'uninstall':
		$drop_ip_mgmt_table = "drop table ip_mgmt";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_ip_mgmt_table);
		$db->Close();
		break;


	default:
		$event->log("$ip_mgmt_command", $_SERVER['REQUEST_TIME'], 3, "ip-mgmt-action", "No such event command ($ip_mgmt_command)", "", "", 0, 0, 0);
		break;


}

?>
