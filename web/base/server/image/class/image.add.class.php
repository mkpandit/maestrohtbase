<?php
/**
 * Image Add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class image_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'image_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "image_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'image_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'image_identifier';
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
		$id = $this->response->html->request()->get('appliance_id');
		if($id !== '') {
			$this->response->add('appliance_id', $id);
			$this->appliance = new appliance();
			$this->appliance->get_instance_by_id($id);
		}
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

		$deployment = new deployment();
		$deployment_id_array = $deployment->get_deployment_ids();
		$info = '<div class="infotext">'.$this->lang['info'].'</div>';

		$storage_link_section = '';
		if(isset($this->appliance)) {
			$resource = new resource();
			$resource->get_instance_by_id($this->appliance->resources);
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
			#$resourceinfo  = '<b>Server:</b> '.$this->appliance->id.' / '.$this->appliance->name.'<br>';
			$info = '<b>Resource:</b> '.$resource->id.' / '.$resource->ip.' '.$resource->hostname.' - '.$virtualization->name.'<br><br>'.$info;
			foreach($deployment_id_array as $id) {
				$new_image_link = '';
				$deployment->get_instance_by_id($id['deployment_id']);
				if (($deployment->storagetype != 'none') && ($deployment->storagetype != 'local-server')) {
					if (strstr($resource->capabilities, "TYPE=local-server")) {
						// disable - local-server already has an image and cannot be re-deployed with a differenet one
						continue;
					}
					else if (strstr($virtualization->type, "-vm-local")) {
						// get virt plugin name, check if deployment->storagetype == virt plugin name
						if ($deployment->storagetype === $virtualization->get_plugin_name()) {
							$new_image_link = $this->response->get_url($this->actions_name, 'load').'&iplugin='.$deployment->storagetype;

						}
					}
					else if (strstr($virtualization->type, "-vm-net")) {
						// find with image-deployment type hook if deployment is network-deployment
						$is_network_deployment = false;
						$rootdevice_identifier_hook = $this->htvcenter->get('basedir')."/web/boot-service/image.".$deployment->type.".php";
						if (file_exists($rootdevice_identifier_hook)) {
							require_once "$rootdevice_identifier_hook";
							$image_is_network_deployment_function="get_".$deployment->type."_is_network_deployment";
							$image_is_network_deployment_function=str_replace("-", "_", $image_is_network_deployment_function);
							if($image_is_network_deployment_function()) {
								$new_image_link = $this->response->get_url($this->actions_name, 'load').'&iplugin='.$deployment->storagetype;
							}
						}
					} else {
						// $new_image_link = "/htvcenter/base/index.php?plugin=".$deployment->storagetype;
						// same as vm-net
						// all network deployment types
					}
					if($new_image_link !== '') {
						$storage_link_section .= "<a href='".$new_image_link."' style='text-decoration: none'><img title='".sprintf($this->lang['create_image'], $deployment->description)."' alt='".sprintf($this->lang['create_image'], $deployment->description)."' src='/htvcenter/base/plugins/".$deployment->storagetype."/img/plugin.png' border=0> ".$deployment->description."</a><br>";
					}
				}
			}
		} else {
			foreach ($deployment_id_array as $deployment_id) {
				$deployment->get_instance_by_id($deployment_id['deployment_id']);
				if (($deployment->storagetype != 'none') && ($deployment->storagetype != 'local-server')) {
					#$new_image_link = "/htvcenter/base/index.php?plugin=".$deployment->storagetype;
					$new_image_link = $this->response->get_url($this->actions_name, 'load').'&iplugin='.$deployment->storagetype;
					switch ($deployment->storagetype) {
						case 'coraid-storage':
						case 'equallogic-storage':
						case 'netapp-storage':
							$new_image_link = "/htvcenter/base/index.php?iframe=/htvcenter/base/plugins/".$deployment->storagetype."/".$deployment->storagetype."-manager.php";
							break;
					}
					
					switch ($deployment->storagetype) {
						case 'linuxcoe':
							$icon = '<i class="fa fa-magic mini"></i> ';
						break;
						case 'lvm-storage':
							$icon = '<i class="fa fa-bolt mini"></i> ';
						break;
						case 'kvm':
							$icon = '<i class="fa fa-keyboard-o mini"></i> ';
						break;
						case 'nfs-storage':
							$icon = '<i class="fa fa-hdd-o mini"></i> ';
						break;
						case 'hybrid-cloud':
							$icon = '<i class="fa fa-cloud mini"></i> ';
						break;

						case 'vmware-esx':
							$icon = '<i class="fa fa-clone mini"></i> ';
						break;

						case 'hyperv':
							$icon = '<i class="fa fa-windows mini"></i> ';
						break;
						default:
							$icon = '<i class="fa fa-close mini"></i>' ;
						break;
					}
					//$storage_link_section .= "<a href='".$new_image_link."' style='text-decoration: none'> ". $icon.$deployment->description."</a><br>";
					
					if (!empty($virtualization->name)) {
						$name = $virtualization->name;
					} else {
						$name = $deployment->description;
						if ($name == 'Automatic Linux Installation (LinuxCOE)') {
							$name = 'LinuxCOE';
						}
					}

					if ($name == 'Blockfile deployment for KVM') {
						$name = 'HTFS Image';
						$query = "SELECT `storage_id` FROM `storage_info` WHERE `storage_resource_id` = 0 and `storage_name` = 'htvcenter-bf'";
						
							$res = mysql_query($query);
							
							while ($rez=mysql_fetch_array($res)) {
								$stid = $rez['storage_id'];
							}
						
						$new_image_link = 'index.php?base=image&iplugin=kvm&icontroller=kvm&image_action=load&kvm_action=edit&storage_id='.$stid;
					}

					if ($name == 'AMI deployment Cloud VMs') {
						$name = 'AMI Image';
					}

					if ($name == 'Local Deployment VMWare ESX') {
						$name = 'ESX Image';
					}

					if ($name == 'Local Deployment Hyper-V') {
						$name = 'Hyper-V Image';
					}

					
					if ( $deployment->description == 'Blockfile deployment for KVM') {
						$dedescription = 'HTFS Image for VMs';
					} else {
						$dedescription = $deployment->description;
					}

					if ( ($name != 'LVM deployment for KVM') && ($name != 'Glusterfs deployment for KVM') ) {
					$storage_link_section .= '
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
						<div class="panel plan resourceplan">
							<div class="panel-body">
											<span class="plan-title">'.$name.'</span>
											
											<div class="plan-icon">
												'.$icon.'
											</div>
					
											<p class="text-muted pad-btm">
												'.$dedescription.'
											</p>
											<a href="'.$new_image_link.'"><button class="btn btn-block btn-primary btn-lg">Add Image</button></a><br>
							</div>
						</div>
					</div>

					';
					}
				}
			}
		}

		if (!strlen($storage_link_section)) {
			$storage_link_section = $this->lang['start_storage_plugin'];
		}

		$t = $this->response->html->template($this->tpldir.'/image-add.tpl.php');
		$t->add($storage_link_section, 'image_new');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['title'], 'title');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($info, 'info');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
