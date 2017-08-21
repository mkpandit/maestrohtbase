<?php
/**
 * Cloud User Portal Home
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_register_home
{
var $tpldir;
var $lang;
var $actions_name = 'cloud-register-home';

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->portaldir = '/cloud-fortis/';
	}

	//--------------------------------------------
	/**
	 * Cloud User Portal Home
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$t = $this->response->html->template($this->tpldir."/cloud-register-home.tpl.php");
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->portaldir, "portaldir");
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

}
?>
