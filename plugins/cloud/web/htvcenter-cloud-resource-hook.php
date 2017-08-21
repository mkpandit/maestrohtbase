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

// This hook removes the resource-pool and hostlimits for a specific resource
// when a resource/Host is removed from htvcenter
//
// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/include/htvcenter-server-config.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudrespool.class.php";
require_once "$RootDir/plugins/cloud/class/cloudhostlimit.class.php";
// DB
require_once $RootDir."/include/htvcenter-database-functions.php";

global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;
global $htvcenter_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function htvcenter_cloud_resource($cmd, $resource_fields) {
	global $event;
	global $htvcenter_SERVER_BASE_DIR;
	global $htvcenter_SERVER_IP_ADDRESS;
	global $htvcenter_EXEC_PORT;

	$resource_id=$resource_fields["resource_id"];
	$resource_ip=$resource_fields["resource_ip"];
	$resource_mac=$resource_fields["resource_mac"];
	// $event->log("htvcenter_remove_resource", $_SERVER['REQUEST_TIME'], 5, "htvcenter-cloud-resource-hook.php", "Handling $cmd event $resource_id/$resource_ip/$resource_mac", "", "", 0, 0, $resource_id);
	switch($cmd) {
		case "remove":
			if (strlen($resource_id)) {
				// cloudrespool
				$resource_pool = new cloudrespool();
				$resource_pool->get_instance_by_resource($resource_id);
				if (strlen($resource_pool->id)) {
					$resource_pool->remove($resource_pool->id);
				}

				// DHCP
                                $array = array();
                                $db = htvcenter_get_db_connection();
				$query = sprintf("select * from resource_info where resource_id='%s'",mysql_real_escape_string($resource_id));
                                $rs = $db->Execute($query);
                                if(isset($rs->fields)) {
                                	while (!$rs->EOF) {
                                        	array_push($array, $rs->fields);
                                                $rs->MoveNext();
                                        }
                                }
				$dhcp = "sudo /usr/share/htvcenter/plugins/dhcpd/bin/perl/manageHost.pl del ".$array[0]['resource_vname'];
				shell_exec($dhcp);
                                shell_exec("sudo /usr/share/htvcenter/plugins/dhcpd/etc/init.d/htvcenter-plugin-dhcpd restart");
				// END DHCP

				// cloudhostlimit
				$resource_hostlimit = new cloudhostlimit();
				$resource_hostlimit->get_instance_by_resource($resource_id);
				if (strlen($resource_hostlimit->id)) {
					$resource_hostlimit->remove($resource_hostlimit->id);
				}
			}
			break;
	}
}



?>


