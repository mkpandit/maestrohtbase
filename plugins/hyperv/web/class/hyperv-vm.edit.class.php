<?php
/**
 * Hyper-V Hosts VM Manager
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_id';
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
	function __construct($htvcenter, $response, $controller) {
		$this->controller = $controller;
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		if($appliance_id === '') {
			return false;
		}
		// set ENV
		$virtualization	= new virtualization();
		$appliance		= new appliance();
		$resource		= new resource();
		$htvcenter_server = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$htvcenter_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource			= $resource;
		$this->appliance		= $appliance;
		$this->virtualization	= $virtualization;
		$this->htvcenter_server	= $htvcenter_server;
		$this->statfile			= $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.short_vm_list';
		$this->vnc_web_base_port	= 6000;
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
		$this->init();
		$data = $this->vm();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/hyperv-vm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_hyperv'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm() {

		if($this->virtualization->type === 'hyperv') {
			
			// check if we have a plugin implementing the remote console
			$remote_console = false;
			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			foreach ($enabled_plugins as $index => $plugin_name) {
				//$plugin_remote_console_running = $this->htvcenter->get('webdir')."/plugins/".$plugin_name."/.running";
				$plugin_remote_console_hook = $this->htvcenter->get('webdir')."/plugins/".$plugin_name."/htvcenter-".$plugin_name."-remote-console-hook.php";
				if ($this->file->exists($plugin_remote_console_hook)) {
					require_once "$plugin_remote_console_hook";
					$link_function = str_replace("-", "_", "htvcenter_"."$plugin_name"."_remote_console");
					if(function_exists($link_function)) {
						$remote_functions[] = $link_function;
						$remote_console = true;
					}
				}
			}
			
			$resource_icon_default="/htvcenter/base/img/resource.png";
			$host_icon="/htvcenter/base/plugins/hyperv/img/plugin.png";

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label   = $this->lang['action_add_local_vm'];
			$a->css     = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=hyperv-vm-local';
			$d['add_local_vm']   = $a->get_string();

			// only show network deployment VMs if dhcpd is enabled
			/*
			$plugin = $this->htvcenter->plugin();
			$enabled_plugins = $plugin->enabled();
			if (in_array("dhcpd", $enabled_plugins)) {
				$a = $this->response->html->a();
				$a->label   = $this->lang['action_add_network_vm'];
				$a->css     = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=hyperv-vm-net';
				$d['add_network_vm']   = $a->get_string();
			} else {
				$d['add_network_vm']   = '';
			}
			*/
			$d['add_network_vm']   = '';

			$a = $this->response->html->a();
			$a->label   = $this->lang['action_import_existing_vms'];
			$a->css     = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, "import");
			$d['import_existing_vms']   = $a->get_string();

			$body = array();
			$file = $this->statfile;
			$identifier_disabled = array();
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->string_to_array(trim($line), '|', '=');
							// first nic
							unset($first_mac);
							$mac_arr = explode(',', $line['mac']);
							$first_mac = $this->controller->__string_to_mac($mac_arr[0]);
							
							// additional nics
							$add_nic_loop = 1;
							$add_nic_str = '<small>';
							foreach($mac_arr as $add_nic) {
								if (strlen($add_nic)) {
									$add_nic_str .= '<nobr>'.$add_nic_loop.': '.$add_nic.'</nobr><br>';
									$add_nic_loop++;
								}
							}
							$add_nic_loop--;
							$add_nic_str .= '</small>';
							
							
							
							// state/id/ip
							$new_resource = false;
							unset($vm_resource);
							unset($virtualization);
							$vm_resource = new resource();
							if ($vm_resource->exists($first_mac)) {
								$vm_resource->get_instance_by_mac($first_mac);
								$virtualization = new virtualization();
								if(isset($vm_resource->vtype) && $vm_resource->vtype !== '') {
									$virtualization->get_instance_by_id($vm_resource->vtype);
								}
							} else {
								$new_resource = true;
							}
							// update vm
							$update_button = $this->response->html->a();
							$update_button->label = $this->lang['action_update'];
							$update_button->title = $this->lang['action_update'];
							$update_button->css   = 'edit';
							$update_button->handler = 'onclick="wait();"';
							$update_button->href  = $this->response->get_url($this->actions_name, "update")."&vm_name=".$line['name']."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;
							
							// idle or active ?
							$console = '';
							$a_update = '';
							$vm_state_icon = '';
							$vtype =  '';
							if(isset($virtualization->type)) {
								if ($virtualization->type == "hyperv-vm-local") {
									if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
										if  ($vm_resource->state == 'active') {
											$vm_state_icon = '<span class="pill idle">idle</span>';
										} else {
											$vm_state_icon = '<span class="pill idle">idle</span>';
										}
									} else {
										if  ($vm_resource->state == 'active') {
											$vm_state_icon = '<span class="pill active">active</span>';
										} else {
											$vm_state_icon = '<span class="pill idle">idle</span>';
										}
									}
									$a_update = $update_button->get_string();
									$vtype =  'hyperv-vm-local';
								} else if ($virtualization->type == "hyperv-vm-net") {
									if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
										if  ($vm_resource->state == 'active') {
											$vm_state_icon = '<span class="pill idle">idle</span>';
										} else {
											$vm_state_icon = '<span class="pill off">off</span>';
										}
									} else {
										if  ($vm_resource->state == 'active') {
											$vm_state_icon = '<span class="pill idle">idle</span>';
										} else {
											$vm_state_icon = '<span class="pill error">error</span>';
										}
									}
									$a_update = $update_button->get_string();
									$vtype =  'hyperv-vm-net';
								}
							} else {
								// VM not yet imported
								/*
								if ($new_resource) {
									$vm_state_icon = '<span class="pill unaligned">unaligned</span>';
									$imp = $this->response->html->a();
									$imp->label = $this->lang['action_import'];
									$imp->title = $this->lang['action_import'];
									$imp->css   = 'add';
									$imp->handler = 'onclick="wait();"';
									$imp->href  = $this->response->get_url($this->actions_name, "import")."&vm_name=".$line['name'];
									$a_update = $imp->get_string();
								}
								*/
							}
							$vm_state_img = $vm_state_icon;

							// format network info
							$network_info_str = "<small><nobr>MAC: ".$first_mac."</nobr><br>";
							$network_info_str .= "<nobr>IP: ".$vm_resource->ip."</nobr></small>";

							
							$hardware  = '<b>'.$this->lang['table_nic'].'</b>: '.$add_nic_loop.'<br>';
							$network  = '<b>'.$this->lang['table_resource'].'</b>: '.$vm_resource->id.'<br>';
							$network .= '<b>'.$this->lang['table_name'].'</b>: '.$line['name'].'<br>';
							$network .= '<b>'.$this->lang['table_ip'].'</b>: '.$vm_resource->ip.'<br>';
							$network .= '<b>'.$this->lang['table_mac'].'</b>: '.$first_mac.'<br>';

							$network .= '<b>'.$this->lang['table_vtype'].'</b>: '.$vtype;

							$body[] = array(
								'state' => $vm_state_img,
								'name'   => trim($line['name']),
								'id' => $vm_resource->id,
								'ip' => $vm_resource->ip,
								'mac' => $first_mac,
								'network' => $network,
								'hardware' => $hardware,
								'vtype' => $vtype,
								'update' => $a_update,
							);
						}
					}
				}
			}

			$h['state']['title'] = '&#160;';
			$h['state']['sortable'] = false;

			$h['id']['title'] = $this->lang['table_resource'];
			$h['id']['hidden'] = true;

			$h['name']['title'] = $this->lang['table_name'];
			$h['name']['hidden'] = true;

			$h['ip']['title'] = $this->lang['table_ip'];
			$h['ip']['hidden'] = true;

			$h['mac']['title'] = $this->lang['table_mac'];
			$h['mac']['hidden'] = true;

			$h['vtype']['title'] = $this->lang['table_vtype'];
			$h['vtype']['hidden'] = true;

			$h['network']['title'] = '&#160;';
			$h['network']['sortable'] = false;

			$h['hardware']['title'] = $this->lang['table_hardware'];
			$h['hardware']['sortable'] = false;

			$h['update']['title'] = ' ';
			$h['update']['sortable'] = false;


			$table = $this->response->html->tablebuilder('hyperv_vm_list', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'id';
			$table->limit           = 20;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action	    = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'name';
			$table->identifier_name = $this->identifier_name;
			$table->identifier_disabled = $identifier_disabled;
			$table->actions_name    = $this->actions_name;
			$table->actions         = array(array('start' => $this->lang['action_start']), array('stop' => $this->lang['action_stop']), array('remove' => $this->lang['action_remove']));

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

function string_to_array($string, $element_delimiter = '|', $value_delimiter = '=') {
	$results = array();
	$array = explode($element_delimiter, $string);
	foreach ($array as $result) {
		$element = explode($value_delimiter, $result);
		if (isset($element[1])) {
			$results[$element[0]] = $element[1];
		}
	}
	return $results;
}

}
?>
