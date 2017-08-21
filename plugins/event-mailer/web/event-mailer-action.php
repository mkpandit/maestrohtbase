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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "event-mailer-action", "Un-Authorized access to event-mailer-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$event_mailer_command = $request->get('event_mailer_command');

// main
$event->log("$event_mailer_command", $_SERVER['REQUEST_TIME'], 5, "event-mailer-action", "Processing event-mailer command $event_mailer_command", "", "", 0, 0, 0);
switch ($event_mailer_command) {

	case 'init':
		// create tables
		$sql  = 'create table event_mailer(';
		$sql .= ' user_id BIGINT,';
		$sql .= ' user_email VARCHAR(255),';
		$sql .= ' event_active BIGINT,';
		$sql .= ' event_error BIGINT,';
		$sql .= ' event_warning BIGINT,';
		$sql .= ' event_regular BIGINT,';
		$sql .= ' event_remove BIGINT';
		$sql .= ')';
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($sql);
		$db->Close();
		$sql  = 'create table event_mailer_template(';
		$sql .= ' event_mailer_template BIGINT,';
		$sql .= ' event_mailer_subject VARCHAR(255),';
		$sql .= ' event_mailer_body VARCHAR(512)';
		$sql .= ')';
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($sql);
		$db->Close();
		// add template values
		$tpl  = 'Hello @@USERNAME@@,'."\n";
		$tpl .= "\n";
		$tpl .= 'the htvcenter Server at @@SERVERIP@@'."\n";
		$tpl .= '@@EVENTACTION@@ @@EVENTTYPE@@ #@@EVENTID@@'."\n";
		$tpl .= "\n";
		$tpl .= 'Time: @@EVENTTIME@@'."\n";
		$tpl .= 'Name: @@EVENTNAME@@'."\n";
		$tpl .= 'Source: @@EVENTSOURCE@@'."\n";
		$tpl .= 'Description: @@DESCRIPTION@@'."\n";
		$tpl .= 'Resource: @@EVENTRESOURCE@@'."\n";
		$tpl .= "\n";
		$tpl .= '@@LINK@@';
		// add values to array		
		$fields['event_mailer_template'] = 1;
		$fields['event_mailer_subject'] = 'htvcenter Event #@@EVENTID@@ - @@DESCRIPTION@@';
		$fields['event_mailer_body'] = $tpl;
		$db=htvcenter_get_db_connection();
		$result = $db->AutoExecute('event_mailer_template', $fields, 'INSERT');
		$db->Close();
		break;
	case 'uninstall':
		$drop_event_mailer_table = "drop table event_mailer";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_event_mailer_table);
		$db->Close();
		$drop_event_mailer_table = "drop table event_mailer_template";
		$db=htvcenter_get_db_connection();
		$recordSet = $db->Execute($drop_event_mailer_table);
		$db->Close();
		break;
	default:
		$event->log("$event_mailer_command", $_SERVER['REQUEST_TIME'], 3, "event-mailer-action", "No such event command ($event_mailer_command)", "", "", 0, 0, 0);
		break;
}

?>
