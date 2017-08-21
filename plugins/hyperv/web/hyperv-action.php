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


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htvcenter-server-config.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$hyperv_command = $request->get('hyperv_command');
$hyperv_id = $request->get('hyperv_id');


global $htvcenter_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;

$cloud_product_hook = $RootDir.'/plugins/hyperv/htvcenter-hyperv-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';
$cloud_usergroup_class = $RootDir.'/plugins/cloud/class/cloudusergroup.class.php';

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "hyperv-action", "Un-Authorized access to hyperv-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}

$hyperv_name = $request->get('hyperv_name');
$hyperv_mac = $request->get('hyperv_mac');
$hyperv_ip = $request->get('hyperv_ip');
$hyperv_ram = $request->get('hyperv_ram');
$hyperv_disk = $request->get('hyperv_disk');

$hyperv_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "hyperv_", 14) == 0) {
		$hyperv_fields[$key] = $value;
	}
}
unset($hyperv_fields["hyperv_command"]);

	$event->log("$hyperv_command", $_SERVER['REQUEST_TIME'], 5, "hyperv-action", "Processing command $hyperv_command", "", "", 0, 0, 0);
	switch ($hyperv_command) {

	case 'init':
		// this commands are creating the following tables
		// -> hyperv_auto_discovery
		// hyperv_ad_id BIGINT
		// hyperv_ad_ip VARCHAR(50)
		// hyperv_ad_mac VARCHAR(50)
		// hyperv_ad_hostname VARCHAR(50)
		// hyperv_ad_user VARCHAR(50)
		// hyperv_ad_password VARCHAR(50)
		// hyperv_ad_comment VARCHAR(255)
		// hyperv_ad_is_integrated BIGINT

		// -> hyperv_pools
		// hyperv_pool_id BIGINT
		// hyperv_pool_name VARCHAR(100)
		// hyperv_pool_path VARCHAR(255)
		// hyperv_pool_comment VARCHAR(255)
		
		$create_hyperv_auto_discovery_table = "create table hyperv_auto_discovery(hyperv_ad_id BIGINT, hyperv_ad_ip VARCHAR(255), hyperv_ad_mac VARCHAR(50), hyperv_ad_hostname VARCHAR(50), hyperv_ad_user VARCHAR(50), hyperv_ad_password VARCHAR(50), hyperv_ad_comment VARCHAR(255), hyperv_ad_is_integrated BIGINT)";
		$create_hyperv_pool_table = "create table hyperv_pool(hyperv_pool_id BIGINT, hyperv_pool_name VARCHAR(100), hyperv_pool_path VARCHAR(255), hyperv_pool_comment VARCHAR(255))";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_hyperv_auto_discovery_table);
		$recordSet = $db->Execute($create_hyperv_pool_table);
		$db->Close();
		
		// add cloud products
		if (file_exists($cloud_usergroup_class)) {
			require_once $cloud_usergroup_class;
			$cloud_project = new cloudusergroup();
			$cloud_project->get_instance_by_name('Admin');
			if (file_exists($cloud_selector_class)) {
				if (file_exists($cloud_product_hook)) {
					$cloud_hook_config = array();
					$cloud_hook_config['cloud_admin_procect'] = $cloud_project->id;
					require_once $cloud_product_hook;
					htvcenter_hyperv_cloud_product("add", $cloud_hook_config);
				}
			}
		}
		
		break;

	case 'uninstall':
		$drop_hyperv_auto_discovery_table = "drop table hyperv_auto_discovery";
		$drop_hyperv_pool_table = "drop table hyperv_pool";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_hyperv_auto_discovery_table);
		$recordSet = $db->Execute($drop_hyperv_pool_table);
		$db->Close();
		
		// remove cloud products
		if (file_exists($cloud_usergroup_class)) {
			require_once $cloud_usergroup_class;
			$cloud_project = new cloudusergroup();
			$cloud_project->get_instance_by_name('Admin');
			if (file_exists($cloud_selector_class)) {
				if (file_exists($cloud_product_hook)) {
					$cloud_hook_config = array();
					$cloud_hook_config['cloud_admin_procect'] = $cloud_project->id;
					require_once $cloud_product_hook;
					htvcenter_hyperv_cloud_product("remove", $cloud_hook_config);
				}
			}
		}
		
		break;




		default:
			$event->log("$hyperv_command", $_SERVER['REQUEST_TIME'], 3, "hyperv-action", "No such hyperv command ($hyperv_command)", "", "", 0, 0, 0);
			break;


	}
?>
