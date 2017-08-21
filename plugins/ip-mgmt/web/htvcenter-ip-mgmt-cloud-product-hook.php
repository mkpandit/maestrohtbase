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
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;



function htvcenter_ip_mgmt_cloud_product($cmd, $cloud_hook_config) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RootDir;

	$htvcenter_server = new htvcenter_server();

	$event->log("htvcenter_ip_mgmt_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ip-mgmt-cloud-product-hook.php", "Handling ".$cmd." event", "", "", 0, 0, 0);
	switch($cmd) {
			case "add":
				$event->log("htvcenter_ip_mgmt_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ip-mgmt-cloud-product-hook.php", "Handling cloud-product ".$cmd." event", "", "", 0, 0, 0);
				$admin_project_id = $cloud_hook_config['cloud_admin_procect'];				
				$assign_ip_mgmt_to_admin_group = "update ip_mgmt set ip_mgmt_user_id='".$admin_project_id."';";
				$db=htvcenter_get_db_connection();
				$recordSet = $db->Execute($assign_ip_mgmt_to_admin_group);
				break;
			
			case "remove":
				// send command to stop the vm and deassign image
				$event->log("htvcenter_ip_mgmt_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ip-mgmt-cloud-product-hook.php", "Handling --------- ".$cmd." event", "", "", 0, 0, 0);
				break;
	}
}



?>


