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


// This class represents a fairesource object in htvcenter

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/htvcenter-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;


class fairesource {

var $id = '';
var $resource_id = '';


//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function fairesource() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	global $htvcenter_SERVER_BASE_DIR;
	$this->_event = new event();
	$this->_base_dir = $htvcenter_SERVER_BASE_DIR;
}


// ---------------------------------------------------------------------------------
// methods to set a resource boot-sequence
// This is especially needed for KVM VMs since the boot-sequence "nc" does
// not use the local disk for boot if set by pxe. -> bug in kvm
// ---------------------------------------------------------------------------------

function set_boot($resource_id, $boot) {
	global $event;
	$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "fairesource.class.php", "Setting boot-sequence of resource ".$resource_id." to ".$boot.".", "", "", 0, 0, 0);
	$boot_sequence = "net";
	switch($boot) {
		case '0':
			// netboot
			$boot_sequence = "net";
			break;
		case '1':
			// local boot
			$boot_sequence = "local";
			break;
	}
	$fai_resource = new resource();
	$fai_resource->get_instance_by_id($resource_id);
	// is it a vm ?
	if ($fai_resource->vhostid == $resource_id) {
		return;
	}
	$fai_resource_virtualization = new virtualization();
	$fai_resource_virtualization->get_instance_by_id($fai_resource->vtype);
	switch($fai_resource_virtualization->type) {
		case 'kvm-vm-net':
			$fai_resource_vhost = new resource();
			$fai_resource_vhost->get_instance_by_id($fai_resource->vhostid);
			$fai_resource_set_boot_commmand = $this->_base_dir."/htvcenter/plugins/kvm/bin/htvcenter-kvm-vm setboot -m ".$fai_resource->mac." -b ".$boot_sequence." --htvcenter-cmd-mode background";
			$fai_resource_vhost->send_command($fai_resource_vhost->ip, $fai_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "fairesource.class.php", "Resource ".$resource_id." is a KVM VM on Host ".$fai_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
		case 'kvm-vm-local':
			$fai_resource_vhost = new resource();
			$fai_resource_vhost->get_instance_by_id($fai_resource->vhostid);
			$fai_resource_set_boot_commmand = $this->_base_dir."/htvcenter/plugins/kvm/bin/htvcenter-kvm-vm setboot -m ".$fai_resource->mac." -b ".$boot_sequence." --htvcenter-cmd-mode background";
			$fai_resource_vhost->send_command($fai_resource_vhost->ip, $fai_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "fairesource.class.php", "Resource ".$resource_id." is a KVM-Storage VM on Host ".$fai_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
	}

}



// ---------------------------------------------------------------------------------

}

?>

