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


class ldap_update
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ldap_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "ldap_msg";
var $lang;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param db $db
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter  = $htvcenter;
		$this->admin	= $this->htvcenter->admin();
		$this->basedir	= $this->htvcenter->get('basedir');
		require_once ($this->htvcenter->get('basedir').'/plugins/ldap/web/class/ldapconfig.class.php');
		$this->ldap = new ldapconfig();
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
		$response = $this->update();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'update', $this->message_param, $response->msg));
		}

		$chk = $this->response->html->a();
		$chk->label = $this->lang['check'];
		$chk->href = $this->response->get_url($this->actions_name, 'check');
		$chk->handler = 'onclick="wait();"';

		$template = $this->response->html->template($this->tpldir."/ldap-update.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($chk, "check");
		$template->add($this->lang['title'], "title");
		$template->add($this->htvcenter->get('baseurl'), "baseurl");
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form', 'ldap_' => 'ldap'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response("update");
		$form     = $response->form;
		if(!$form->get_errors()	&& $response->submit()) {
			$ini = $form->get_request();
			foreach($ini as $key => $value) {
				$this->ldap->set_value_by_key($key, $value);
			}
			$htvcenter_server = new htvcenter_server();
			if(isset($ini['enabled'])) {
				$this->ldap->set_value_by_key('enabled', '1');
				$ldap_command = $this->basedir."/plugins/ldap/etc/init.d/htvcenter-plugin-ldap activate ".$this->admin->name." ".$this->admin->password;
				$htvcenter_server->send_command($ldap_command);
			} else {
				$this->ldap->set_value_by_key('enabled', '0');
				$ldap_command = $this->basedir."/plugins/ldap/etc/init.d/htvcenter-plugin-ldap deactivate ".$this->admin->name." ".$this->admin->password;
				$htvcenter_server->send_command($ldap_command);
			}
			$response->msg = $this->lang['msg_updated'];
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "update");
		$i = 0;
		$d = array();
		$ini = $this->ldap->display_overview(0, 100, 'csc_id', 'ASC');
		foreach($ini as $v) {
			$d['ldap_'.$i]['label'] = $v['csc_key'];
			$d['ldap_'.$i]['required'] = true;
			$d['ldap_'.$i]['object']['type'] = 'input';
			$d['ldap_'.$i]['object']['attrib']['name'] = $v['csc_key'];
			if($v['csc_key'] === 'ldap-password') {
				$d['ldap_'.$i]['object']['attrib']['type'] = 'password';
			}
			if($v['csc_key'] === 'enabled') {
				$d['ldap_'.$i]['label'] = $this->lang['enabled'];
				$d['ldap_'.$i]['required'] = false;
				$d['ldap_'.$i]['object']['attrib']['type'] = 'checkbox';
				if($v['csc_value'] === '1') {
					$d['ldap_'.$i]['object']['attrib']['checked'] = true;
				}
			}
			if(isset($v['csc_value'])) {
				$d['ldap_'.$i]['object']['attrib']['value'] = $v['csc_value'];
			}
			$i++;
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
