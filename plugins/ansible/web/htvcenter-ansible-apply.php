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
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/image.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/virtualization.class.php";
require_once $RootDir."/class/appliance.class.php";
require_once $RootDir."/class/deployment.class.php";
require_once $RootDir."/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$ansible_command = $request->get('ansible_command');
$ansible_server_id = $request->get('ansible_id');
$ansible_server_name = $request->get('ansible_name');
$ansible_server_mac = $request->get('ansible_mac');
$ansible_server_ip = $request->get('ansible_ip');

global $htvcenter_SERVER_BASE_DIR;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;


// main
$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 5, "ansible-apply", "Processing ansible command ".$ansible_command, "", "", 0, 0, 0);

	switch ($ansible_command) {

		case 'apply':
			$appliance = new appliance();
			$appliance->get_instance_by_id($ansible_server_id);
			if ($ansible_server_name == $appliance->name) {
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				if (($ansible_server_mac == $resource->mac) && ($ansible_server_ip == $resource->ip)) {
					$command  = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/ansible/bin/htvcenter-ansible-manager apply ".$appliance->id." ".$appliance->name." ".$resource->ip."  --htvcenter-cmd-mode background";
					$htvcenter_server = new htvcenter_server();
					$htvcenter_server->send_command($command, NULL, true);
				} else {
					$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 3, "ansible-apply", "Request for Ansible apply for server id ".$ansible_server_id." with wrong resource ".$resource->id, "", "", 0, 0, 0);
				}
			} else {
				$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 3, "ansible-apply", "Request for Ansible apply for server id ".$ansible_server_id." with wrong name ".$ansible_server_name, "", "", 0, 0, 0);
			}
			break;

		default:
			$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 3, "ansible-apply", "No such command ".$ansible_command, "", "", 0, 0, 0);
			break;


	}






?>
