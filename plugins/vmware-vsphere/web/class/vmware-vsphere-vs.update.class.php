<?php
/**
 * vSphere Hosts VSwitch Manager
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vs_update
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
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('vs_name', $this->response->html->request()->get('vs_name'));
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
		$this->host = $host;

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
		$data = $this->update();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vs-update.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $this->response->html->request()->get('vs_name')), 'label');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_vsphere'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect($this->response->get_url($this->actions_name, 'edit', $this->message_param, $msg));
		}
	}

	//--------------------------------------------
	/**
	 * VSwitch Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function update() {

		if($this->virtualization->type === 'vmware-vsphere') {
			$resource_icon_default="/htvcenter/base/img/resource.png";
			$host_icon="/htvcenter/base/plugins/vmware-vsphere/img/plugin.png";
			$state_icon='<span class="pill active">active</span>';

			$d['state'] = $state_icon;
			$d['name'] = $this->host;

			// get the vswitch name from the response
			#$vs_name = '';
			$vs_name  = $this->response->html->request()->get('vs_name');
			#foreach($vswitch_arr as $vs) {
			#	$vs_name = $vs;
			#	break;
			#}
			// build the link to add a new portgroup to the vswitch
			$a = $this->response->html->a();
			$a->label = $this->lang['action_add_pg'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_pg")."&vs_name=".$vs_name;
			$d['add_pg'] = $a->get_string();


			$d['add_up'] = '';
			// build the link to add an uplink to the vswitch
			#$a = $this->response->html->a();
			#$a->label = $this->lang['action_add_up'];
			#$a->handler = 'onclick="wait();"';
			#$a->css   = 'add';
			#$a->href  = $this->response->get_url($this->actions_name, "add_up")."&vs_name=".$vs_name;

			// not removing uplink from Portgroups on vSwitch0
			#if ($vs_name === "vSwitch0") {
			#	$d['add_up'] = '';
			#} else {
			#	$d['add_up'] = $a->get_string();
			#}

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							if ($line['type'] === 'pg') {
								if ($line['vswitch'] === $vs_name) {
									// build the link to add/remove an uplink to the portgroup
									// $line[5] = "yoho";
/*									if (strlen($line['name'])) {
										$a = $this->response->html->a();
										$a->label = $this->lang['action_remove_pg_up'];
										$a->title = $this->lang['action_remove_pg_up'];
										$a->css   = 'remove';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, "remove_up")."&vs_name=".$vs_name."&pg_name=".$line['name']."&uplink=".$line[5];
									} else {
										$a = $this->response->html->a();
										$a->label = $this->lang['action_add_pg_up'];
										$a->title = $this->lang['action_add_pg_up'];
										$a->css   = 'add';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, "add_up")."&vs_name=".$vs_name."&pg_name=".$line['name'];
									}
									$uplink_action = $a->get_string();
 * 
 */
									// not removing uplink from Portgroups on vSwitch0
									if ($vs_name === "vSwitch0") {
										$uplink_action = '';
									}

									$pgname = str_replace('@', ' ', $line['name']);
									$body[] = array(
										'state' => $d['icon'],
										'pg_name'   => $pgname,
										'pg_vlan' => '',
										'pg_ports' => $line['numPorts'],
										'uplink' => '',
									);
								}
							}
						}
					}
				}
			}

			$h['state']['title'] = $this->lang['table_state'];
			$h['state']['sortable'] = false;
			$h['pg_name']['title'] = $this->lang['table_pg_name'];
			$h['pg_vlan']['title'] = $this->lang['table_pg_vlan'];
			$h['pg_ports']['title'] = $this->lang['table_pg_ports'];
			$h['uplink']['title']    = '&#160;';
			$h['uplink']['sortable'] = false;

			$table = $this->response->html->tablebuilder('vmware_pg_list', $this->response->get_array($this->actions_name, 'update'));
			// keep the vs_name
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
			$table->identifier      = 'pg_name';
			$table->identifier_name = $this->identifier_name;
			$table->actions_name    = $this->actions_name;
			$table->identifier_type = "checkbox";
			$table->actions         = array(array('remove_pg' => $this->lang['action_remove']));

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
