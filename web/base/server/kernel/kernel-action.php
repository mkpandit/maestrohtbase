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
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

global $KERNEL_INFO_TABLE;

$event = new event();

// user/role authentication
if (!strstr($htvcenter_USER->role, "administrator")) {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "kernel-action", "Un-Authorized access to kernel-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}

$kernel_command=$request->get('kernel_command');
$kernel_name=$request->get('kernel_name');
$kernel_id=$request->get('kernel_id');
$kernel_name=$request->get('kernel_name');
$kernel_version=$request->get('kernel_version');
$kernel_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "kernel_", 7) == 0) {
		$kernel_fields[$key] = $value;
	}
}
unset($kernel_fields["kernel_command"]);


$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();

global $htvcenter_SERVER_IP_ADDRESS;

$event->log("$kernel_command", $_SERVER['REQUEST_TIME'], 5, "kernel-action", "Processing command $kernel_command for kernel $kernel_name", "", "", 0, 0, 0);
switch ($kernel_command) {
	case 'new_kernel':
		$kernel = new kernel();
		// check that name is unique
		$kernel->get_instance_by_name($kernel_name);
		if ($kernel->id > 0) {
			$event->log("$kernel_command", $_SERVER['REQUEST_TIME'], 5, "kernel-action", "Kernel name must be unique! Not adding new kernel ".$kernel_name, "", "", 0, 0, 0);
		} else {
                        if ($kernel_name == 'default') {
                            $kernel_fields["kernel_id"]=1;
                        } else {
                            $kernel_fields["kernel_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
                        }
			$kernel->add($kernel_fields);
		}
		break;

	case 'update':
		$kernel = new kernel();
		$kernel->update($kernel_id, $kernel_fields);
		break;

	case 'remove':
		$kernel = new kernel();
		$kernel->remove($kernel_id);
		break;

	case 'remove_by_name':
		$kernel = new kernel();
		$kernel->remove_by_name($kernel_name);
		break;

	default:
		$event->log("$kernel_command", $_SERVER['REQUEST_TIME'], 3, "kernel-action", "No such kernel command ($kernel_command)", "", "", 0, 0, 0);
		break;


}
?>

</body>
