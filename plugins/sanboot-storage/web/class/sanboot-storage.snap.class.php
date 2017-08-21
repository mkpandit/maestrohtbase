<?php
/**
 * sanboot-Storage snap Volume
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class sanboot_storage_snap
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'sanboot_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "sanboot_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'sanboot_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'sanboot_identifier';
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
		$this->file = $this->htvcenter->file();
		$this->user = $htvcenter->user();
		$this->lvol = $this->response->html->request()->get('lvol');
		$this->volgroup = $this->response->html->request()->get('volgroup');

		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = new storage();
		$resource         = new resource();
		$deployment       = new deployment();
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);

		$this->response->add('storage_id', $storage_id);
		$this->response->add('volgroup', $this->volgroup);
		$this->response->add('lvol', $this->lvol);
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
		$this->set_max();
		$response = $this->snap();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
				);
			} else {
				$this->response->params['reload'] = 'false';
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/sanboot-storage-snap.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->lvol), 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * snap
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function snap() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			if($form->get_request('size') > $this->max) {
				$form->set_error('size', sprintf($this->lang['error_size_exeeded'], number_format($this->max, 0, '', '')));
			}
			if(!$form->get_errors()) {
				$storage_id = $this->response->html->request()->get('storage_id');
				$storage    = new storage();
				$resource   = new resource();
				$deployment = new deployment();
				$storage->get_instance_by_id($storage_id);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);

				$name        = $form->get_request('name');
				$command     = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/bin/htvcenter-sanboot-storage snap';
				$command    .= ' -t '.$this->deployment->type;
				$command    .= ' -v '.$this->volgroup;
				$command    .= ' -n '.$this->lvol;
				$command    .= ' -m '.$form->get_request('size');
				$command    .= ' -s '.$name;
				$command    .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				if($this->deployment->type === 'iscsi-san-deployment') {
					$image    = new image();
					$command .= ' -i '.$image->generatePassword(12);
				}

				$statfile = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
				if (file_exists($statfile)) {
					$lines = explode("\n", file_get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$error = sprintf($this->lang['error_exists'], $name);
								}
							}
						}
					}
				}
				if(isset($error)) {
					$response->error = $error;
				} else {
					if(file_exists($statfile)) {
						unlink($statfile);
					}
					$this->resource->send_command($this->resource->ip, $command);
					while (!file_exists($statfile)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}


					switch($this->deployment->type) {
						case 'aoe-san-deployment':
							// for sanboot-aoe deployment we need to get the shelf + slot from get_root_identifiert
							$ident_file = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$this->resource->id.'.lv.aoe-san-deployment.ident';
							$get_ident_command  = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/bin/htvcenter-sanboot-storage post_identifier';
							$get_ident_command .= ' -t '.$this->deployment->type.' -v '.$this->volgroup;
							$get_ident_command    .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
							$ident_file = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$this->resource->id.'.lv.aoe-san-deployment.ident';
							if(file_exists($ident_file)) {
								unlink($ident_file);
							}
							$this->resource->send_command($this->resource->ip, $get_ident_command);
							while (!file_exists($ident_file)) {
								usleep(10000); // sleep 10ms to unload the CPU
								clearstatcache();
							}
							$ident_lines = explode("\n", file_get_contents($ident_file));
							if(count($ident_lines) >= 1) {
								foreach($ident_lines as $ident_line) {
									if($ident_line !== '') {
										$ident_line = explode(',', $ident_line);
										$ident_root_path = explode(':', $ident_line[1]);
										$ident_check = $ident_root_path[1];
										if($name === $ident_check) {
											$volume_path = $ident_line[1];
											$rootfstype = 'local';
											break;
										}
									}
								}
							}
							break;
						case 'iscsi-san-deployment':
							$volume_path = $line[2].':/dev/'.$line[1].':'.$line[1].'/0';
							$rootfstype = 'local';
							break;
					}

					$tables = $this->htvcenter->get('table');
					$image_fields = array();
					$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$image_fields['image_name'] = $name;
					$image_fields['image_type'] = $deployment->type;
					$image_fields['image_rootfstype'] = $rootfstype;
					$image_fields['image_storageid'] = $storage->id;
					$image_fields['image_comment'] = "Image Object for volume $name";
					$image_fields['image_rootdevice'] = $volume_path;
					$image = new image();
					$image->add($image_fields);

					$response->msg = sprintf($this->lang['msg_snaped'], $this->lvol, $name);
					// save image id in response for the wizard
					$response->image_id = $image_fields["image_id"];
				}
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
		$form = $response->get_form($this->actions_name, 'snap');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = $this->lvol.'_snap';
		$d['name']['object']['attrib']['maxlength'] = 50;

		$d['size']['label']                         = sprintf($this->lang['form_size'], number_format($this->max, 0, '', ''));
		$d['size']['required']                      = true;
		$d['size']['validate']['regex']             = '/^[0-9]+$/i';
		$d['size']['validate']['errormsg']          = sprintf($this->lang['error_size'], '0-9');
		$d['size']['object']['type']                = 'htmlobject_input';
		$d['size']['object']['attrib']['name']      = 'size';
		$d['size']['object']['attrib']['type']      = 'text';
		$d['size']['object']['attrib']['value']     = '';
		$d['size']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Set max
	 *
	 * @access protected
	 * @return bool
	 */
	//--------------------------------------------
	function set_max() {
		$vgmax = '';
		$sanbootax = '';
		$statfile = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
		if (file_exists($statfile)) {
			$lines = explode("\n", file_get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[1] === $this->lvol) {
							$sanbootax = str_replace('.', '', $line[4]);
							$sanbootax = str_replace('m', '', $sanbootax);
							$sanbootax = (int)$sanbootax / 100;
						}
					}
				}
			}
		}
		$statfile = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$this->resource->id.'.vg.stat';
		if (file_exists($statfile)) {
			$lines = explode("\n", file_get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[0] === $this->volgroup) {
							$vgmax = str_replace('.', '', $line[6]);
							$vgmax = str_replace('m', '', $vgmax);
							$vgmax = (int)$vgmax / 100;
						}
					}
				}
			}
		}

		if(	$vgmax < $sanbootax ) {
			$max = $vgmax;
		} else {
			$max = $sanbootax;
		}
		$this->max = $max;
		return true;
	}

}
?>
