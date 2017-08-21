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


// This class represents a ansible user in htvcenter

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/class/file.class.php";

$event = new event();
global $event;

$ansible_playbook_dir = "$RootDir/plugins/ansible/ansible/playbooks";
global $ansible_playbook_dir;
$ansible_server_dir = "$RootDir/plugins/ansible/ansible/server";
global $ansible_server_dir;

class ansible {

		function __construct() {
			$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
			$this->playbook_dir = $RootDir.'/plugins/ansible/ansible/playbooks';
			$this->server_dir = $RootDir.'/plugins/ansible/ansible/server';
			$this->event = new event();
		}

// ---------------------------------------------------------------------------------
// general ansibleconfig methods
// ---------------------------------------------------------------------------------


function get_available_playbooks() {
	$app_dir = new folder();
	$app_dir->getFolderContent($this->playbook_dir);
	$ansible_playbooks = array();
	$ansible_playbooks = $app_dir->files;
	$ansible_playbook_array = array();
	foreach($ansible_playbooks as $ansible) {
		$ansible_playbook = str_replace(".yml", "", $ansible->name);
		$ansible_playbook_array[] .= $ansible_playbook;
	}
	return $ansible_playbook_array;
}



function get_playbook_info($group_name) {

	$filename = $this->playbook_dir."/".$group_name.".yml";
	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_playbook_info", $_SERVER['REQUEST_TIME'], 2, "ansible.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
		}
		while (!feof($handle)) {
			$info = fgets($handle, 4096);
			if (strstr($info, "#")) {
				$info = str_replace("#", "", $info);
				fclose($handle);
				return $info;
			}
		}
	}


}



function set_playbooks($appliance_name, $ansible_playbook_array) {
	$filename = $this->server_dir."/".$appliance_name;
	if (!$handle = fopen($filename, 'w+')) {
		$this->event->log("set_playbook", $_SERVER['REQUEST_TIME'], 2, "ansible.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
		exit;
	}
	foreach($ansible_playbook_array as $ansible_playbook) {
		fwrite($handle, $ansible_playbook."\n");
	}
	$empty = count($ansible_playbook_array);
	fclose($handle);
}


function get_playbooks($appliance_name) {
	$ansible_playbook_array = array();
	$filename = $this->server_dir."/".$appliance_name;

	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_playbooks", $_SERVER['REQUEST_TIME'], 2, "ansible.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
		}
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			$buffer = trim($buffer);
			$ansible_playbook_array[] .= $buffer;
		}
		fclose($handle);
	}
	return $ansible_playbook_array;
}

function remove_appliance($appliance_name) {
	$filename = $this->server_dir."/".$appliance_name;
	if (file_exists($filename)) {
		unlink($filename);
	}
}


// ---------------------------------------------------------------------------------

}

