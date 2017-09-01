<?php
/**
 * puppet config
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class puppet_config
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'puppet_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "puppet_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'puppet_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'puppet_identifier';
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
		$this->htvcenter  = $htvcenter;
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';

		require_once($this->rootdir.'/plugins/puppet/class/puppetconfig.class.php');
		$this->puppetconfig = new puppetconfig();

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
	function action() {

		$response = $this->config();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$response->redirect(
					$response->get_url($this->actions_name, 'config', $this->message_param, $response->msg)
			);
		}
		$data['please_wait'] = $this->lang['please_wait'];
		$data['prefix_tab'] = $this->prefix_tab;
		$data['label'] = sprintf($this->lang['label']);
		$data['baseurl'] = $this->htvcenter->get('baseurl');
		$data['thisfile']    = $response->html->thisfile;
		$t = $response->html->template($this->tpldir.'/puppet-config.tpl.php');
		$t->add($response->form);
		$t->add($data);
		$t->group_elements(array('param_' => 'form', 'f_' => 'fields'));

		return $t;
	}


	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function config() {
		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$request = $form->get_request();
			foreach($request as $k => $v) {
				$fields['cc_key'] = $k;
				foreach($v as $id => $value) {
					$fields['cc_id'] = $id;
					$fields['cc_value'] = $value;
				}
				$this->puppetconfig->remove($fields['cc_id']);
				$this->puppetconfig->add($fields);
			}
			$response->msg = $this->lang['msg_updated'];
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {

		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'config');
		$fields = $this->puppetconfig->display_overview(0, 10000, 'cc_id', 'ASC');
		$i = 0;
		foreach($fields as $k => $v) {
			$d['f_'.$i]['label']                         = $v['cc_key'];
			$d['f_'.$i]['required']                      = true;
			$d['f_'.$i]['object']['type']                = 'htmlobject_input';
			$d['f_'.$i]['object']['attrib']['name']      = $v['cc_key'].'['.$v['cc_id'].']';
			$d['f_'.$i]['object']['attrib']['value']     = $v['cc_value'];
			$d['f_'.$i]['object']['attrib']['maxlength'] = 50;
			$i++;
		}
		$form->add($d);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$response->form = $form;
		return $response;

	}

}
?>
