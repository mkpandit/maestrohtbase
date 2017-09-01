<?php
/**
 * device-manager Remove LVM Volume Group
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class device_manager_removevg
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab;
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name;
/**
* path to device-managers
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		'label' => 'Remove Volume Group on storage %s',
		'confirm_text' => 'Do you realy want to remove Volume Group %s?',
		'msg_removed' => 'Successfully removed Volume Group %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	);

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
		$this->tpldir   = $this->rootdir.'/plugins/device-manager/tpl';

		$id = $this->response->html->request()->get('storage_id');
		$this->response->add('storage_id', $id);
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));

		$storage = new storage();
		$this->storage = $storage->get_instance_by_id($id);

		$resource = new resource();
		$this->resource = $resource->get_instance_by_id($this->storage->resource_id);
		
		$this->statfile = $this->htvcenter->get('basedir').'/plugins/device-manager/web/storage/'.$this->resource->id.'.device.stat';
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
		$response = $this->removevg();
		$deployment = new deployment();
		$deployment->get_instance_by_id($this->storage->type);

		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}

		$data['confirm_text'] = sprintf($this->lang['confirm_text'], $this->response->html->request()->get('volgroup'));
		$data['plugin'] = $deployment->storagetype;
		$data['label'] = sprintf($this->lang['label'], $this->storage->name);
		$data['baseurl'] = $this->htvcenter->get('baseurl');
		$data['thisfile']    = $response->html->thisfile;
		$t = $response->html->template($this->tpldir.'/device-manager-removevg.tpl.php');
		$t->add($response->form);
		$t->add($data);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove Volume Group
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function removevg() {
		// get response
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$command  = $this->htvcenter->get('basedir').'/plugins/device-manager/bin/htvcenter-device-manager remove_vg';
			$command .= ' -v '.$this->response->html->request()->get('volgroup');
			$command .= ' -u '.$this->htvcenter->admin()->name;
			$command .= ' -p '.$this->htvcenter->admin()->password;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode regular';
			if($this->file->exists($this->statfile)) {
				$this->file->remove($this->statfile);
			}
			$this->resource->send_command($this->resource->ip, $command);
			while (!$this->file->exists($this->statfile)) // check if the data file has been modified
			{
				usleep(10000); // sleep 10ms to unload the CPU
				clearstatcache();
			}
			$result = trim($this->file->get_contents($this->statfile));
			if($result === 'ok') {
				$response->msg = sprintf($this->lang['msg_removed'], $this->response->html->request()->get('volgroup'));
			}
			else if($result !== 'ok') {
				$response->error = $result;
			}
		} else {
			if($form->get_errors()) {
				$_REQUEST[$this->message_param] = implode("<br>", $form->get_errors());
			}
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
		$form     = $response->get_form($this->actions_name, 'removevg');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$cancel = $form->get_elements('cancel');
		$cancel->handler = 'onclick="cancel();"';
		$form->add($cancel, 'cancel');

		$form->display_errors = false;

		$response->form = $form;
		return $response;
	}

}
?>
