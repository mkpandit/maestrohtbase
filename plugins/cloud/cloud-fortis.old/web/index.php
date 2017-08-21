<?php

/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

// add custom-branding header
require_once "custom-branding-header.php";

require_once($_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/include/user.inc.php');
require_once('user/class/cloud.controller.class.php');
$controller = new cloud_controller();
echo $controller->register()->get_string();

// reserve some space
?>
<br>
<br>
<br>
<br>

