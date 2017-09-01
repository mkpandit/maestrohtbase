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
require_once($RootDir.'/class/htvcenter.controller.class.php');
if (!file_exists('unconfigured')) {
	require_once($RootDir.'/include/user.inc.php');
}
$controller = new htvcenter_controller();
$controller->api();
?>
