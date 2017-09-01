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
$cloud_nephos_dir = $_SERVER["DOCUMENT_ROOT"].'/cloud-nephos/';

require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/htvcenter_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;
// special ldapconfig class
require_once "$RootDir/plugins/ldap/class/ldapconfig.class.php";

global $htvcenter_SERVER_BASE_DIR;
$refresh_delay=5;

$ldap_command = $request->get('ldap_command');

$event = new event();
$htvcenter_server = new htvcenter_server();
$htvcenter_SERVER_IP_ADDRESS=$htvcenter_server->get_ip_address();
global $htvcenter_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($htvcenter_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "ldap-action", "Un-Authorized access to ldap-actions from $htvcenter_USER->name", "", "", 0, 0, 0);
	exit();
}


// main
$event->log("$ldap_command", $_SERVER['REQUEST_TIME'], 5, "ldap-action", "Processing ldap command $ldap_command", "", "", 0, 0, 0);

	switch ($ldap_command) {

		case 'init':

			// -> ldap_config
			// csc_id BIGINT
			// csc_key VARCHAR(50)
			// csc_value VARCHAR(50)

			$create_ldap_config = "create table ldap_config(csc_id BIGINT, csc_key VARCHAR(50), csc_value VARCHAR(50))";
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($create_ldap_config);

			// create the default configuration
			$create_default_ldap_config1 = "insert into ldap_config(csc_id, csc_key, csc_value) values (1, 'ldap-host', '192.168.88.3')";
			$recordSet = $db->Execute($create_default_ldap_config1);
			$create_default_ldap_config2 = "insert into ldap_config(csc_id, csc_key, csc_value) values (2, 'ldap-port', '389')";
			$recordSet = $db->Execute($create_default_ldap_config2);
			$create_default_ldap_config3 = "insert into ldap_config(csc_id, csc_key, csc_value) values (3, 'base-dn', 'dc=htvcenterldap,dc=com')";
			$recordSet = $db->Execute($create_default_ldap_config3);
			$create_default_ldap_config4 = "insert into ldap_config(csc_id, csc_key, csc_value) values (4, 'ldap-admin', 'cn=admin,dc=htvcenterldap,dc=com')";
			$recordSet = $db->Execute($create_default_ldap_config4);
			$create_default_ldap_config5 = "insert into ldap_config(csc_id, csc_key, csc_value) values (5, 'ldap-password', 'password')";
			$recordSet = $db->Execute($create_default_ldap_config5);
			$create_default_ldap_config6 = "insert into ldap_config(csc_id, csc_key, csc_value) values (10, 'enabled', '0')";
			$recordSet = $db->Execute($create_default_ldap_config6);

			$db->Close();
			break;

		case 'uninstall':
			$drop_ldap_config = "drop table ldap_config";
			$db=htvcenter_get_db_connection();
			$recordSet = $db->Execute($drop_ldap_config);
			$db->Close();
			break;


		case 'get-httpd-base-config':
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
			// print out config
			echo "
<Directory ".$RootDir.">
    Options FollowSymLinks
    AuthType Basic
    AuthBasicProvider file ldap
    AuthName 'htvcenter Server protected by LDAP: Please login with your LDAP user id'
    AuthzLDAPAuthoritative off
    AuthLDAPURL ldap://".$ldap_host.":".$ldap_port."/ou=People,".$ldap_base_dn."?uid
    AuthLDAPBindDN 'cn=admin,".$ldap_base_dn."'
    AuthLDAPBindPassword ".$ldap_password."
    AuthUserFile ".$RootDir.".htpasswd
    require valid-user
</Directory>
";
			break;

		case 'get-httpd-cloud-config':
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
			// print out config
			echo "
<Directory ".$CloudDir."user>
    Options FollowSymLinks
    AuthType Basic
    AuthBasicProvider ldap
    AuthName 'htvcenter Cloud protected by LDAP: Please login with your LDAP user id'
    AuthzLDAPAuthoritative on
    AuthLDAPURL ldap://".$ldap_host.":".$ldap_port."/ou=People,".$ldap_base_dn."?uid
    AuthLDAPBindDN 'cn=admin,".$ldap_base_dn."'
    AuthLDAPBindPassword ".$ldap_password."
    require valid-user
</Directory>
";
			break;

		case 'get-httpd-cloud-nephos-config':
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
			// print out config
			echo "
<Directory ".$cloud_nephos_dir."user>
    Options FollowSymLinks
    AuthType Basic
    AuthBasicProvider ldap
    AuthName 'htvcenter Cloud Zones protected by LDAP: Please login with your LDAP user id'
    AuthzLDAPAuthoritative on
    AuthLDAPURL ldap://".$ldap_host.":".$ldap_port."/ou=People,".$ldap_base_dn."?uid
    AuthLDAPBindDN 'cn=admin,".$ldap_base_dn."'
    AuthLDAPBindPassword ".$ldap_password."
    require valid-user
</Directory>
";
			break;



		default:
			$event->log("$ldap_command", $_SERVER['REQUEST_TIME'], 3, "ldap-action", "No such ldap command ($ldap_command)", "", "", 0, 0, 0);
			break;


	}






?>
