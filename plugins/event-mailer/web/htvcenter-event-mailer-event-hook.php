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
require_once $RootDir."/include/htvcenter-server-config.php";
require_once $RootDir."/class/user.class.php";
require_once $RootDir."/class/event.class.php";
require_once $RootDir."/plugins/event-mailer/class/event-mailer.class.php";

//--------------------------------------------------
/**
* send mails
* @access public
* @param enum $action
* @param integer $event_id
* @param enum $event_type active|error|regular|warning
*/
//--------------------------------------------------
function htvcenter_event_mailer_event($action, $event_id) {

	global $htvcenter_SERVER_CONFIG_FILE;
	$conf = htvcenter_parse_conf($htvcenter_SERVER_CONFIG_FILE);

	$oquser = new user('dummy');
	$event  = new event();
	$event  = $event->get_instance_by_id(intval($event_id));
	# TODO : handle event not found

	// translate priority to type
	$type   = 'regular';
	$filter = '';
	if(isset($event->priority) && $event->priority !== '') {
		if($event->priority < 4 && $event->status !== '1') {
			$type   = 'error';
			$filter = 'error';
		}
		else if($event->priority === '4' && $event->status !== '1') {
			$type   = 'warning';
			$filter = 'warning';
		}
		else if($event->priority === '9' && $event->status !== '1') {
			$type   = 'active';
			$filter = 'active';
		}
	}

	$mailer = new event_mailer();
	$users = $mailer->get_result_by_event($type);
	if(isset($users)) {

		$result = $mailer->get_template();
		$message = $result['event_mailer_body'];
		$message = str_replace('@@DESCRIPTION@@', $event->description, $message);
		$message = str_replace('@@SERVERIP@@', $_SERVER['HTTP_HOST'], $message);
		$message = str_replace('@@EVENTID@@', $event_id, $message);
		$message = str_replace('@@EVENTNAME@@', $event->name, $message);
		$message = str_replace('@@EVENTSOURCE@@', $event->source, $message);
		$message = str_replace('@@EVENTTIME@@', date('Y/m/d H:i:s', $event->time), $message);
		$message = str_replace('@@EVENTTYPE@@', strtoupper($type).' Event', $message);

		$resource = ($event->resource_id === '0') ? 'htvcenter Server' : $event->resource_id;
		$message = str_replace('@@EVENTRESOURCE@@', $resource, $message);
		$message = wordwrap($message, 80);

		$subject = $result['event_mailer_subject'];

		switch($action) {		
			case 'add':
				$link = $conf['htvcenter_WEB_PROTOCOL'].'://'.$_SERVER['HTTP_HOST'].'/htvcenter/base/index.php?base=event&event_filter='.$filter;
				$message = str_replace('@@EVENTACTION@@', 'added', $message);
				$message = str_replace('@@LINK@@', $link, $message);
				$subject = str_replace('@@EVENTID@@', $event_id, $subject);
				$subject = str_replace('@@DESCRIPTION@@', $event->description, $subject);
			break;
			case 'remove':
				$message = str_replace('@@EVENTACTION@@', 'removed', $message);
				$message = str_replace('@@LINK@@', '', $message);
				$subject = str_replace('@@EVENTID@@', $event_id, $subject);
				$subject = str_replace('@@DESCRIPTION@@', '', $subject);
				$subject = $subject.' removed';
			break;
		}

		foreach($users as $user) {

			if($action === 'remove' && $user['event_remove'] === '0') {
				continue;
			}

			$cuser = $oquser->get_instance_by_id($user['user_id']);
			$name  = $cuser->name;

			if(isset($cuser->first_name) && isset($cuser->last_name)) {
				$name = $cuser->first_name.' '.$cuser->last_name;
			}

			$body = str_replace('@@USERNAME@@', $name, $message);
			$to      = $user['user_email'];
			$headers   = array();
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";
			$headers[] = "Content-Transfer-Encoding: 8bit";
			$headers[] = "From: htvcenter@".$_SERVER['HTTP_HOST'];
			$headers[] = "Subject: ".$subject;
			$headers[] = "X-Mailer: PHP/".phpversion();

			$res = mail($to, $subject, $body, implode("\r\n", $headers));
			if (!$res) {
				syslog(0, 'Event-Mailer could not sent mail !');
			}
		}

	}

}
?>
