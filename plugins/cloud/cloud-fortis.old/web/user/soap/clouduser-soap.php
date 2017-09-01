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


error_reporting(E_ALL);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-fortis/';
require_once "$RootDir/plugins/cloud/class/cloudsoapuser.class.php";

// turn off the wsdl cache
ini_set("soap.wsdl_cache_enabled", "0");
ini_set("session.auto_start", 0);

//for persistent session
session_start();

//service
$ws = "./clouduser.wdsl";
$server = new SoapServer($ws);

// set class to use
$server->setClass("cloudsoapuser");


// make persistant
$server->setPersistence(SOAP_PERSISTENCE_SESSION);

$server->handle();

?>

