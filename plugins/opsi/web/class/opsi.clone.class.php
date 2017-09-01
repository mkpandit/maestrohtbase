<?php
/**
 * Local-Storage Clone Volume(s)
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class opsi_clone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'opsi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'opsi_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'opsi_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'opsi_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
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
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->volume = $this->response->html->request()->get('volume');

		$this->response->add('storage_id',  $this->response->html->request()->get('storage_id'));
		$this->response->add('volume', $this->volume);

		require_once($this->htvcenter->get('basedir').'/plugins/opsi/web/class/opsi-volume.class.php');
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
		$response = $this->duplicate();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/opsi-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->volume), 'label');
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function duplicate() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {

			$storage_id = $this->response->html->request()->get('storage_id');
			$storage    = new storage();
			$resource   = new resource();
			$deployment = new deployment();
			$storage->get_instance_by_id($storage_id);
			$resource->get_instance_by_id($storage->resource_id);
			$deployment->get_instance_by_id($storage->type);
			$name        = $form->get_request('name');

			// check if volume / image name is aleady existing
			$image_check = new image();
			$image_check->get_instance_by_name($name);
			if (isset($image_check->id) && $image_check->id > 0) {
				$error = sprintf($this->lang['error_image_exists'], $name);
			}

			$opsi_volume = new opsi_volume();
			$opsi_volume->get_instance_by_name($name);
			if (isset($opsi_volume->id) && $opsi_volume->id > 0) {
				$error = sprintf($this->lang['error_exists'], $name);
			}

			if(isset($error)) {
				$response->error = $error;
			} else {
				// add volume
				$opsi_volume->get_instance_by_name($this->volume);
				$volume_fields = array();
				$volume_fields["opsi_volume_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$volume_fields['opsi_volume_name'] = $name;
				$volume_fields['opsi_volume_root'] = $opsi_volume->root;
				$volume_fields['opsi_volume_description'] = $opsi_volume->description;
				$opsi_volume->add($volume_fields);
				// add image
				$tables = $this->htvcenter->get('table');
				$image_fields = array();
				$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields['image_name'] = $name;
				$image_fields['image_type'] = $deployment->type;
				$image_fields['image_rootfstype'] = 'local';
				$image_fields['image_storageid'] = $storage->id;
				$image_fields['image_comment'] = "Image Object for volume $name";
				$image_fields['image_rootdevice'] = 'local';
				$image = new image();
				$image->add($image_fields);

				$response->msg = sprintf($this->lang['msg_cloned'], $this->volume, $name);
				// save image id in response for the wizard
				$response->image_id = $image_fields["image_id"];
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
		$form = $response->get_form($this->actions_name, 'clone');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="opsi_" data-length="8"';
		$d['name']['object']['attrib']['value']         = $this->volume.'_clone';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
