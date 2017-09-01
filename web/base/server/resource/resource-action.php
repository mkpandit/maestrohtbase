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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/class/virtualization.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

global $RESOURCE_INFO_TABLE;

$event = new event();

// user/role authentication
if (!strstr($htvcenter_USER->role, "administrator")) {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "resource-action", "Un-Authorized access to resource-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}

$resource_command = $request->get('resource_command');
$resource_id = $request->get('resource_id');
$resource_hostname = $request->get('resource_hostname');
$resource_mac = strtolower($request->get('resource_mac'));
$resource_ip = $request->get('resource_ip');
$resource_state = $request->get('resource_state');
$resource_event = $request->get('resource_event');
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "resource_", 9) == 0) {
		$resource_fields[$key] = $request->get($key);
	}
}
unset($resource_fields["resource_command"]);
// add mac address always in lowercase
$macl = strtolower($resource_fields["resource_mac"]);
$resource_fields["resource_mac"] = $macl;

$virtualization_id = $request->get('virtualization_id');
$virtualization_name = $request->get('virtualization_name');
$virtualization_type = $request->get('virtualization_type');
$virtualization_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "virtualization_", 15) == 0) {
		$virtualization_fields[$key] = $value;
	}
}


function res_new_redirect($strMsg) {
	global $thisfile;
	$url = 'resource-new.php?strMsg='.urlencode($strMsg).'&currenttab=tab0';
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();

global $htvcenter_SERVER_IP_ADDRESS;

$event->log("$resource_command", $_SERVER['REQUEST_TIME'], 5, "resource-action", "Processing command $resource_command on $resource_id", "", "", 0, 0, 0);
switch ($resource_command) {

	// new_resource needs :
	// resource_mac
	// resource_ip
	case 'new_resource':
		$strMsg = '';
		$resource = new resource();
		if ($resource->exists($resource_mac)) {
			$strMsg = "Resource ".$resource_mac." already exist in the htvcenter-database!";
			res_new_redirect($strMsg);
		}
		if ("$resource_id" == "-1") {
			$new_resource_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$resource->id = $new_resource_id;
		} else {
		// 	check if resource_id is free
			if ($resource->is_id_free($resource_id)) {
				$new_resource_id=$resource_id;
			} else {
				$strMsg = "Given resource id ".$resource_id." is already in use!";
				res_new_redirect($strMsg);
			}
		}
		// check name
		if($resource_hostname != '') {
			if (!preg_match('#^[A-Za-z0-9_.-]*$#', $resource_hostname)) {
				$strMsg .= 'Hostname name must be [A-Za-z0-9_.-]<br/>';
				res_new_redirect($strMsg);
			}
		} else {
			$strMsg .= "Hostname can not be empty<br/>";
			res_new_redirect($strMsg);
		}

		// send command to the htvcenter-server
		$htvcenter_server->send_command("htvcenter_server_add_resource $new_resource_id $resource_mac $resource_ip");
		// add to htvcenter database
		$resource_fields["resource_id"]=$new_resource_id;
		$resource_fields["resource_localboot"]=0;
		$resource_fields["resource_vtype"]=1;
		$resource_fields["resource_vhostid"]=$new_resource_id;
		$resource->add($resource_fields);
		// set lastgood to -1 to prevent automatic checking the state
		$resource_fields["resource_lastgood"]=-1;
		$resource->update_info($new_resource_id, $resource_fields);
		// $resource->get_parameter($new_resource_id);

		break;

	// remove requires :
	// resource_id
	// resource_mac
	case 'remove':
		// remove from htvcenter database
		$resource = new resource();
		$resource->remove($resource_id, $resource_mac);
		break;

	// localboot requires :
	// resource_id
	// resource_mac
	// resource_ip
	case 'localboot':
		$htvcenter_server->send_command("htvcenter_server_set_boot local $resource_id $resource_mac $resource_ip");
		// update db
		$resource = new resource();
		$resource->set_localboot($resource_id, 1);
		break;

	// netboot requires :
	// resource_id
	// resource_mac
	// resource_ip
	case 'netboot':
		$htvcenter_server->send_command("htvcenter_server_set_boot net $resource_id $resource_mac $resource_ip");
		// update db
		$resource = new resource();
		$resource->set_localboot($resource_id, 0);
		break;

	// assign requires :
	// resource_id
	// resource_mac
	// resource_ip
	// kernel_id
	// kernel_name
	// image_id
	// image_name
	// appliance_id

	case 'assign':

		$kernel_id=($_REQUEST["resource_kernelid"]);
		$kernel = new kernel();
		$kernel->get_instance_by_id($kernel_id);
		$kernel_name = $kernel->name;

		$image_id=($_REQUEST["resource_imageid"]);
		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name = $image->name;

		// send command to the htvcenter-server
		$htvcenter_server->send_command("htvcenter_assign_kernel $resource_id $resource_mac $kernel_name");
		// update htvcenter database
		$resource = new resource();
		$resource->assign($resource_id, $kernel_id, $kernel_name, $image_id, $image_name);
		$resource->send_command($resource_ip, "reboot");
		break;

	// reboot requires :
	// resource_ip
	case 'reboot':
		$resource = new resource();
		$resource->send_command("$resource_ip", "reboot");
		// set state to transition
		$resource_fields=array();
		$resource_fields["resource_state"]="transition";
		$resource = new resource();
		$resource->get_instance_by_ip($resource_ip);
		$resource->update_info($resource->id, $resource_fields);
		break;


	// halt requires :
	// resource_ip
	case 'halt':
		$resource = new resource();
		$resource->send_command("$resource_ip", "halt");
		// set state to off
		$resource_fields=array();
		$resource_fields["resource_state"]="off";
		$resource = new resource();
		$resource->get_instance_by_ip($resource_ip);
		$resource->update_info($resource->id, $resource_fields);
		break;

	// list requires :
	// nothing
	case 'list':
		$resource = new resource();
		$resource_list = $resource->get_resource_list();
		foreach ($resource_list as $resource_l) {
			foreach ($resource_l as $key => $val) {
				print "$key=$val ";
			}
			print "\n";
		}
		exit(0); // nothing more to do
		break;

	case 'add_virtualization_type':
		$virtualization = new virtualization();
		$virtualization_fields["virtualization_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		$virtualization->add($virtualization_fields);
		break;

	case 'remove_virtualization_type':
		$virtualization = new virtualization();
		$virtualization->remove_by_type($virtualization_type);
		break;


	default:
		$event->log("$resource_command", $_SERVER['REQUEST_TIME'], 3, "resource-action", "No such resource command ($resource_command)", "", "", 0, 0, 0);
		break;
}

?>

</body>
