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
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
require_once "$RootDir/class/htmlobjects/htmlobject.class.php";
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;

function get_novnc_appliance_link($appliance_id) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	$p_resource = new resource();
	$p_resource->get_instance_by_id($p_appliance->resources);
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($p_appliance->virtualization);

	$html = new htmlobject($htvcenter_SERVER_BASE_DIR.'/htvcenter/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = 'noVNC';
	$a->css = 'badge';
	$a->handler = 'onclick="wait();"';

	$plugin_link = '';
	if (strstr($p_appliance->state, "active")) {
		if(strstr($virtualization->type, '-vm-')) {
			$a->href = '/htvcenter/base/index.php?plugin=novnc&controller=novnc&novnc_action=console&appliance_id='.$p_appliance->id;
		} else {
			$a->href = '/htvcenter/base/index.php?plugin=novnc&controller=novnc&novnc_action=login&appliance_id='.$p_appliance->id;
		}
		$plugin_link = $a;
	}
	else if ($p_resource->id === '0') {
		$a->href = '/htvcenter/base/index.php?plugin=novnc&controller=novnc&novnc_action=login&appliance_id='.$p_appliance->id;
		$plugin_link = $a;
	}
	return $plugin_link;
}
?>
