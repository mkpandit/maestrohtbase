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
// special fai classes
require_once "$RootDir/plugins/fai/class/faistate.class.php";
require_once "$RootDir/plugins/fai/class/fairesource.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;



// this function is going to manage the fai installation states
function htvcenter_fai_monitor() {
	global $event;
	global $RootDir;
	global $htvcenter_server;
	global $htvcenter_SERVER_BASE_DIR;

	$event->log("htvcenter_fai_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-monitor-hook.php", "Checking the fai states .....", "", "", 0, 0, 0);
	$now = $_SERVER['REQUEST_TIME'];
	$fai_state_monitor = new faistate();
	$fai_state_id_arr = $fai_state_monitor->get_all_ids();
	foreach($fai_state_id_arr as $fai_state_id_db) {
		$fai_state_id = $fai_state_id_db['fai_id'];
		$fai_state = new faistate();
		$fai_state->get_instance_by_id($fai_state_id);
		$fai_time_diff = $now - $fai_state->install_start;
		if ($fai_time_diff >= $fai_state->timeout) {
			$event->log("htvcenter_fai_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-monitor-hook.php", "Fai states resource ".$fai_state->resource_id." timeout, setting to localboot.....", "", "", 0, 0, 0);
			$fairesource = new fairesource();
			$fairesource->set_boot($fai_state->resource_id, 1);
			// remove
			$fai_state->remove($fai_state->id);
		} else {
			$event->log("htvcenter_fai_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-fai-monitor-hook.php", "Fai states still waiting for ".$fai_state->resource_id." timeout to appear .....", "", "", 0, 0, 0);
		}
	}
}



?>
