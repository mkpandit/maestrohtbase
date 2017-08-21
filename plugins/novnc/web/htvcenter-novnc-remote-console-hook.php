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
$event = new event();
global $event;

function htvcenter_novnc_remote_console($resource_id) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$html = new htmlobject($htvcenter_SERVER_BASE_DIR.'/htvcenter/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = 'noVNC';
	$a->css = 'badge';
	$a->href = '/htvcenter/base/index.php?plugin=novnc&controller=novnc&novnc_action=console&resource_id='.$resource_id;
	$a->handler = 'onclick="wait();"';

	return $a;
}



// this functions implements the stop action for the vnc remote console
// deprecated - stop implemented via timeout
function htvcenter_novnc_disable_remote_console($vncserver, $vncport, $vm_res_id, $vm_mac, $resource_name) {
	#global $event;
	#global $htvcenter_SERVER_BASE_DIR;
	#// stop the novnc proxy
	#$novnc_stop_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/plugins/novnc/bin/htvcenter-novnc-manager disable-remoteconsole -n ".$resource_name." -d ".$vm_res_id." -m ".$vm_mac." -i ".$vncserver." -v ".$vncport;
	#$output = shell_exec($novnc_stop_command);
}


?>
