<?php
/**
 * vSphere Hosts VM Manager
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_id';
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
		$this->statfile			= $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_list';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_vsphere'], $this->response->html->request()->get('appliance_id'));
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

		if($this->virtualization->type === 'vmware-vsphere') {
			
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
			$host_icon="/htvcenter/base/plugins/vmware-vsphere/img/plugin.png";

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label   = $this->lang['action_add_local_vm'];
			$a->css     = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=vmware-vsphere-vm-local';
			$d['add_local_vm']   = $a->get_string();

			// only show network deployment VMs if dhcpd is enabled
			$plugin = $this->htvcenter->plugin();
			$enabled_plugins = $plugin->enabled();
			if (in_array("dhcpd", $enabled_plugins)) {
				$a = $this->response->html->a();
				$a->label   = $this->lang['action_add_network_vm'];
				$a->css     = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=vmware-vsphere-vm-net';
				$d['add_network_vm']   = $a->get_string();
			} else {
				$d['add_network_vm']   = '';
			}

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
							$line = $this->htvcenter->string_to_array($line, '|', '=');

							// first nic
							unset($first_mac);
							$first_nic_str = explode(',', $line['macAddress']);
							$first_mac = $first_nic_str[0];
							// additional nics
							$add_nic_loop = 1;
							$add_nic_str = '<small>';
							foreach($first_nic_str as $add_nic) {
								if ($add_nic_loop == 1) {
									continue;
								}
								if (strlen($add_nic)) {
									$add_nic_str .= '<nobr>'.$add_nic_loop.': '.$add_nic.'</nobr><br>';
									$add_nic_loop++;
								}
							}
							$add_nic_str .= '</small>';
							// state/id/ip
							$vm_resource = new resource();
							if (strlen($first_mac)) {
								$vm_resource->get_instance_by_mac($first_mac);
							}
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
							$update_button->href  = $this->response->get_url($this->actions_name, "update")."&vm_name=".$line['name']."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;
							
							// clone vm
							$clone_button = $this->response->html->a();
							$clone_button->label = $this->lang['action_clone'];
							$clone_button->title = $this->lang['action_clone'];
							$clone_button->css   = 'edit';
							$clone_button->handler = 'onclick="wait();"';
							$clone_button->href  = $this->response->get_url($this->actions_name, "clone")."&vm_name=".$line['name']."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;

							// vnc access
							$a_vnc = '';
							foreach($remote_functions as $function) {
								$a = $function($vm_resource->id, $line['vncport'] - 5900, $line['hostip']);
								if(is_object($a)) {
									$a_vnc .= $a->get_string();
								}
							}
							
							// idle or active ?
							$console = '';
							$a_update = '';
							$a_clone = '';
							$vm_state_icon = '';
							if ($virtualization->type == "vmware-vsphere-vm-local") {
								if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
									if  (!strcmp($line['powerState'], 'poweredOn')) {
										$vm_state_icon = '<span class="pill active">active</span>';
										$console = $a_vnc;
									} else {
										$vm_state_icon = '<span class="pill idle">idle</span>';
										$a_update = $update_button->get_string();
										$a_clone = $clone_button->get_string();
									}
								} else {
									if  (!strcmp($line['powerState'],  'poweredOn')) {
										$vm_state_icon = '<span class="pill active">active</span>';
										$console = $a_vnc;
									} else {
										$state = 'idle';
										$vm_state_icon = '<span class="pill '.$state.'">'.$state.'</span>';
										$a_update = $update_button->get_string();
										$a_clone = $clone_button->get_string();
									}
								}
							} 
							else if ($virtualization->type == "vmware-vsphere-vm-net") {
								if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
									if  (!strcmp($line['powerState'],  'poweredOn')) {
										$vm_state_icon = '<span class="pill idle">idle</span>';
										$console = $a_vnc;
									} else {
										$vm_state_icon = '<span class="pill off">off</span>';
									}
									$a_update = $update_button->get_string();
									$a_clone = $clone_button->get_string();
								} else {
									if  (!strcmp($line['powerState'],  'poweredOn')) {
										$vm_state_icon = '<span class="pill active">active</span>';
										$console = $a_vnc;
									} else {
										$vm_state_icon = '<span class="pill error">error</span>';
										$a_update = $update_button->get_string();
										$a_clone = $clone_button->get_string();
									}
								}
							}
							else if ($virtualization->type == "vmware-vsphere") {
								$identifier_disabled[] = $line['name'];
								if  (!strcmp($line['powerState'],  'poweredOn')) {
									$vm_state_icon = '<span class="pill active">active</span>';
								} else {
									$vm_state_icon = '<span class="pill off">off</span>';
								}
							} else {
								// VM not yet imported
								$vm_state_icon = '<span class="pill '.$line['powerState'].'">'.$line['powerState'].'</span>';
								if($line['powerState'] === 'poweredOff') {
									$imp = $this->response->html->a();
									$imp->label = $this->lang['action_import'];
									$imp->title = $this->lang['action_import'];
									$imp->css   = 'add';
									$imp->handler = 'onclick="wait();"';
									$imp->href  = $this->response->get_url($this->actions_name, "import")."&vm_name=".$line['name'];
									$a_update = $imp->get_string();
								}
							}
							
							$vm_state_img = $vm_state_icon;
							
							// format vnc info
							$vnc_info_str = '';
							if ((isset($line['hostip'])) && (isset($line['vncport']))) {
								$vnc_info_str = $line['hostip'].":".$line['vncport'];
							}
							// $vnc_info_str .= "Password: ".$line['vncpass']."<br></small>";

							// format network info
							#$network_info_str = "<small><nobr>NIC Type: ".$first_nic_type."</nobr><br>";
							$network_info_str  = "<nobr>MAC: ".$first_mac."</nobr><br>";
							$network_info_str .= "<nobr>IP: ".$vm_resource->ip."</nobr></small>";

							$a_boot_str = '';
							switch ($line['boot']) {
								case 'allow:net,hd,cd':
									$a_boot_str = 'Network';
									break;
								case 'allow:hd,cd,net':
									$a_boot_str = 'Local';
									break;
								case 'allow:cd,hd,net':
									$a_boot_str = 'CDROM/ISO';
									break;
								default:
									$a_boot_str = 'Local';
									break;
							}

							// filter datastore
							$first_vmdk_arr = explode(',', $line['fileName']);
							$ds_start_marker = strpos($first_vmdk_arr[0], '[');
							$ds_start_marker++;
							$ds_end_marker = strpos($first_vmdk_arr[0], ']');
							$vm_datastore = substr($first_vmdk_arr[0], $ds_start_marker, $ds_end_marker - $ds_start_marker);
							$vm_datastore = trim($vm_datastore);
							$vm_datastore_filename = substr($first_vmdk_arr[0], $ds_end_marker+1);
							$vm_datastore_filename = trim($vm_datastore_filename);
							$vm_datastore_link_content = '['.$vm_datastore.']'.$vm_datastore_filename;
							// link to ds manager
							$ds_link = '?plugin=vmware-vsphere&controller=vmware-vsphere-ds&appliance_id='.$this->appliance->id.'&vmware_vsphere_ds_action=volgroup&volgroup='.$vm_datastore;
							$a = $this->response->html->a();
							$a->title   = $vm_datastore_link_content;
							$a->label   = $vm_datastore_link_content;
							$a->handler = 'onclick="wait();"';
							$a->href  = $this->response->html->thisfile.$ds_link;
							$vm_datastore_link = $a->get_string();

							// count nics
							$nics = $add_nic_loop;
							$iso_str = '-';
							if (strlen($line['iso'])) {
								$iso_str = str_replace(" ", "", $line['iso']);
							}

							$hardware = '<b>'.$this->lang['table_cpu'].'</b>: '.$line['numCpu'].'<br>';
							$hardware .= '<b>'.$this->lang['table_ram'].'</b>: '.$line['memorySizeMB'].' MB<br>';
							$hardware .= '<b>'.$this->lang['table_nic'].'</b>: '.$line['numEthernetCards'].'<br>';
							$hardware .= '<b>'.$this->lang['table_disk'].'</b>: '.$vm_datastore_link.'<br>';
							$hardware .= '<b>'.$this->lang['table_iso'].'</b>: '.$iso_str.'<br>';
							$hardware .= '<b>'.$this->lang['table_ip'].'</b>: '.$vm_resource->ip.'<br>';
							$hardware .= '<b>'.$this->lang['table_mac'].'</b>: '.$first_mac.'<br>';
							if (($virtualization->type == "vmware-vsphere-vm-local") || ($virtualization->type == "vmware-vsphere-vm-net")) {
								$hardware .= '<b>'.$this->lang['table_vnc'].'</b>: '.$vnc_info_str.'<br>';
							}
							$hardware .= '<b>'.$this->lang['table_boot'].'</b>: '.$a_boot_str.'<br>';
							$hardware .= '<b>'.$this->lang['table_resource'].'</b>: '.$vm_resource->id.'<br>';
							$hardware .= '<b>'.$this->lang['table_vtype'].'</b>: '.$virtualization->type;

							$location  = '<nobr><b>'.$this->lang['table_datacenter'].'</b>: '.$line['datacenter'].'</nobr><br>';
							if ($line['cluster'] != $line['host']) {
								$location .= '<nobr><b>'.$this->lang['table_cluster'].'</b>: '.$line['cluster'].'</nobr><br>';
							}
							$location .= '<nobr><b>'.$this->lang['table_host'].'</b>: '.$line['host'].'</nobr><br>';
							$location .= '<nobr><b>'.$this->lang['table_resourcepool'].'</b>: '.$line['resourcepool'].'</nobr><br>';
							
							// relocate vm
							if (!strlen($vm_datastore)) {
								$vm_datastore = "0";
							}
							$relocate_button = $this->response->html->a();
							$relocate_button->label = $this->lang['action_relocate'];
							$relocate_button->title = $this->lang['action_relocate'];
							$relocate_button->css   = 'edit';
							$relocate_button->handler = 'onclick="wait();"';
							$relocate_button->href  = $this->response->get_url($this->actions_name, "relocate")."&vm_name=".$line['name']."&datastore=".$vm_datastore."&resourcepool=".$line['resourcepool'];
							$location .= $relocate_button->get_string();
							
							$actions_str = $console.$a_update.$a_clone;
							
							$body[] = array(
								'state' => $vm_state_img,
								'name'   => $line['name'],
								'datacenter'   => $line['datacenter'],
								'location'   => $location,
								'hardware' => $hardware,
								'actions' => $actions_str,
							);
						}
					}
				}
			}

			$h['state']['title'] = '&#160;';
			$h['state']['sortable'] = false;

			$h['name']['title'] = $this->lang['table_name'];
			$h['name']['hidden'] = false;

			$h['datacenter']['title'] = $this->lang['table_datacenter'];
			$h['datacenter']['hidden'] = true;
			
			$h['location']['title'] = $this->lang['table_location'];
			$h['location']['hidden'] = false;
			$h['location']['sortable'] = false;

			$h['hardware']['title'] = '&#160;';
			$h['hardware']['sortable'] = false;

			$h['actions']['title'] = ' ';
			$h['actions']['sortable'] = false;


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
