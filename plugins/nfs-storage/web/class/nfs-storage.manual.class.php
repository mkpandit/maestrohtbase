<?php
/**
 * NFS-Storage Manual Configuration
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class nfs_storage_manual
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'nfs_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "nfs_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'nfs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'nfs_identifier';
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
		$this->user	    = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->storage = $this->response->html->request()->get('storage_id');
		$this->response->params['storage_id'] = $this->storage;
		
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'manual', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/nfs-storage-manual.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$config     = $form->get_request('config');
			$storage_id = $this->response->html->request()->get('storage_id');
			$storage    = new storage();
			$resource   = new resource();
			$storage->get_instance_by_id($storage_id);
			$resource->get_instance_by_id($storage->resource_id);
			$file = $this->htvcenter->get('basedir')."/plugins/nfs-storage/web/storage/".$resource->id.".nfs.stat.manual";
			$image = new image();
			$error = '';
			// get old data
			$old = array();
			if($this->file->exists($file)) {
				$old = $this->file->get_contents($file);
				$old = str_replace("\r\n", "\n", $old);
				$old = explode("\n", $old);
			}
			if(isset($config) && $config !== '') {
				$new = str_replace("\r\n", "\n", $config);
				$new = explode("\n", $new);
				$new = array_unique($new);
				// sync old and new values
				foreach($old as $v) {
					if(!in_array($v, $new)) {
						$name = substr($v, strrpos($v, '/')+1);
						if(isset($name) && $name !== '' && $name !== false) {
							$current = $image->get_instance_by_name($name);
							if(isset($current) && $current->name === $name) {
								$image->remove($current->id);
							}
						}
					}
					else if(in_array($v, $new)) {
						unset($new[array_search($v, $new)]);
					}
				}
				// add new values to images
				foreach($new as $v) {
					$name = substr($v, strrpos($v, '/')+1);
					if(isset($name) && $name !== '' && $name !== false) {
						$current = $image->get_instance_by_name($name);
						if(isset($current) && $current->name === $name) {
								$error[] = sprintf($this->lang['error_image_in_use'], $name, $v);
						}
						else if($v !== '') {
							$tables = $this->htvcenter->get('table');
							$f['image_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
							$f['image_name'] = $name;
							$f['image_type'] = 'nfs-deployment';
							$f['image_rootfstype'] = 'nfs';
							$f['image_rootdevice'] = $v;
							$f['image_storageid'] = $this->storage;
							$f['image_comment'] = 'Manually added nfs export';
							$image->add($f);
						}
					}
					else if ((!isset($name) || $name === '' || $name === false) && $v !== '') {
						$error[] = $this->lang['error_name_empty'];
					}
				}
				// handle config file
				if($error === '') {
					if (!$handle = fopen($file, 'w+')) {
						$error = "Cannot open file ($file)";
					}
					if (fwrite($handle, $config) === FALSE) {
						$error = "Cannot write to file ($file)";
					}		
					if($error !== '') {
						$response->error = $error;
					} else {
						$response->msg = $this->lang['saved'];
					}
				} else {
					if(is_array($error)) {
						$response->error = implode('<br>', $error);
					}
					else if(is_string($error)) {
						$response->error = $error;
					}
				}
			} else {
				// remove old values from image table
				foreach($old as $v) {
					if(!in_array($v, $new)) {
						$name = substr($v, strrpos($v, '/')+1);
						if(isset($name) && $name !== '' && $name !== false) {
							$current = $image->get_instance_by_name($name);
							if(isset($current) && $current->name === $name) {
								$image->remove($current->id);
							}
						}
					}
				}
				if($this->file->exists($file)) {
					$error = $this->file->remove($file);
				}
				if(count($old) > 1) {
					$response->msg = $this->lang['saved'];
				} else {
					$response->msg = '';
				}
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
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
		$form = $response->get_form($this->actions_name, 'manual');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$storage_id  = $this->storage;
		$storage     = new storage();
		$resource    = new resource();
		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$file = $this->htvcenter->get('basedir')."/plugins/nfs-storage/web/storage/".$resource->id.".nfs.stat.manual";

		$d['config']['label']                    = 'Exports';
		$d['config']['validate']['regex']        = '~^[\r\na-z0-9/._-]+$~i';
		$d['config']['validate']['errormsg']     = sprintf($this->lang['error_config'], 'a-z0-9/._-');	
		$d['config']['object']['type']           = 'htmlobject_textarea';
		$d['config']['object']['attrib']['name'] = 'config';
		if($this->file->exists($file)) {
			$d['config']['object']['attrib']['value'] = $this->file->get_contents($file);
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
