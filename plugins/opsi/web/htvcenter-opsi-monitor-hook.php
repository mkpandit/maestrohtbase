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
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/class/event.class.php";
// special opsi classes
require_once "$RootDir/plugins/opsi/class/opsistate.class.php";
require_once "$RootDir/plugins/opsi/class/opsiresource.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;



// this function is going to manage the opsi installation states
function htvcenter_opsi_monitor() {
	global $event;
	global $RootDir;
	global $htvcenter_server;
	global $htvcenter_SERVER_BASE_DIR;

	$event->log("htvcenter_opsi_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-monitor-hook.php", "Checking the opsi states .....", "", "", 0, 0, 0);
	$now = $_SERVER['REQUEST_TIME'];
	$opsi_state_monitor = new opsistate();
	$opsi_state_id_arr = $opsi_state_monitor->get_all_ids();
	foreach($opsi_state_id_arr as $opsi_state_id_db) {
		$opsi_state_id = $opsi_state_id_db['opsi_id'];
		$opsi_state = new opsistate();
		$opsi_state->get_instance_by_id($opsi_state_id);
		$opsi_time_diff = $now - $opsi_state->install_start;
		if ($opsi_time_diff >= $opsi_state->timeout) {
			$event->log("htvcenter_opsi_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-monitor-hook.php", "Opsi states resource ".$opsi_state->resource_id." timeout, setting to localboot.....", "", "", 0, 0, 0);
			$opsiresource = new opsiresource();
			$opsiresource->set_boot($opsi_state->resource_id, 1);
			// remove
			$opsi_state->remove($opsi_state->id);
		} else {
			$event->log("htvcenter_opsi_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-monitor-hook.php", "Opsi states still waiting for ".$opsi_state->resource_id." timeout to appear .....", "", "", 0, 0, 0);
		}
	}
}



?>
