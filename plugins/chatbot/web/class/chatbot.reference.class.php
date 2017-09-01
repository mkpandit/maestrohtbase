<?php
/**
 * chatbot select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class chatbot_reference
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'chatbot_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "chatbot_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'chatbot_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'chatbot_identifier';
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

public $config = array();

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
		$this->htvcenter  = $htvcenter;
		$this->file       = $htvcenter->file();
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->tpldir   = $this->rootdir.'/plugins/chatbot/tpl';
		$this->statfile  = $this->htvcenter->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$t = $this->response->html->template($this->tpldir.'/chatbot-reference.tpl.php');
		$t->add('Maestro ChatBot', 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$this->config = $config;
		return $t;

	}
}
?>
