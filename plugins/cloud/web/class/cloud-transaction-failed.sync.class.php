<?php
/**
 * Cloud failed-Transaction Sync
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_transaction_failed_sync
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud_transaction_failed';



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
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->webdir  = $this->htvcenter->get('webdir');
		$this->rootdir  = $this->htvcenter->get('basedir');
		require_once $this->webdir."/plugins/cloud/class/cloudtransaction.class.php";
		$this->cloudtransaction = new cloudtransaction();
		require_once $this->webdir."/plugins/cloud/class/cloudtransactionfailed.class.php";
		$this->cloudtransactionfailed = new cloudtransactionfailed();
		require_once $this->webdir."/plugins/cloud/class/clouduser.class.php";
		$this->cloud_user = new clouduser();

	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		if($this->response->html->request()->get($this->identifier_name) !== '') {
			$response = $this->sync();
			if(isset($response->msg)) {
				$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
			}
			$template = $this->response->html->template($this->tpldir."/cloud-transaction-failed-sync.tpl.php");
			$template->add($response->form->get_elements());
			$template->add($response->html->thisfile, "thisfile");
			$template->add($this->lang['cloud_transaction_failed_confirm_sync'], 'confirm_sync');
			$template->group_elements(array('param_' => 'form'));
			return $template;
		} else {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select'));
		}
	}

	//--------------------------------------------
	/**
	 * Cloud failed-Transaction Sync
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function sync() {

		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$request = $form->get_request($this->identifier_name);
			if(isset($request) && is_array($request)) {
				$errors  = array();
				$message = array();
				foreach($request as $key => $cz_ug_id) {
					$this->cloudtransactionfailed->get_instance_by_id($cz_ug_id);
					$this->cloudtransaction->get_instance_by_id($this->cloudtransactionfailed->ct_id);
					//sync here
					if ($this->cloudtransaction->sync($this->cloudtransactionfailed->ct_id, false)) {
						$this->cloudtransactionfailed->remove($cz_ug_id);
						$message[] = $this->lang['cloud_transaction_synced']." - ".$cz_ug_id;
					} else {
						$message[] = $this->lang['cloud_transaction_sync_failed']." - ".$cz_ug_id;
					}
				}

				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		}
		return $response;
	}


	function get_response() {
		$tosync = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'sync');
		$d        = array();
		if( $tosync !== '' ) {
			$i = 0;
			foreach($tosync as $cz_ug_id) {
				$this->cloudtransactionfailed->get_instance_by_id($cz_ug_id);
				$d['param_f'.$i]['label']                       = $cz_ug_id;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'[]';
				$d['param_f'.$i]['object']['attrib']['id']      = $this->identifier_name.'_'.$i;
				$d['param_f'.$i]['object']['attrib']['value']   = $cz_ug_id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
		}
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>


