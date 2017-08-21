<?php
/**
 * Kernel Add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kernel_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kernel_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "kernel_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kernel_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kernel_identifier';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response   = $response;
		$this->file       = $htvcenter->file();
		$this->htvcenter    = $htvcenter;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$t = $this->response->html->template($this->tpldir.'/kernel-add.tpl.php');
		$t->add($this->lang['title'], 'title');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['kernel_add'], 'kernel_add');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
