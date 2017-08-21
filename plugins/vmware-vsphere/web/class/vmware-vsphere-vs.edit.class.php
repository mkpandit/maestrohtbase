<?php
/**
 * vSphere Hosts Network Manager
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vs_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_vs_id';
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
		$host = $this->response->html->request()->get('esxhost');
		if($host === '') {
			return false;
		}
		
		// set ENV
		$virtualization = new virtualization();
		$appliance    = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource   = $resource;
		$this->appliance    = $appliance;
		$this->virtualization = $virtualization;
		$this->host = $host;
		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.net_config';
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
		$data = $this->ne();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vs-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $this->host), 'label');
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
	 * Network Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ne() {

		if($this->virtualization->type === 'vmware-vsphere') {
			$state_icon='<span class="pill active">active</span>';

			$d['state'] = $state_icon;
			$d['name'] = $this->host;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['add'] = $a->get_string();

			$body = array();
			$identifier_disabled = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							
							$ui_name = str_replace('@', ' ', $line['name']);
							
							if ($line['type'] == 'vs') {

								$resource_icon_default="/htvcenter/base/img/resource.png";
								$host_icon="/htvcenter/base/plugins/vmware-vsphere/img/plugin.png";
								if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$host_icon)) {
									$resource_icon_default=$host_icon;
								}
								$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";

								
								// format the uplink remove link
								$pnic_action = '';
								if (strlen($line['pnic'])) {
									$pnic_arr = explode(",", $line['pnic']);
									foreach ($pnic_arr as $pnic) {
										$a = $this->response->html->a();
										$a->label = $this->lang['action_remove_up'];
										$a->title = $this->lang['action_remove_up'];
										$a->css   = 'remove';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, "remove_up")."&vs_name=".$line['name']."&uplink=".$pnic;
										$pnic_action .= '<nobr>'.$pnic.' ';
										$pnic_action .= $a->get_string();
										$pnic_action .= '</nobr><br>';
									}
									
									
									
								}
								$a = $this->response->html->a();
								$a->label = $this->lang['action_add_up'];
								$a->title = $this->lang['action_add_up'];
								$a->css   = 'add';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, "add_up")."&vs_name=".$line['name']."&uplink=".$line['pnic'];
								$uplink_action = $a->get_string();

								// not removing uplink from vSwitch0
								if ($line['name'] === "vSwitch0") {
									$uplink_remove = '';
								}

								$a = $this->response->html->a();
								$a->label = $this->lang['action_update'];
								$a->title = $this->lang['action_update'];
								$a->css   = 'edit';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, "update")."&vs_name=".$line['name'];
								$update = $a->get_string();

								$body[] = array(
									'state' => $d['icon'],
									'vs_name'   => $ui_name,
									'vname'   => $line['name'],
									'pg_name'   => '',
									'num_ports' => $line['numPorts'],
									'numPortsAvailable' => $line['numPortsAvailable'],
									'mtu' => $line['mtu'],
									'uplink' => $pnic_action,
									'edit' => $update,
									'action' => $uplink_action,
								);
							
							} elseif ($line['type'] == 'pg') {

								$resource_icon_default="/htvcenter/base/img/kernel.png";
								$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
								$identifier_disabled[] = $line['vswitch'];
								
								$body[] = array(
									'state' => '',
									'vs_name'   => $line['vswitch'],
									'vname'   => $d['icon'],
									'pg_name'   => $ui_name,
									'num_ports' => '',
									'numPortsAvailable' => '',
									'mtu' => '',
									'uplink' => '',
									'edit' => '',
									'action' => '',
								);
							
							
							}
						}
					}
				}
			}

			$h['state']['title'] = '&#160;';
			$h['state']['sortable'] = false;
			$h['vs_name']['title'] = $this->lang['table_vs'];
			$h['vs_name']['hidden'] = true;
			$h['vname']['title'] = $this->lang['table_vs'];
			$h['pg_name']['title'] = $this->lang['table_pg'];
			$h['num_ports']['title'] = $this->lang['table_num_ports'];
			$h['numPortsAvailable']['title'] = $this->lang['table_available'];
			$h['mtu']['title'] = $this->lang['table_mtu'];
			$h['uplink']['title'] = $this->lang['table_uplink'];
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;


			$table = $this->response->html->tablebuilder('vmware_vs_list', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'name';
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
			$table->form_action     = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier_type = "checkbox";
			$table->identifier_disabled = $identifier_disabled;
			// not removing the last vswitch
			$max_vswitch = count($body);
			if ($max_vswitch > 1) {
				$table->identifier      = 'vs_name';
				$table->identifier_name = $this->identifier_name;
				$table->actions_name    = $this->actions_name;
				$table->actions         = array(array('remove' => $this->lang['action_remove']));
			}

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
