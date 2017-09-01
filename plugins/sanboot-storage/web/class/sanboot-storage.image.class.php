<?php
/**
 * Sanboot-Storage add/remove Images
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class sanboot_storage_image
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
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'sanboot_tab';
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
		$this->htvcenter    = $htvcenter;
		$this->user       = $htvcenter->user();
		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = new storage();
		$resource         = new resource();
		$deployment       = new deployment();
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);
		$this->response->add('storage_id', $storage_id);
		$this->volgroup   = $this->response->html->request()->get('volgroup');
		$this->response->add('volgroup', $this->volgroup);
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
		$response = $this->image();
		$this->response->params['reload'] = 'false';
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response)
		);
	}

	//--------------------------------------------
	/**
	 * Add/Remove image object
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function image() {
		$response = '';
		$errors       = array();
		$message      = array();
		$image_command = $this->response->html->request()->get('image_command');

		if( $image_command !== '' ) {
			switch ($image_command) {
				case 'add':
					$root_device = $this->response->html->request()->get('root_device');
					$image_name = $this->response->html->request()->get('image_name');

					// check if image name is not in use yet
					$image = new image();
					$image->get_instance_by_name($image_name);
					if (strlen($image->id)) {
						$errors[] = sprintf($this->lang['error_exists'], $image_name);
					} else {
						$ident_file = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$this->resource->id.'.sanboot.'.$image_name.'.adapt';
						$get_ident_command  = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/bin/htvcenter-sanboot-storage adapt';
						$get_ident_command .= ' -n '.$image_name;
						$get_ident_command .= ' -t '.$this->deployment->type.' -v '.$this->volgroup;
						$get_ident_command    .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
						$get_ident_command .= ' --htvcenter-ui-user '.$this->user->name;
						$get_ident_command .= ' --htvcenter-cmd-mode background';
						if(file_exists($ident_file)) {
							unlink($ident_file);
						}
						$this->resource->send_command($this->resource->ip, $get_ident_command);
						while (!file_exists($ident_file)) {
							usleep(10000); // sleep 10ms to unload the CPU
							clearstatcache();
						}
						$found = false;
						// for sanboot-aoe deployment we need to get the shelf + slot from get_root_identifiert
						if ($this->deployment->type == 'aoe-san-deployment') {
							$name = $root_device;
							$root_device = '';
							$ident_lines = explode("\n", file_get_contents($ident_file));
							if(count($ident_lines) >= 1) {
								foreach($ident_lines as $ident_line) {
									if($ident_line !== '') {
										$ident_line = explode(',', $ident_line);
										$ident_root_path = explode(':', $ident_line[1]);
										$ident_check = $ident_root_path[1];
										if($name === $ident_check) {
											$root_device = $ident_line[1];
											$found = true;
											break;
										}
									}
								}
							}
						}
						if ($this->deployment->type == 'iscsi-san-deployment') {
							$found = true;
						}
						// add image
						if ($found) {
							$tables = $this->htvcenter->get('table');
							$image_fields = array();
							$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
							$image_fields['image_name'] = $image_name;
							$image_fields['image_type'] = $this->deployment->type;
							$image_fields['image_rootfstype'] = 'local';
							$image_fields['image_storageid'] = $this->storage->id;
							$image_fields['image_comment'] = "Image Object for volume $image_name";
							$image_fields['image_rootdevice'] = $root_device;
							$image = new image();
							$image->add($image_fields);
							$message[] = sprintf($this->lang['msg_added_image'], $image_name);
						} else {
							$message[] = sprintf($this->lang['msg_add_failed'], $image_name);
						}
					}
					break;

				case 'remove':
					$image_id = $this->response->html->request()->get('image_id');
					// check if image is not in use any more before removing
					$remove_error = 0;
					$appliance = new appliance();
					$appliance_id_list = $appliance->get_all_ids();
					foreach($appliance_id_list as $appliance_list) {
						$appliance_id = $appliance_list['appliance_id'];
						$app_image_remove_check = new appliance();
						$app_image_remove_check->get_instance_by_id($appliance_id);
						if ($app_image_remove_check->imageid == $image_id) {
							$image_is_used_by_appliance .= $appliance_id." ";
							$remove_error = 1;
						}
					}
					if ($remove_error == 1) {
						$errors[] = sprintf($this->lang['error_image_still_in_use'], $image_id, $image_is_used_by_appliance);
					} else {
						$image_remove = new image();
						$image_remove->remove($image_id);
						$message[] = sprintf($this->lang['msg_removed_image'], $image_id);
					}
					break;
			}
			if(count($errors) === 0) {
				$response = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response = join('<br>', $msg);
			}
		} else {
			$response = '';
		}
		return $response;
	}


}
?>
