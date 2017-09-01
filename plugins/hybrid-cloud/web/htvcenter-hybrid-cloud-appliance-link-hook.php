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
# class for the hybrid cloud accounts
require_once $RootDir."/plugins/hybrid-cloud/class/hybrid-cloud.class.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;

function get_hybrid_cloud_appliance_link($appliance_id) {
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

	if ($virtualization->type != "hybrid-cloud-vm-local") {
		return;
	}

	// get hybrid-cloud account
	$hybrid_cloud_acl_id = $p_resource->get_resource_capabilities("HCACL");
	if ($hybrid_cloud_acl_id == '') {
		$event->log("get_hybrid_cloud_appliance_link", $_SERVER['REQUEST_TIME'], 2, "htvcenter-hybrid-cloud-appliance-link-hook.php", "Could not find Hybrid-Cloud Account for resource ".$p_resource->id, "", "", 0, 0, $appliance_id);
		return;
	}
	$hc = new hybrid_cloud();
	$hc->get_instance_by_id($hybrid_cloud_acl_id);

	$html = new htmlobject($htvcenter_SERVER_BASE_DIR.'/htvcenter/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = 'Dashboard';
	$a->css = 'badge';
	$a->target     = '_BLANK';
	if (($hc->account_type == 'aws') || ($hc->account_type == 'euca')) {
		$a->href    = 'https://console.aws.amazon.com/ec2/';
	}
	if ($hc->account_type == 'lc-openstack') {
		$a->href    = 'http://'.$hc->host.'/project/instances/';
	}
	if ($hc->account_type == 'lc-azure') {
		$a->href    = 'https://manage.windowsazure.com/';
	}
	$plugin_link = $a->get_string();
	return $plugin_link;
}
?>
