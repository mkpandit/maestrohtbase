<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/
// check if configured already
if (file_exists("./unconfigured")) {
    header("Location: configure.php");
}
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
require_once($RootDir.'class/htmlobjects/htmlobject.class.php');
require_once($RootDir.'class/htvcenter.class.php');
require_once($RootDir.'class/resource.class.php');
global $htvcenter_SERVER_BASE_DIR;
global $htvcenter_EXECUTION_LAYER;
global $htvcenter_WEB_PROTOCOL;


$lang = array(
	'label' => 'License upload',
	'upload' => 'Please select the htvcenter Enterprise Server License File and Public Key from your local computer and submit',
	'msg' => 'Uploaded License file %s',
);


$html = new htmlobject($RootDir.'class/htmlobjects/');

$response = $html->response();
$form = $response->get_form();
$form->box_css = 'htmlobject_box';
$form->display_errors = true;

$d['upload']['label'] = $lang['upload'];
$d['upload']['object']['type']           = 'input';
$d['upload']['object']['attrib']['type'] = 'file';
$d['upload']['object']['attrib']['name'] = 'upload';
$d['upload']['object']['attrib']['size'] = 30;

$form->add($html->thisfile, 'thisfile');
$form->add($d);

if(!$form->get_errors() && $response->submit()) {
	require_once($RootDir.'class/file.handler.class.php');
	require_once($RootDir.'class/file.upload.class.php');
	$file = new file_handler();
	$upload = new file_upload($file);
	$error = $upload->upload('upload', $RootDir.'tmp');
	if($error !== '') {
		$form->set_error('upload', $error['msg']);
	} else {
		$resource_command = $htvcenter_SERVER_BASE_DIR."/htvcenter/bin/htvcenter license -l ".$htvcenter_SERVER_BASE_DIR."/htvcenter/web/base/tmp/".$_FILES['upload']['name']." --htvcenter-cmd-mode background";
		$resource = new resource();
		$resource->get_instance_by_id(0);
		$resource->send_command($resource->ip, $resource_command);
		$response_msg = sprintf($lang['msg'], $_FILES['upload']['name']);
		sleep(4);
		$response->redirect('/htvcenter/base/index.php?datacenter_msg='.$response_msg);
	}
}
$tpl = $html->template($RootDir.'tpl/upload.tpl.php');
$tpl->add($html->thisurl, 'baseurl');
$tpl->add($lang['label'], 'label');
$tpl->add($form->get_elements());
echo $tpl->get_string();
?>
