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



function get_lcmc_appliance_link($appliance_id) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	$p_resource = new resource();
	$p_resource->get_instance_by_id($p_appliance->resources);
	$lcmc_gui="/htvcenter/base/plugins/lcmc/lcmc-gui.php";
	$icon_size = "width='24' height='24'";
	$icon_title = "Configure appliaction highavailability";

	$html = new htmlobject($htvcenter_SERVER_BASE_DIR.'/htvcenter/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = 'LCMC'; //'<img title="'.$icon_title.'" alt="'.$icon_title.'" $icon_size src="/htvcenter/base/plugins/lcmc/img/plugin.png" border=0>';
	$a->css = 'badge';
	$a->href = '#';
	$a->handler = 'onclick="window.open(\''.$lcmc_gui.'\',\'\', \'location=0,status=0,scrollbars=1,width=1150,height=800,left=50,top=50,screenX=50,screenY=50\');return false;"';

	$plugin_link = '';
	if (strstr($p_appliance->state, "active")) {
		$plugin_link = $a;
	}
	if ($p_resource->id == 0) {
		$plugin_link = $a;
	}
	if ($p_resource->id == '') {
		$plugin_link = "";
	}

	return $plugin_link;
}

?>

