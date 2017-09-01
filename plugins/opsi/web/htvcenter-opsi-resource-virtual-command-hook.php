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
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $htvcenter_server;
$event = new event();
global $event;



function htvcenter_opsi_resource_virtual_command($cmd, $resource_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;
	global $htvcenter_server;

	$resource_id = $resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$resource_ip = $resource->ip;
	$event->log("htvcenter_opsi_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id on windows host", "", "", 0, 0, 0);

	switch($cmd) {
		case "reboot":
			$event->log("htvcenter_opsi_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			$virtual_command = "dbclient -K 10 -y -i $htvcenter_SERVER_BASE_DIR/htvcenter/etc/dropbear/dropbear_rsa_host_key -p 22 root@$resource_ip 'shutdown.exe /r /f /t 2'";
			$htvcenter_server->send_command($virtual_command);
			sleep(2);
			$htvcenter_server->send_command($virtual_command);
			break;
		case "halt":
			$event->log("htvcenter_opsi_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter-opsi-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			$virtual_command = "dbclient -K 10 -y -i $htvcenter_SERVER_BASE_DIR/htvcenter/etc/dropbear/dropbear_rsa_host_key -p 22 root@$resource_ip 'shutdown.exe /s /f /t 2'";
			$htvcenter_server->send_command($virtual_command);
			sleep(2);
			$htvcenter_server->send_command($virtual_command);
			break;

	}
}



?>
