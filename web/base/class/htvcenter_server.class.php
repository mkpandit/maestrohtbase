<?php

// This class represents the htvcenter-server
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $RESOURCE_INFO_TABLE;
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXECUTION_LAYER;
$event = new event();
global $event;

class htvcenter_server {

var $id = '';


// ---------------------------------------------------------------------------------
// general server methods
// ---------------------------------------------------------------------------------

// returns the ip of the htvcenter-server
function get_ip_address() {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=htvcenter_get_db_connection();
	$rs = $db->Execute("select resource_htvcenterserver from $RESOURCE_INFO_TABLE where resource_id=0");
	if (!$rs)
		$event->log("get_ip_address", $_SERVER['REQUEST_TIME'], 2, "htvcenter_server.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$resource_htvcenterserver=$rs->fields["resource_htvcenterserver"];
		$rs->MoveNext();
	}
	if (!strlen($resource_htvcenterserver)) {
		$event->log("get_ip_address", $_SERVER['REQUEST_TIME'], 2, "htvcenter_server.class.php", "Could not find out IP-Address of the htvcenter server. Server misconfiguration!", "", "", 0, 0, 0);
	}
	return $resource_htvcenterserver;
}


// function to send a command to the htvcenter-server
function send_command($server_command, $command_timeout = NULL, $run_local = NULL) {
	global $htvcenter_EXEC_PORT;
	// global $htvcenter_SERVER_IP_ADDRESS;
	$htvcenter_SERVER_IP_ADDRESS=$this->get_ip_address();
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_EXECUTION_LAYER;
	global $event;

	// check which execution layer to use
	switch($htvcenter_EXECUTION_LAYER) {
		case 'dropbear':
			// generate a random token for the cmd
			$cmd_token = md5(uniqid(rand(), true));
			// custom timeout ?
			if (!is_null($command_timeout)) {
				$cmd_token .= ".".$command_timeout;
			}
			// run local ?
			$run_local_parameter = '';
			if (!is_null($run_local)) {
				$run_local_parameter = "-l true";
			}
			$final_command = "$htvcenter_SERVER_BASE_DIR/htvcenter/sbin/htvcenter-exec -i $htvcenter_SERVER_IP_ADDRESS -t $cmd_token $run_local_parameter -c \"$server_command\"";
			// $event->log("send_command", $_SERVER['REQUEST_TIME'], 5, "htvcenter_server.class.php", "Running : $final_command", "", "", 0, 0, 0);
			shell_exec($final_command);
			return true;
			break;
		case 'htvcenter-execd':
			$fp = fsockopen($htvcenter_SERVER_IP_ADDRESS, $htvcenter_EXEC_PORT, $errno, $errstr, 30);
			if(!$fp) {
				$event->log("send_command", $_SERVER['REQUEST_TIME'], 2, "htvcenter_server.class.php", "Could not connect to the htvcenter Server", "", "", 0, 0, 0);
				$event->log("send_command", $_SERVER['REQUEST_TIME'], 2, "htvcenter_server.class.php", "$errstr ($errno)", "", "", 0, 0, 0);
				return false;
			} else {
				fputs($fp,"$server_command");
				fclose($fp);
				return true;
			}
			break;
	}

}



// ---------------------------------------------------------------------------------

}

?>