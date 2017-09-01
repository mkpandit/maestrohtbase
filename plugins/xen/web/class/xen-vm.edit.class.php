<?php
/**
 * xen-vm Edit VM
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'xen_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'xen_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xen_vm_identifier';
/**
* identifier name
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
		$this->file                     = $htvcenter->file();
		$this->htvcenter                  = $htvcenter;
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$appliance  = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.vm_list';
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
		$data = $this->edit();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/xen-vm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_host'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {
		$resource_icon_default = "/img/resource.png";
		$storage_icon = "";
		$state_icon = '';
		if ($this->file->exists($this->htvcenter->get('webdir').$storage_icon)) {
			$resource_icon_default = $storage_icon;
		}
		$resource_icon_default = '';

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
		// prepare list of all Host resource id for the migration select
		// we need a select with the ids/ips from all resources which
		// are used by appliances with kvm capabilities
		$kvm_hosts = array();
		$appliance_list = new appliance();
		$appliance_list_array = $appliance_list->get_list();
		foreach ($appliance_list_array as $index => $app) {
			$appliance_kvm_host_check = new appliance();
			$appliance_kvm_host_check->get_instance_by_id($app["value"]);
			// only active appliances
			if ((!strcmp($appliance_kvm_host_check->state, "active")) || ($appliance_kvm_host_check->resources == 0)) {
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_kvm_host_check->virtualization);
				if ((!strcmp($virtualization->type, "xen")) && (!strstr($virtualization->type, "xen-vm"))) {
					$kvm_host_resource = new resource();
					$kvm_host_resource->get_instance_by_id($appliance_kvm_host_check->resources);
					// exclude source host
					#if ($kvm_host_resource->id == $this->resource->id) {
					#	continue;
					#}
					// only active appliances
					if (!strcmp($kvm_host_resource->state, "active")) {
						$migration_select_label = "Res. ".$kvm_host_resource->id."/".$kvm_host_resource->ip;
						$kvm_hosts[] = array("value"=>$kvm_host_resource->id, "label"=> $migration_select_label,);
					}
				}
			}
		}


				switch($resource->state) {
					case 'active':
						$icon = 'fa fa-long-arrow-right fabelle';
					break;
					case 'inactive':
						$icon = 'fa fa-close';
					break;
					default:
						$icon = 'fa fa-globe';
					break;
				}

		$d['state'] = '<i class="'.$icon.' fabelle"></i>';
		//$d['icon'] = '<img width="24" height="24" src="'.$this->htvcenter->get('baseurl').'/plugins/xen/img/plugin.png">';
		$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
		$d['name'] = $this->appliance->name;
		$d['id'] = $this->appliance->id;

		$a = $this->response->html->a();
		$a->label   = $this->lang['action_add_local_vm'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=xen-vm-local';
		$d['add_local_vm']   = $a->get_string();

		$a = $this->response->html->a();
		$a->label   = $this->lang['action_add_network_vm'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=xen-vm-net';
		$d['add_network_vm']   = $a->get_string();

		$body = array();
		$identifier_disabled = array();
		$file = $this->statfile;
		if($this->file->exists($file)) {				
			$lines = explode("\n", $this->file->get_contents($file));
			if(count($lines) >= 1) {
				$i = 0;
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						
						$state = $line[0];
						$name = $line[1];
						$mac = $line[2];

						$resource = new resource();
						$resource->get_instance_by_mac($mac);
						if ($resource->vhostid != $this->resource->id) {
							continue;
						}
						$res_virtualization = new virtualization();
						$res_virtualization->get_instance_by_id($resource->vtype);
						
						$update = '';
						if($state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_update'];
							$a->label   = $this->lang['action_update'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "update").'&vm='.$name.'&vmtype='.$res_virtualization->type;
							$update = $a->get_string();
						}
						
						$console = '';
						if($remote_console === true && $resource->imageid !== 1 && $state === '2') {
							$t = $this->response->html->template($this->htvcenter->get('webdir').'/js/htvcenter-progressbar.js');
							$identifier_disabled[] = $name;
							// progressbar
							$t->add(uniqid('b'), 'id');
							$t->add($this->htvcenter->get('baseurl').'/api.php?action=plugin&plugin=kvm&controller=kvm-vm&kvm_vm_action=progress&name='.$name.'.vm_migration_progress', 'url');
							$t->add($this->lang['action_migrate_in_progress'], 'lang_in_progress');
							$t->add($this->lang['action_migrate_finished'], 'lang_finished');
							$console = $t->get_string();
						} else {
							if($remote_console === true && $resource->imageid !== 1 && $state === '1') {
								foreach($remote_functions as $function) {
									$a = $function($resource->id);
									if(is_object($a)) {
										$console .= $a->get_string();
									}
								}
							}
						}

						$migrate = '';
						if(count($kvm_hosts) >= 1 && $state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_migrate'];
							$a->label   = $this->lang['action_migrate'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'migrate';
							$a->href    = $this->response->get_url($this->actions_name, "migrate").'&vm='.$name.'&mac='.$mac;
							$migrate    = $a->get_string();
						}

						$clone = '';
						if($state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_clone'];
							$a->label   = $this->lang['action_clone'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'clone';
							$a->href    = $this->response->get_url($this->actions_name, "clone").'&vm='.$name;
							$clone    = $a->get_string();
						}

						$network  = '<b>'.$this->lang['table_ip'].'</b>: '.$resource->ip.'<br>';
						$network .= '<b>'.$this->lang['table_mac'].'</b>: '.$mac.'<br>';
						$vm_vnc_port = $line[5];
						$vm_vnc_port = $vm_vnc_port + 5900;
						$network .= '<b>'.$this->lang['table_vnc'].'</b>: '.$vm_vnc_port;

						if($state !== '2') {
							$hardware  = '<b>'.$this->lang['table_cpu'].'</b>: '.$line[3].'<br>';
							$hardware .= '<b>'.$this->lang['table_ram'].'</b>: '.$line[4].'<br>';
							$hardware .= '<b>'.$this->lang['table_nics'].'</b>: '.$resource->nics;
						} else {
							$t = $this->response->html->template($this->htvcenter->get('webdir').'/js/htvcenter-progressbar.js');
							$identifier_disabled[] = $name;
							// progressbar
							$t->add(uniqid('b'), 'id');
							$t->add($this->htvcenter->get('baseurl').'/api.php?action=plugin&plugin=xen&controller=xen-vm&xen_vm_action=progress&name='.$name.'.vm_migration_progress', 'url');
							$t->add($this->lang['action_migrate_in_progress'], 'lang_in_progress');
							$t->add($this->lang['action_migrate_finished'], 'lang_finished');
							$hardware = $t->get_string();
						}

						$state = '<img src="'.$this->htvcenter->get('baseurl').'/img/idle.png">';
						if($line[0] === '1') {
							$state = '<img src="'.$this->htvcenter->get('baseurl').'/img/active.png">';
						}

						$body[$i] = array(
							'state' => $state,
							'icon' => $d['icon'],
							'name' => $name,
							'resource' => $resource->id,
							'id' => $resource->id,
							'mac' => $mac,
							'cpu' => $line[3],
							'ram' => $line[4],
							'ip' => $resource->ip,
							'vnc' => $vm_vnc_port,
							'network' => $network,
							'hardware' => $hardware,
							'update' => $update,
							'clone' => $clone,
						);
						if($remote_console === true) {
							$body[$i]['console'] = $console;
						}
						if(count($kvm_hosts) >= 1) {
							$body[$i]['migrate'] = $migrate;
						}
					}
					$i++;
				}
			}
		}

		$h['state']['title'] = '&#160;';
		$h['state']['sortable'] = false;
		$h['icon']['title'] = '&#160;';
		$h['icon']['sortable'] = false;
		$h['name']['title'] = $this->lang['table_name'];
		$h['resource']['title'] = 'Resource';
		$h['resource']['sortable'] = false;
		$h['id']['title'] = $this->lang['table_resource'];
		$h['id']['hidden'] = true;
		$h['ip']['title'] = $this->lang['table_ip'];
		$h['ip']['hidden'] = true;
		$h['mac']['title'] = $this->lang['table_mac'];
		$h['mac']['hidden'] = true;
		$h['vnc']['title'] = $this->lang['table_vnc'];
		$h['vnc']['hidden'] = true;
		$h['network']['title'] = $this->lang['table_network'];
		$h['network']['sortable'] = false;
		$h['hardware']['title'] = $this->lang['table_hardware'];
		$h['hardware']['sortable'] = false;
		$h['cpu']['title'] = $this->lang['table_cpu'];
		$h['cpu']['hidden'] = true;
		$h['ram']['title'] = $this->lang['table_ram'];
		$h['ram']['hidden'] = true;
		$h['nics']['title'] = $this->lang['table_nics'];
		$h['nics']['hidden'] = true;
		if($remote_console === true) {
			$h['console']['title'] = '&#160;';
			$h['console']['sortable'] = false;
		}
		if(count($kvm_hosts) >= 1) {
			$h['migrate']['title'] = '&#160;';
			$h['migrate']['sortable'] = false;
		}
		$h['clone']['title'] = '&#160;';
		$h['clone']['sortable'] = false;
		$h['update']['title'] = '&#160;';
		$h['update']['sortable'] = false;
	
		$table = $this->response->html->tablebuilder('xen_vm_edit', $this->response->get_array($this->actions_name, 'edit'));
		$table->sort            = 'name';
		$table->limit           = 10;
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
		$table->actions         = array(
				array('start' => $this->lang['action_start']),
				array('stop' => $this->lang['action_stop']),
				array('reboot' => $this->lang['action_reboot']),
				array('remove' => $this->lang['action_remove'])
			);

		$d['table'] = $table->get_string();
		return $d;
	}

}
?>
