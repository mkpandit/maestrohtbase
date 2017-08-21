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



function htvcenter_highavailability_cloud_product($cmd, $cloud_hook_config) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $RootDir;

	$htvcenter_server = new htvcenter_server();

	$event->log("htvcenter_highavailability_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-highavailability-cloud-product-hook.php", "Handling ".$cmd." event", "", "", 0, 0, 0);
	switch($cmd) {
			case "add":
				$event->log("htvcenter_highavailability_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-highavailability-cloud-product-hook.php", "Handling cloud-product ".$cmd." event", "", "", 0, 0, 0);
				// create resource products
				$db=htvcenter_get_db_connection();
				$event->log("htvcenter_highavailability_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-highavailability-cloud-product-hook.php", "Adding highavailability as cloud-product", "", "", 0, 0, 0);
				$cloud_product_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$create_ha_cloudselector_config = "insert into cloud_selector (id, type, sort_id, quantity, price, name, description, state) VALUES (".$cloud_product_id.", 'ha', 0, '1', 10, 'Highavailability', 'Automatic Highavailability', 1);";
				$recordSet = $db->Execute($create_ha_cloudselector_config);
				break;
			
			case "remove":
				$cloud_product_class = $RootDir."/plugins/cloud/class/cloudselector.class.php";
				if (file_exists($cloud_product_class)) {
					require_once $cloud_product_class;
					$cloud_selector = new cloudselector();
					$cloud_selector_id_ar = $cloud_selector->get_all_ids();
					foreach ($cloud_selector_id_ar as $key => $value) {
						$id = $value['id'];
						$cloud_selector->get_instance_by_id($id);
						if ($cloud_selector->type == 'ha') {
							$event->log("htvcenter_highavailability_cloud_product", $_SERVER['REQUEST_TIME'], 5, "htvcenter-highavailability-cloud-product-hook.php", "Removing application ".$cloud_selector->type." from cloud-products", "", "", 0, 0, 0);
							$cloud_selector->remove($id);
						}
					}
				}
				break;
	}
}



?>


