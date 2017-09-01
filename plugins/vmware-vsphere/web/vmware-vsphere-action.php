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

$vmware_vsphere_command = $request->get('vmware_vsphere_command');
$vmware_vsphere_id = $request->get('vmware_vsphere_id');


global $htvcenter_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;

$cloud_product_hook = $RootDir.'/plugins/vmware-vsphere/htvcenter-vmware-vsphere-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';
$cloud_usergroup_class = $RootDir.'/plugins/cloud/class/cloudusergroup.class.php';

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "vmware-vsphere-action", "Un-Authorized access to vmware-vsphere-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}

$vmware_vsphere_name = $request->get('vmware_vsphere_name');
$vmware_vsphere_mac = $request->get('vmware_vsphere_mac');
$vmware_vsphere_ip = $request->get('vmware_vsphere_ip');
$vmware_vsphere_ram = $request->get('vmware_vsphere_ram');
$vmware_vsphere_disk = $request->get('vmware_vsphere_disk');

$vmware_vsphere_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "vmware_vsphere_", 14) == 0) {
		$vmware_vsphere_fields[$key] = $value;
	}
}
unset($vmware_vsphere_fields["vmware_vsphere_command"]);

	$event->log("$vmware_vsphere_command", $_SERVER['REQUEST_TIME'], 5, "vmware-vsphere-action", "Processing command $vmware_vsphere_command", "", "", 0, 0, 0);
	switch ($vmware_vsphere_command) {

	case 'init':
		// this command creates the following table
		// -> vmw_vsphere_auto_discovery
		// vmw_vsphere_ad_id BIGINT
		// vmw_vsphere_ad_ip VARCHAR(50)
		// vmw_vsphere_ad_mac VARCHAR(50)
		// vmw_vsphere_ad_hostname VARCHAR(50)
		// vmw_vsphere_ad_user VARCHAR(50)
		// vmw_vsphere_ad_password VARCHAR(50)
		// vmw_vsphere_ad_comment VARCHAR(255)
		// vmw_vsphere_ad_is_integrated BIGINT

		$create_vmw_auto_discovery_table = "create table vmw_vsphere_auto_discovery(vmw_vsphere_ad_id BIGINT, vmw_vsphere_ad_ip VARCHAR(255), vmw_vsphere_ad_mac VARCHAR(50), vmw_vsphere_ad_hostname VARCHAR(50), vmw_vsphere_ad_user VARCHAR(50), vmw_vsphere_ad_password VARCHAR(50), vmw_vsphere_ad_comment VARCHAR(255), vmw_vsphere_ad_is_integrated BIGINT)";
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
					htvcenter_vmware_vsphere_cloud_product("add", $cloud_hook_config);
				}
			}
		}
		
		break;

	case 'uninstall':
		$drop_vmw_auto_discovery_table = "drop table vmw_vsphere_auto_discovery";
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
					htvcenter_vmware_vsphere_cloud_product("remove", $cloud_hook_config);
				}
			}
		}
		
		break;




		default:
			$event->log("$vmware_vsphere_command", $_SERVER['REQUEST_TIME'], 3, "vmware-vsphere-action", "No such vmware-vsphere command ($vmware_vsphere_command)", "", "", 0, 0, 0);
			break;


	}
?>
