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


function get_highavailability_appliance_link($appliance_id) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	$resource = new resource();
	$resource->get_instance_by_id($p_appliance->resources);
	
	$html = new htmlobject($htvcenter_SERVER_BASE_DIR.'/htvcenter/web/base/class/htmlobjects');
	$a = '';
	if ($p_appliance->resources != 0 && strpos($resource->capabilities, 'TYPE=local-server') === false) {
		if ($p_appliance->highavailable != '1') {
			$a = $html->a();
			$a->label = 'enable HA'; //'<img title="enable highavailability" alt="enable Highavailability" height="24" width="24" src="/htvcenter/base/img/idle.png" border="0">';
			$a->css = 'badge';
			$a->href = $html->thisfile.'?plugin=highavailability&highavailability_action=enable&highavailability_identifier[]='.$appliance_id;
		} else {
			$a = $html->a();
			$a->label = 'Disable HA'; //'<img title="disable highavailability" alt="disable Highavailability" height="24" width="24" src="/htvcenter/base/plugins/highavailability/img/plugin.png" border="0">';
			$a->css = 'badge';
			$a->href = $html->thisfile.'?plugin=highavailability&highavailability_action=disable&highavailability_identifier[]='.$appliance_id;
		}
	}
	return $a;
}


?>


