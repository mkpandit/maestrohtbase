<?php
/**
 *  Hybrid-cloud S3 Bucket file delete
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hybrid_cloud_s3_delete
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_s3_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_s3_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_s3_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_s3_tab';
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
		$this->id         = $this->response->html->request()->get('hybrid_cloud_id');
		$this->response->add('hybrid_cloud_id', $this->id);
		$this->region     = $response->html->request()->get('region');
		# bucket
		$this->bucket_name = $this->response->html->request()->get('bucket_name');
		$this->response->add('bucket_name', $this->bucket_name);
		# hybrid-cloud account
		$this->id         = $this->response->html->request()->get('hybrid_cloud_id');
		$this->response->add('hybrid_cloud_id', $this->id);
		require_once($this->htvcenter->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$hc = new hybrid_cloud();
		$hc->get_instance_by_id($this->id);
		# s3 object
		require_once($this->htvcenter->get('basedir').'/plugins/hybrid-cloud/web/class/S3.php');
		$s3 = new S3($hc->access_key, $hc->secret_key);
		$this->s3 = $s3;

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
		$response = $this->delete();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'files', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-s3-delete.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $response->html->request()->get('file_name'), $this->bucket_name), 'label');
		$t->add($this->lang['tab'], 'tab');
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function delete() {
		$response = $this->get_response();
		$file_name  = $response->html->request()->get('file_name');
		$form     = $response->form;

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['param_f']['label']                       = $file_name;
		$d['param_f']['object']['type']              = 'htmlobject_input';
		$d['param_f']['object']['attrib']['type']    = 'checkbox';
		$d['param_f']['object']['attrib']['name']    = 'file_name';
		$d['param_f']['object']['attrib']['value']   = $file_name;
		$d['param_f']['object']['attrib']['checked'] = true;
		$form->add($d);

		if(!$form->get_errors() && $response->submit()) {
			$errors = array();
			$message = array();

			$this->s3->deleteObject($this->bucket_name, $file_name);
			$message[] = sprintf($this->lang['msg_removed'], $file_name);

			if(count($errors) === 0) {
				$response->msg = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response->error = join('<br>', $msg);
			}
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
		$form = $response->get_form($this->actions_name, 'delete');
		$response->form = $form;
		return $response;
	}

}
?>
