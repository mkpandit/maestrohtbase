<?php
/**
 * iSCSI-Storage Auth Volume(s)
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class iscsi_storage_auth
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'iscsi_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "iscsi_storage_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'iscsi_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'iscsi_tab';
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
		$this->htvcenter = $htvcenter;
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->volume = $this->response->html->request()->get('volume');
		$this->response->params['volume'] = $this->volume;
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
		$response = $this->auth();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/iscsi-storage-auth.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->volume), 'label');
		// explanation for auth
		$t->add($this->lang['auth_explanation'], 'auth_explanation');
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Auth
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function auth() {
		$response = $this->get_response();
		$export   = $response->html->request()->get('volume');
		$form     = $response->form;
		if( $export !== '' ) {
			if(!$form->get_errors() && $response->submit()) {
				// set ENV
				$storage_id = $this->response->html->request()->get('storage_id');
				$storage    = new storage();
				$resource   = new resource();
				$storage->get_instance_by_id($storage_id);
				$resource->get_instance_by_id($storage->resource_id);

				$errors  = array();
				$message = array();
				$auths   = $form->get_request('pass');
				$statfile = $this->htvcenter->get('basedir').'/plugins/iscsi-storage/web/storage/'.$resource->id.'.iscsi.stat';

				$error = '';
				$command  = $this->htvcenter->get('basedir').'/plugins/iscsi-storage/bin/htvcenter-iscsi-storage auth';
				$command .= ' -n '.$export.' -i '.$auths;
				$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				if($this->file->exists($statfile)) {
					$this->file->remove($statfile);
				}
				$resource->send_command($resource->ip, $command);
				while (!$this->file->exists($statfile)) {
	  				usleep(10000); // sleep 10ms to unload the CPU
	  				clearstatcache();
				}
				$message[] = sprintf($this->lang['msg_authd'], $export, $auths);
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'auth');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['pass']['label']                         = $this->lang['form_pass'];
		$d['pass']['required']                      = true;
		$d['pass']['object']['type']                = 'htmlobject_input';
		$d['pass']['object']['attrib']['name']      = 'pass';
		$d['pass']['object']['attrib']['type']      = 'password';
		$d['pass']['object']['attrib']['value']     = '';
		$d['pass']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
