<?php
/**
 * Resource Add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class resource_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'resource_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'resource_identifier';
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
		$virtualization = new virtualization();
		$virtualization_list = array();
		$virtualization_list = $virtualization->get_list();
		$virtualization_link_section = "";
		// filter out the virtualization hosts
		foreach ($virtualization_list as $id => $virt) {
			$virtualization_id = $virt['value'];
			$available_virtualization = new virtualization();
			$available_virtualization->get_instance_by_id($virtualization_id);
			if (strstr($available_virtualization->type, "-vm")) {
				$virtualization_plugin_name = $available_virtualization->get_plugin_name();
				$virtualization_name = str_replace(" VM", "", $available_virtualization->name);

				

				$overver = 0;
				
				switch($virtualization_plugin_name) {
					case 'vmware-esx':
						$vnamee = 'VMware';
						$overver = 2;
						$iconz = '<i class="fa fa-clone mini '.$virtualization_plugin_name.'"></i>';
						break;

					case 'kvm':
						$iconz = '<i class="fa fa-keyboard-o mini"></i>';
						$vnamee = 'OCH';
						$overver = 1;
						break;
					case 'xen':
						$iconz = '<i class="fa fa-close mini"></i>';
						$vnamee = 'Xen';
						break;
					case 'hyperv':
						$vnamee = 'Hyper-V';
						$iconz = '<i class="fa fa-windows mini"></i>';
						break;
					case 'hybrid-cloud':
						$vnamee = 'Hybrid-Cloud';
						$iconz = '<i class="fa fa-cloud mini"></i>';
						break;
					case 'citrix':
						$vnamee = 'Citrix';
						$iconz = '<i class="fa fa-contao mini"></i>';
						break;

						
					default:
						$iconz = '<i class="fa fa-close mini '.$virtualization_plugin_name.'"></i>';
						break;
				}

				$nothis = 0;

				switch ($virtualization_plugin_name) {
					case 'kvm':
						$secondbtn = 1;
						if ($kvmyep == 1) {
							$nothis = 1;
						}
						$kvmyep = 1;
						break;

					case 'hyperv':
						$secondbtn = 1;
						if ($hypervyep == 1) {
							$nothis = 1;
						}
						$hypervyep = 1;
						break;

					case 'vmware-esx':
						$secondbtn = 1;
						if ($vmwareyep == 1) {
							$nothis = 1;
						}
						$vmwareyep = 1;
						break;
						

					case 'citrix':
						$secondbtn = 1;
						if ($citrixyep == 1) {
							$nothis = 1;
						}
						$citrixyep = 1;
						break;

					case 'xen':
						$secondbtn = 1;
						if ($xenyep == 1) {
							$nothis = 1;
						}
						$xenyep = 1;
						break;
					
					default:
						$secondbtn = 0;
						$nothis = 0;
						break;
				}

				if ($nothis == 0) {
				$overv = str_replace('(localboot)', '', $virtualization_name);
				if ($overver == 1) {
					$overv = 'OCH';
					$overver = 0;
				}

				if ($overver == 2) {
					$overv = 'VMware';
					$overver = 0;
				}
				$panel = '
				<div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
				<a href="'.$this->response->get_url($this->actions_name, 'load').'&rplugin='.$virtualization_plugin_name.'&rcontroller='.$virtualization_plugin_name."-vm".'
			"><div class="panel plan resourceplan">
										<div class="panel-body">
											<span class="plan-title">'.$vnamee.'</span>
											
											<div class="plan-icon">
												'.$iconz.'
											</div>
					
											<p class="text-muted pad-btm">
												'.$overv.' '.$this->lang['vm'].'
											</p>';
											
		$panel .= '</div></div></a></div>';


				$a = $this->response->html->a();
				$a->label = $panel;

				#$new_vm_link = "/htvcenter/base/index.php?plugin=".$virtualization_plugin_name."&controller=".$virtualization_plugin_name."-vm";
				#$virtualization_link_section .= "<a href='".$new_vm_link."' style='text-decoration: none'><img title='".sprintf($this->lang['create_vm'], $virtualization_plugin_name)."' alt='".sprintf($this->lang['create_vm'], $virtualization_plugin_name)."' src='/htvcenter/base/plugins/".$virtualization_plugin_name."/img/plugin.png' border=0> ".$virtualization_name." ".$this->lang['vm']."</a><br>";
				$virtualization_link_section .= $a->get_string();
				}

			}
		}
		if (!strlen($virtualization_link_section)) {
			$virtualization_link_section = $this->lang['start_vm_plugin'];
		}
		// local-server plugin enabled and started
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/htvcenter/base/plugins/local-server/.running")) {
			$a = $this->response->html->a();
			//$a->href = '';
			
			$panel = '
				<div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
				<div class="panel plan resourceplan">
										<div class="panel-body">
											<span class="plan-title">Integrate Local Server</span>
											
											<div class="plan-icon">
												<i class="fa fa-object-group mini"></i>
											</div>
					
											<p class="text-muted pad-btm">
												'.$this->lang['integrate_local_server'].'
											</p>
											<a href="'.$this->response->get_url($this->actions_name, 'load').'&rplugin=local-server&rcontroller=local-server-about&local_server_about_action=usage"><button class="btn btn-block btn-primary btn-lg">Choose</button></a>
										</div>
									</div>
				</div>';


			$a->label = $panel;
			$local_server_plugin_link = $a->get_string().'<br>';
			#$local_server_plugin_link = "<a href='/htvcenter/base/index.php?plugin=local-server&controller=local-server-about&local_server_about_action=usage' style='text-decoration: none'><img title='".$this->lang['integrate_local_server']."' alt='".$this->lang['integrate_local_server']."' src='/htvcenter/base/plugins/local-server/img/plugin.png' border=0> ".$this->lang['integrate_local_server']."</a>";
		} else {
			$local_server_plugin_link = $this->lang['start_local_server'];
		}


			$panel = '<div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
				<div class="panel plan resourceplan">
										<div class="panel-body">
											<span class="plan-title">Add an un-managed system </span>
											
											<div class="plan-icon">
												<i class="fa fa-server mini"></i>
											</div>
					
											<p class="text-muted pad-btm">
												manual add an un-managed system 
											</p>
											<button class="btn btn-block btn-primary btn-lg">Local Server</button>
										</div>
									</div>
				</div>';
		// manual add new resource
		$manual_new_resource_link = $panel;
		


		$t = $this->response->html->template($this->tpldir.'/resource-add.tpl.php');
		$t->add($virtualization_link_section, 'resource_virtual');
		$t->add($local_server_plugin_link, 'resource_local');
		$t->add($manual_new_resource_link, 'resource_new');
		$t->add($this->lang['title'], 'label');
		$t->add($this->lang['vm_type'], 'vm_type');
		$t->add($this->lang['local'], 'local');
		$t->add($this->lang['unmanaged'], 'unmanaged');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
