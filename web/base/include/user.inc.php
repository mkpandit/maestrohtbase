<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once( $RootDir.'/include/htvcenter-database-functions.php');
global $USER_INFO_TABLE;

require_once( $RootDir.'/class/user.class.php');
require_once ($RootDir.'/class/event.class.php');

function set_env() {
	// auth user
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		$htvcenter_USER = new user($_SERVER['PHP_AUTH_USER']);
		if ($htvcenter_USER->check_user_exists()) {
			$htvcenter_USER->set_user();
			$GLOBALS['htvcenter_USER'] = $htvcenter_USER;
			define('htvcenter_USER_NAME', $htvcenter_USER->name);
			define('htvcenter_USER_ROLE_NAME', $htvcenter_USER->role);
		}
	}
	// admin user for running commands
	$htvcenter_ADMIN = new user('htvcenter');
	$htvcenter_ADMIN->set_user();
	$GLOBALS['htvcenter_ADMIN'] = $htvcenter_ADMIN;
}

set_env();

?>
