<?php
/**
 * ESX Hosts VM Manager
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_id';
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
		$this->statfile			= $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_esx'], $this->response->html->request()->get('appliance_id'));
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

		if($this->virtualization->type === 'vmware-esx') {
			
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
			$host_icon="/htvcenter/base/plugins/vmware-esx/img/plugin.png";

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label   = 'Add Virtual Machine';
			$a->css     = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=vmware-esx-vm-local';
			$d['add_local_vm']   = $a->get_string();

			// only show network deployment VMs if dhcpd is enabled
			$plugin = $this->htvcenter->plugin();
			$enabled_plugins = $plugin->enabled();
			if (in_array("dhcpd", $enabled_plugins)) {
				$a = $this->response->html->a();
				$a->label   = $this->lang['action_add_network_vm'];
				$a->css     = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=vmware-esx-vm-net';
				$d['add_network_vm']   = $a->get_string();
			} else {
				$d['add_network_vm']   = '';
			}

			#$a = $this->response->html->a();
			#$a->label   = $this->lang['action_import_existing_vms'];
			#$a->css     = 'add';
			#$a->handler = 'onclick="wait();"';
			#$a->href    = $this->response->get_url($this->actions_name, "import");
			#$d['import_existing_vms']   = $a->get_string();

			$body = array();
			$file = $this->statfile;
			$identifier_disabled = array();
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);

							// first nic
							unset($first_mac);
							$first_nic_str = explode(',', $line[4]);
							$first_mac = $first_nic_str[0];
							$first_nic_type = $first_nic_str[1];
							// additional nics
							$add_nic_loop = 1;
							$add_nic_str = '<small>';
							$add_nic_arr = explode('/', $line[5]);
							foreach($add_nic_arr as $add_nic) {
								if (strlen($add_nic)) {
									$add_one_nic = explode(',', $add_nic);
									$add_nic_str .= '<nobr>'.$add_nic_loop.': '.$add_one_nic[0].'/'.$add_one_nic[1].'</nobr><br>';
									$add_nic_loop++;
								}
							}
							$add_nic_str .= '</small>';
							// state/id/ip
							$vm_resource = new resource();
							$vm_resource->get_instance_by_mac($first_mac);
							$virtualization = new virtualization();
							if(isset($vm_resource->vtype) && $vm_resource->vtype !== '') {
								$virtualization->get_instance_by_id($vm_resource->vtype);
							}
							// update vm
							$update_button = $this->response->html->a();
							$update_button->label = $this->lang['action_update'];
							$update_button->title = $this->lang['action_update'];
							$update_button->css   = 'edit';
							$update_button->handler = 'onclick="wait();"';
							$update_button->href  = $this->response->get_url($this->actions_name, "update")."&vm_name=".$line[0]."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;
							// vnc access
							$a_vnc = '';
							foreach($remote_functions as $function) {
								$a = $function($vm_resource->id);
								if(is_object($a)) {
									$a_vnc .= $a->get_string();
								}
							}
							
							
							// idle or active ?
							$console = '';
							$a_update = '';
							$vm_state_icon = '';
							if ($virtualization->type == "vmware-esx-vm-local") {
								if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
									if  (!strcmp($line[1], 'active')) {
										$vm_state_icon = '<span class="pill idle">idle</span>';
									} else {
										$vm_state_icon = '<span class="pill idle">idle</span>';
									}
									$a_update = $update_button->get_string();
								} else {
									if  (!strcmp($line[1],  'active')) {
										$vm_state_icon = '<span class="pill active">active</span>';
										$console = $a_vnc;
									} else {
										$state = $line[1];
										if($line[1] === 'inactive') {
											$state = 'idle';
										}
										$vm_state_icon = '<span class="pill '.$state.'">'.$state.'</span>';
										$a_update = $update_button->get_string();
									}
								}
							} 
							else if ($virtualization->type == "vmware-esx-vm-net") {
								if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
									if  (!strcmp($line[1],  'active')) {
										$vm_state_icon = '<span class="pill idle">idle</span>';
										$console = $a_vnc;
									} else {
										$vm_state_icon = '<span class="pill off">off</span>';
									}
									$a_update = $update_button->get_string();
								} else {
									if  (!strcmp($line[1],  'active')) {
										$vm_state_icon = '<span class="pill active">active</span>';
										$console = $a_vnc;
									} else {
										$vm_state_icon = '<span class="pill error">error</span>';
										$a_update = $update_button->get_string();
									}
								}
							} else {
								// VM not yet imported
								$vm_state_icon = '<span class="pill '.$line[1].'">'.$line[1].'</span>';
								if(
									$line[1] === 'inactive' &&
									$line[8] !== '' &&
									($line[12] === 'local' || $line[12] === 'cdrom' ||  $line[12] === 'network')
								 ) {
									$imp = $this->response->html->a();
									$imp->label = $this->lang['action_import'];
									$imp->title = $this->lang['action_import'];
									$imp->css   = 'add';
									$imp->handler = 'onclick="wait();"';
									$imp->href  = $this->response->get_url($this->actions_name, "import")."&vm_name=".$line[0];
									$a_update = $imp->get_string();
								}
							}
							
							$vm_state_img = $vm_state_icon;
							// format vnc info
							$vnc_info_str = $this->resource->ip.":".$line[11];
							#$vnc_info_str .= "Password: ".$line[10]."<br></small>";

							// format network info
							$network_info_str = "<small><nobr>NIC Type: ".$first_nic_type."</nobr><br>";
							$network_info_str .= "<nobr>MAC: ".$first_mac."</nobr><br>";
							$network_info_str .= "<nobr>IP: ".$vm_resource->ip."</nobr></small>";

							$a_boot_str = '';
							switch ($line[12]) {
								case 'network':
									$a_boot_str = 'Network';
									break;
								case 'local':
									$a_boot_str = 'Local';
									break;
								case 'cdrom':
									$a_boot_str = 'CDROM/ISO';
									break;
							}

							// filter datastore
							$ds_start_marker = strpos($line[8], '[');
							$ds_start_marker++;
							$ds_end_marker = strpos($line[8], ']');
							$vm_datastore = substr($line[8], $ds_start_marker, $ds_end_marker - $ds_start_marker);
							$vm_datastore = trim($vm_datastore);
							$vm_datastore_filename = substr($line[8], $ds_end_marker+1);
							$vm_datastore_filename = trim($vm_datastore_filename);
							$vm_datastore_link_content = '['.$vm_datastore.']'.basename($vm_datastore_filename);
							// link to ds manager
							$ds_link = '?plugin=vmware-esx&controller=vmware-esx-ds&appliance_id='.$this->appliance->id.'&vmware_esx_ds_action=volgroup&volgroup='.$vm_datastore;
							$a = $this->response->html->a();
							$a->title   = $vm_datastore_link_content;
							$a->label   = $vm_datastore_link_content;
							$a->handler = 'onclick="wait();"';
							$a->href  = $this->response->html->thisfile.$ds_link;
							$vm_datastore_link = $a->get_string();

							// count nics
							$nics = 1;
							if(isset($line[5]) && $line[5] !== '') {
								$c = explode('/', $line[5]);
								// explode delivers one too many - keep in mind
								$nics = count($c);
							}

							$hardware  = '<b>'.$this->lang['table_cpu'].'</b>: '.$line[2].'<br>';
							$hardware .= '<b>'.$this->lang['table_ram'].'</b>: '.$line[3].' MB<br>';
							$hardware .= '<b>'.$this->lang['table_nic'].'</b>: '.$nics.'<br>';
							$hardware .= '<b>'.$this->lang['table_disk'].'</b>: '.$line[9].' MB<br>';
							$hardware .= '<b>'.$this->lang['table_boot'].'</b>: '.$a_boot_str;

							$network  = '<b>'.$this->lang['table_resource'].'</b>: '.$vm_resource->id.'<br>';
							$network .= '<b>'.$this->lang['table_name'].'</b>: '.$line[0].'<br>';
							$network .= '<b>'.$this->lang['table_ip'].'</b>: '.$vm_resource->ip.'<br>';
							$network .= '<b>'.$this->lang['table_mac'].'</b>: '.$first_mac.'<br>';
							$network .= '<b>'.$this->lang['table_vnc'].'</b>: '.$vnc_info_str.'<br>';
							$network .= '<b>'.$this->lang['table_datastore'].'</b>: '.$vm_datastore_link.'<br>';
							$network .= '<b>Guest ID</b>: '.$line[7].'<br>';
							$network .= '<b>'.$this->lang['table_vtype'].'</b>: '.$virtualization->type;

							$body[] = array(
								'state' => $vm_state_img,
								'name'   => $line[0],
								'id' => $vm_resource->id,
								'ip' => $vm_resource->ip,
								'mac' => $first_mac,
								'cpu' => $line[2],
								'mem' => $line[3],
								'network' => $network,
								'hardware' => $hardware,
								'boot' => $a_boot_str,
								'vtype' => $virtualization->type,
								'console' => $console,
								'vnc' => $vnc_info_str,
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

			$h['vnc']['title'] = $this->lang['table_vnc'];
			$h['vnc']['hidden'] = true;

			$h['vtype']['title'] = $this->lang['table_vtype'];
			$h['vtype']['hidden'] = true;

			$h['cpu']['title'] = $this->lang['table_cpu'];
			$h['cpu']['hidden'] = true;

			$h['mem']['title'] = $this->lang['table_ram'];
			$h['mem']['hidden'] = true;

			$h['boot']['title'] = $this->lang['table_boot'];
			$h['boot']['hidden'] = true;

			$h['network']['title'] = '&#160;';
			$h['network']['sortable'] = false;

			$h['hardware']['title'] = $this->lang['table_hardware'];
			$h['hardware']['sortable'] = false;

			$h['console']['title'] = ' ';
			$h['console']['sortable'] = false;

			$h['update']['title'] = ' ';
			$h['update']['sortable'] = false;


			$table = $this->response->html->tablebuilder('vmware_vm_list', $this->response->get_array($this->actions_name, 'edit'));
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


}
?>
