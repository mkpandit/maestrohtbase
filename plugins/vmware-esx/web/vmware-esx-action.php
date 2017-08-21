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

$vmware_esx_command = $request->get('vmware_esx_command');
$vmware_esx_id = $request->get('vmware_esx_id');


global $htvcenter_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;

$cloud_product_hook = $RootDir.'/plugins/vmware-esx/htvcenter-vmware-esx-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';
$cloud_usergroup_class = $RootDir.'/plugins/cloud/class/cloudusergroup.class.php';

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "vmware-esx-action", "Un-Authorized access to vmware-esx-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}

$vmware_esx_name = $request->get('vmware_esx_name');
$vmware_esx_mac = $request->get('vmware_esx_mac');
$vmware_esx_ip = $request->get('vmware_esx_ip');
$vmware_esx_ram = $request->get('vmware_esx_ram');
$vmware_esx_disk = $request->get('vmware_esx_disk');

$vmware_esx_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "vmware_esx_", 14) == 0) {
		$vmware_esx_fields[$key] = $value;
	}
}
unset($vmware_esx_fields["vmware_esx_command"]);

	$event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-action", "Processing command $vmware_esx_command", "", "", 0, 0, 0);
	switch ($vmware_esx_command) {

	case 'init':
		// this command creates the following table
		// -> vmw_esx_auto_discovery
		// vmw_esx_ad_id BIGINT
		// vmw_esx_ad_ip VARCHAR(50)
		// vmw_esx_ad_mac VARCHAR(50)
		// vmw_esx_ad_hostname VARCHAR(50)
		// vmw_esx_ad_user VARCHAR(50)
		// vmw_esx_ad_password VARCHAR(50)
		// vmw_esx_ad_comment VARCHAR(255)
		// vmw_esx_ad_is_integrated BIGINT

		$create_vmw_auto_discovery_table = "create table vmw_esx_auto_discovery(vmw_esx_ad_id BIGINT, vmw_esx_ad_ip VARCHAR(255), vmw_esx_ad_mac VARCHAR(50), vmw_esx_ad_hostname VARCHAR(50), vmw_esx_ad_user VARCHAR(50), vmw_esx_ad_password VARCHAR(50), vmw_esx_ad_comment VARCHAR(255), vmw_esx_ad_is_integrated BIGINT)";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($create_vmw_auto_discovery_table);
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
					htvcenter_vmware_esx_cloud_product("add", $cloud_hook_config);
				}
			}
		}
		
		break;

	case 'uninstall':
		$drop_vmw_auto_discovery_table = "drop table vmw_esx_auto_discovery";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_vmw_auto_discovery_table);
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
					htvcenter_vmware_esx_cloud_product("remove", $cloud_hook_config);
				}
			}
		}
		
		break;




		default:
			$event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 3, "vmware-esx-action", "No such vmware-esx command ($vmware_esx_command)", "", "", 0, 0, 0);
			break;


	}
?>
