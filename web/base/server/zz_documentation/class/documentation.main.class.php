<?php
/**
 * Documentation Main
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class documentation_main
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'documentation_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "documentation_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'documentation_tab';
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
		$t = $this->response->html->template($this->tpldir.'/documentation-main.tpl.php');
		$t->add($this->lang['title'], 'title');
		$t->add($this->lang['technical'], 'technical');
		$t->add($this->lang['technical_description'], 'technical_description');
		$t->add($this->lang['technical_url'], 'technical_url');
		$t->add($this->lang['howtos'], 'howtos');
		$t->add($this->lang['howto1_title'], 'howto1_title');
		$t->add($this->lang['howto1_url'], 'howto1_url');
		$t->add($this->lang['howto2_title'], 'howto2_title');
		$t->add($this->lang['howto2_url'], 'howto2_url');
		$t->add($this->lang['howto3_title'], 'howto3_title');
		$t->add($this->lang['howto3_url'], 'howto3_url');
		$t->add($this->lang['api'], 'api');
		$t->add($this->lang['api_description'], 'api_description');
		$t->add($this->lang['api_url'], 'api_url');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
