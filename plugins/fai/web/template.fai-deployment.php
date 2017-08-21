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
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htvcenter-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
global $htvcenter_SERVER_BASE_DIR;

// global event for logging
$event = new event();
global $event;

function wait_for_fai_profile_list($sfile) {
	$refresh_delay=1;
	$refresh_loop_max=20;
	$refresh_loop=0;
	while (!file_exists($sfile)) {
		sleep($refresh_delay);
		$refresh_loop++;
		flush();
		if ($refresh_loop > $refresh_loop_max)  {
			return false;
		}
	}
	return true;
}

function get_fai_deployment_templates($local_storage_storage_id) {
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_ADMIN;
	global $event;

	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/plugins/fai/storage';
	// get the fai-server resource
	$fai_storage = new storage();
	$fai_storage->get_instance_by_id($local_storage_storage_id);
	$fai_server_resource = new resource();
	$fai_server_resource->get_instance_by_id($fai_storage->resource_id);

	// remove statfile
	$template_list_file = $StorageDir."/".$fai_server_resource->id.".fai-profiles.list";
	if (file_exists($template_list_file)) {
		unlink($template_list_file);
	}
	$fai_server_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/fai/bin/htvcenter-fai post_profiles -u ".$htvcenter_ADMIN->name." -p ".$htvcenter_ADMIN->password." --htvcenter-cmd-mode background";
	$fai_server_resource->send_command($fai_server_resource->ip, $fai_server_command);
	sleep(2);
	if (!wait_for_fai_profile_list($template_list_file)) {
		$event->log("get_fai_deployment_templates", $_SERVER['REQUEST_TIME'], 2, "template.fai-deployment.php", "Timeout while requesting template identifier from storage id $fai_storage->id", "", "", 0, 0, 0);
		return;
	}
	$local_deployment_tepmplates_identifier_array = array();
	$fcontent = file($template_list_file);
	foreach($fcontent as $template_list_info) {
		$tpos = strpos($template_list_info, ",");
		$template_name = trim(substr($template_list_info, $tpos+1));
		$template_identifier = $template_name;
		$template_deployment_parameter = "fai-deployment:".$local_storage_storage_id.":".$template_identifier;
		$local_deployment_tepmplates_identifier_array[] = array("value" => "$template_deployment_parameter", "label" => "$template_name");
	}
	return $local_deployment_tepmplates_identifier_array;
}


function get_fai_deployment_methods() {
	$fai_deployment_methods_array = array("value" => "fai-deployment", "label" => "Automatic Linux Installation (Fai)");
	return $fai_deployment_methods_array;
}


function get_fai_deployment_additional_parameters() {
	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Add. Class 1');
	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Add. Class 2');
	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Add. Class 3');
	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Add. Class 4');
	return $local_deployment_additional_parameters;
}

?>


