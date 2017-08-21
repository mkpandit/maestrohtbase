<?php
ini_set('display_errors','Off');
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/
//$ti = microtime(true);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once "$RootDir/class/htvcenter.controller.class.php";
if (!file_exists('unconfigured')) {
	require_once($RootDir.'/include/user.inc.php');
}
$action = new htvcenter_controller();
$output = $action->action();
echo $output->get_string();

//if(function_exists('memory_get_peak_usage')) {
//	$memory = memory_get_peak_usage(false);
//}
//echo 'memory: '.$memory.' byte<br>';
//$ti = (microtime(true) - $ti);
//echo 'time: '.$ti.' sec';
?>
