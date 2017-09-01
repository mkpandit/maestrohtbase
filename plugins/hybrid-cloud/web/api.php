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
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/htvcenter.class.php";
require_once $RootDir."/plugins/hybrid-cloud/class/hybrid-cloud.controller.class.php";
require_once $RootDir."/class/htmlobjects/htmlobject.class.php";
$html = new htmlobject($RootDir."/class/htmlobjects/");
$response = $html->response();

require_once($RootDir.'/class/file.handler.class.php');
$file = new file_handler();

require_once($RootDir.'/class/user.class.php');
$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user();

$htvcenter = new htvcenter($file, $user);
$htvcenter->init();

$controller = new hybrid_cloud_controller($htvcenter, $response);
$controller->api();
?>
