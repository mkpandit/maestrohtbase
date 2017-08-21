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
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-fortis/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
require_once "$RootDir/class/event.class.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
// special cloud-nephos class
require_once "$RootDir/plugins/cloud-nephos/class/cloud-nephos-user.class.php";

// special ldapconfig class
require_once "$RootDir/plugins/ldap/class/ldapconfig.class.php";

global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;
global $CLOUD_IMAGE_TABLE;
global $CLOUD_APPLIANCE_TABLE;
global $CLOUD_SHOP_SYNCED_ORDERS_TABLE;

global $APPLIANCE_INFO_TABLE;
global $IMAGE_INFO_TABLE;
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXEC_PORT;

$cloud_nephos_USER_TABLE="cloud_nephos_users";

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;



// this function is going to sync ldap-users with cloud-users

function htvcenter_ldap_monitor() {
	global $event;
	global $RootDir;
	global $CLOUD_USER_TABLE;
	global $cloud_nephos_USER_TABLE;
	global $htvcenter_SERVER_BASE_DIR;
	$ldap_monitor_lock = $htvcenter_SERVER_BASE_DIR."/htvcenter/web/action/ldap-conf/ldap-monitor.lock";
	$ldap_monitor_timeout = "360";

	// enabled ?
	$ldap_conf = new ldapconfig();
	$ldap_conf->get_instance_by_id(10);
	$ldap_enabled = $ldap_conf->value;
	if ($ldap_enabled == 0) {
		$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "LDAP not yet activated ...", "", "", 0, 0, 0);
		return 0;
	}


	// lock to prevent running multiple times in parallel
	if (file_exists($ldap_monitor_lock)) {
		// check from when it is, if it is too old we remove it and start
		$ldap_monitor_lock_date = file_get_contents($ldap_monitor_lock);
		$now=$_SERVER['REQUEST_TIME'];
		if (($now - $ldap_monitor_lock_date) > $ldap_monitor_timeout) {
			$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 2, "htvcenter-ldap-monitor-hook.php", "Timeout for the ldap-monitor-lock reached, creating new lock at $ldap_monitor_lock", "", "", 0, 0, 0);
			$ldap_lock_fp = fopen($ldap_monitor_lock, 'w');
			fwrite($ldap_lock_fp, $now);
			fclose($ldap_lock_fp);
		} else {
			$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "ldap is still processing ($ldap_monitor_lock), skipping ldap event check !", "", "", 0, 0, 0);
			return 0;
		}
	} else {
		$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Checking for ldap events to be handled. Created $ldap_monitor_lock", "", "", 0, 0, 0);
		$now=$_SERVER['REQUEST_TIME'];
		$ldap_lock_fp = fopen($ldap_monitor_lock, 'w');
		fwrite($ldap_lock_fp, $now);
		fclose($ldap_lock_fp);
	}

	// ################################## start ldap commands ###############################

	// get ldap from db config
	$ldap_conf = new ldapconfig();
	$ldap_conf->get_instance_by_id(1);
	$ldap_host = $ldap_conf->value;
	$ldap_conf->get_instance_by_id(2);
	$ldap_port = $ldap_conf->value;
	$ldap_conf->get_instance_by_id(3);
	$ldap_base_dn = $ldap_conf->value;
	$ldap_conf->get_instance_by_id(4);
	$ldap_admin = $ldap_conf->value;
	$ldap_conf->get_instance_by_id(5);
	$ldap_password = $ldap_conf->value;

	$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Starting User-sync from LDAP -> htvcenter", "", "", 0, 0, 0);

	// get user data from ldap
	$filter = "(uid=*)";
	$connect = ldap_connect($ldap_host, $ldap_port);
	ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
	$bind = ldap_bind($connect, $ldap_admin, $ldap_password);
	$read = ldap_search($connect, $ldap_base_dn, $filter);
	$info = ldap_get_entries($connect, $read);

	// here we get the htvcenter gid number from the ldap
	$dn = "cn=htvcenter,ou=Group,".$ldap_base_dn;
	$filter="(cn=*)";
	$justthese = array("gidNumber"); 
	$sr=ldap_read($connect, $dn, $filter, $justthese);
	$entry = ldap_get_entries($connect, $sr);
	// in the resulting array gidNumber is full lowercase -> gidnumber
	$htvcenter_gid = $entry[0]["gidnumber"][0];
	if (!strlen($htvcenter_gid)) {
		$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 2, "htvcenter-ldap-monitor-hook.php", "Coud not find the htvcenter gidnumber in the ldap server", "", "", 0, 0, 0);
		unlink($ldap_monitor_lock);
		return;
	} else {
		$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Found htvcenter gid number $htvcenter_gid", "", "", 0, 0, 0);
	}

	ldap_close($connect);
	$all_ldap_users = array();

	// htvcenter system user sync
	$htvcenter_user = new user('htvcenter');




	// is ldap user in htvcenter ?
	for($line = 0; $line<$info["count"]; $line++) {
		for($col = 0; $col<$info[$line]["count"]; $col++) {
			if ($col == 0) {
				$data = $info[$line][$col];
				$ldap_user_name = $info[$line][$data][0];
				$ldap_user_gid = $info[$line]['gidnumber'][0];
				$check_htvcenter_user = new user($ldap_user_name);
				if (!$check_htvcenter_user->check_user_exists()) {
					// check for admin user
					if ($ldap_user_gid == $htvcenter_gid) {
						// add
						$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Creating LDAP user $ldap_user_name:$ldap_user_gid in htvcenter", "", "", 0, 0, 0);
						$user_fields['user_id'] = htvcenter_db_get_free_id('user_id', $check_htvcenter_user->_user_table);
						$user_fields['user_name'] = $ldap_user_name;
						$user_fields['user_state'] = 'activated';
						$user_fields['user_role'] = 0;
						$user_fields['user_lang'] = 'en';
						$htvcenter_user->add($user_fields);
					}
				}

				// begin cloud user sync
				// only if cloud is enabled
				if (file_exists("$RootDir/plugins/cloud/.running")) {
					$c_user = new clouduser();
					if($c_user->is_name_free($ldap_user_name)) {
						$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Creating LDAP user $ldap_user_name in the Cloud", "", "", 0, 0, 0);
						// get free cu_id
						$cloud_user_fields['cu_id'] = htvcenter_db_get_free_id('cu_id', $CLOUD_USER_TABLE);
						// TODO
						// auto-give-ccus ?
						$cloud_user_fields['cu_ccunits'] = 0;
						// other fields
						$cloud_user_fields['cu_cg_id'] = 0;
						$cloud_user_fields['cu_status'] = 1;
						$cloud_user_fields['cu_name'] = "$ldap_user_name";
						$cloud_user_fields['cu_password'] = "";
						$cloud_user_fields['cu_lastname'] = "$ldap_user_name";
						$cloud_user_fields['cu_forename'] = "Cloud-User";
						$cloud_user_fields['cu_email'] = "empty";
						$cloud_user_fields['cu_street'] = "empty";
						$cloud_user_fields['cu_city'] = "empty";
						$cloud_user_fields['cu_country'] = "empty";
						$cloud_user_fields['cu_phone'] = "0";
						$cloud_user_fields['cu_lang'] = 'en';
						$cl_user = new clouduser();
						$cl_user->add($cloud_user_fields);
						// set user permissions and limits, set to 0 (infinite) by default
						$cloud_user_limit = new clouduserlimits();
						$cloud_user_limits_fields['cl_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$cloud_user_limits_fields['cl_cu_id'] = $cloud_user_fields['cu_id'];
						$cloud_user_limits_fields['cl_resource_limit'] = 0;
						$cloud_user_limits_fields['cl_memory_limit'] = 0;
						$cloud_user_limits_fields['cl_disk_limit'] = 0;
						$cloud_user_limits_fields['cl_cpu_limit'] = 0;
						$cloud_user_limits_fields['cl_network_limit'] = 0;
						$cloud_user_limit->add($cloud_user_limits_fields);
						// adding complete
					}
				}
				// end cloud user sync


				// begin cloud zones user sync
				// only if cloud is enabled
				if (file_exists("$RootDir/plugins/cloud-nephos/.running")) {
					$cloud_nephos_user = new cloud_nephos_user();
					if($cloud_nephos_user->is_name_free($ldap_user_name)) {
						$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Creating LDAP user $ldap_user_name in Cloud Zones", "", "", 0, 0, 0);
						$cloud_nephos_user_fields = array();
						// auto-give-ccus ?
						$cloud_nephos_user_fields['cloud_nephos_user_ccunits'] = 0;
						// other fields
						$cloud_nephos_user_fields['cloud_nephos_usergroup_id'] = 1;
						$cloud_nephos_user_fields['cloud_nephos_user_name'] = "$ldap_user_name";
						$cloud_nephos_user_fields['cloud_nephos_user_forename'] = "Cloud Zone User";
						$cloud_nephos_user_fields['cloud_nephos_user_lastname'] = "$ldap_user_name";
						$cloud_nephos_user_fields['cloud_nephos_user_email'] = "empty";
						$cloud_nephos_user_fields['cloud_nephos_user_street'] = "empty";
						$cloud_nephos_user_fields['cloud_nephos_user_city'] = "empty";
						$cloud_nephos_user_fields['cloud_nephos_user_country'] = "empty";
						$cloud_nephos_user_fields['cloud_nephos_user_phone'] = "0";
						$cloud_nephos_user_fields['cloud_nephos_user_lang'] = "en";
						$cloud_nephos_user->add($cloud_nephos_user_fields);
						// adding complete
					}
				}
				// end cloud zones user sync

				// add $ldap_user_name to array
				array_push($all_ldap_users, $ldap_user_name);
			}
		}
	}


	// htvcenter user remove
	$htvcenter_username_arr = $htvcenter_user->get_name_list();
	foreach($htvcenter_username_arr as $htvcenter_username) {
		$username = $htvcenter_username['user_name'];
		// keep htvcenter user and anonymous user
		if(!strcmp($username, 'htvcenter')) {
			continue;
		}
		if(!strcmp($username, 'anonymous')) {
			continue;
		}
		if (!in_array($username, $all_ldap_users)) {
			// remove
			$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "htvcenter User $username does not exists in ldap server, removing...", "", "", 0, 0, 0);
			$htvcenter_user->remove_by_name($username);
		}
	}

	// end htvcenter user remove

	// cloud user remove
	// only if cloud is enabled
	if (file_exists("$RootDir/plugins/cloud/.running")) {

		// check array if use still exist in LDAP, if not remove
		$all_cloud_users = new clouduser();
		$all_cloud_users_ids = $all_cloud_users->get_all_ids();
		foreach($all_cloud_users_ids as $cu_id_arr) {
			$cu_id = $cu_id_arr['cu_id'];
			$existing_cloud_user = new clouduser();
			$existing_cloud_user->get_instance_by_id($cu_id);
			if (!in_array($existing_cloud_user->name, $all_ldap_users)) {
				// remove
				$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Cloud User $existing_cloud_user->name does not exists in ldap server, removing...", "", "", 0, 0, 0);
				// remove limits
				$cloud_user_limit = new clouduserlimits();
				$cloud_user_limit->remove_by_cu_id($existing_cloud_user->id);
				// remove user object
				$existing_cloud_user->remove($existing_cloud_user->id);
			}
		}
	}
	// end cloud user remove

	// cloud zones user remove
	// only if cloud zones is enabled
	if (file_exists("$RootDir/plugins/cloud-nephos/.running")) {

		// check array if use still exist in LDAP, if not remove
		$all_cloud_nephos_users = new cloud_nephos_user();
		$all_cloud_nephos_users_ids = $all_cloud_nephos_users->get_all_ids();
		foreach($all_cloud_nephos_users_ids as $cz_u_id_arr) {
			$cz_u_id = $cz_u_id_arr['cloud_nephos_user_id'];
			$existing_cloud_nephos_user = new cloud_nephos_user();
			$existing_cloud_nephos_user->get_instance_by_id($cz_u_id);
			// enabled or just registered ?
			if ($existing_cloud_nephos_user->cloud_nephos_user_status == 1) {
				if (!in_array($existing_cloud_nephos_user->cloud_nephos_user_name, $all_ldap_users)) {
					// remove
					$event->log("htvcenter_ldap_monitor", $_SERVER['REQUEST_TIME'], 5, "htvcenter-ldap-monitor-hook.php", "Cloud Zones User $existing_cloud_user->cloud_nephos_user_name does not exists in ldap server, removing...", "", "", 0, 0, 0);
					// remove user object
					$existing_cloud_nephos_user->remove($existing_cloud_nephos_user->cloud_nephos_user_id);
				}
			}
		}
	}
	// end cloud zones user remove

	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "ldap-monitor", "Removing the ldap-monitor lock $ldap_monitor_lock", "", "", 0, 0, 0);
	unlink($ldap_monitor_lock);
}



?>
