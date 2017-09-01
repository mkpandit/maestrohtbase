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
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/event.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/htvcenter_server.class.php";
require_once $RootDir."/include/htvcenter-server-config.php";
require_once $RootDir."/plugins/puppet/class/puppet.class.php";
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;



// cloud hook to get the available application groups
function htvcenter_puppet_get_cloud_applications() {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$puppet_group_list = array();
	$puppet = new puppet();
	$puppet_group_array = $puppet->get_available_groups();
	foreach ($puppet_group_array as $index => $puppet_app) {
		$puppet_group_list[] = "puppet/".$puppet_app;
	}
	return $puppet_group_list;
}


// cloud hook to set applications for a cloud server
function htvcenter_puppet_set_cloud_applications($appliance_name, $application_array) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	$puppet = new puppet();
	$puppet->set_groups($appliance_name, $application_array);
}


// cloud hook to remove applications from a cloud server
function htvcenter_puppet_remove_cloud_applications($appliance_name) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	$puppet = new puppet();
	$puppet->remove_appliance($appliance_name);
}





?>


