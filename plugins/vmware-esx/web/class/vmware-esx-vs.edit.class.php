<?php
/**
 * ESX Hosts Network Manager
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_vs_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_vs_id';
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
		$virtualization = new virtualization();
		$appliance    = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource   = $resource;
		$this->appliance    = $appliance;
		$this->virtualization = $virtualization;
		$this->statfile = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-esx-vs-edit.tpl.php');
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
	 * Network Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ne() {

		if($this->virtualization->type === 'vmware-esx') {
			$resource_icon_default="/htvcenter/base/img/resource.png";
			$host_icon="/htvcenter/base/plugins/vmware-esx/img/plugin.png";
			$state_icon='<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';

			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$host_icon)) {
				$resource_icon_default=$host_icon;
			}

			$d['state'] = $state_icon;
			$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['add'] = $a->get_string();

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if ($line[0] === 'vs') {
								// format the uplink remove link
								if (strlen($line[6])) {
									$a = $this->response->html->a();
									$a->label = $this->lang['action_remove_up'];
									$a->title = $this->lang['action_remove_up'];
									$a->css   = 'remove';
									$a->handler = 'onclick="wait();"';
									$a->href  = $this->response->get_url($this->actions_name, "remove_up")."&vs_name=".$line[1]."&uplink=".$line[6];
									$uplink_remove = $a->get_string();
								} else {
									$a = $this->response->html->a();
									$a->label = $this->lang['action_add_up'];
									$a->title = $this->lang['action_add_up'];
									$a->css   = 'add';
									$a->handler = 'onclick="wait();"';
									$a->href  = $this->response->get_url($this->actions_name, "add_up")."&vs_name=".$line[1]."&uplink=".$line[6];
									$uplink_remove = $a->get_string();
								}
								// not removing uplink from vSwitch0
								if ($line[1] === "vSwitch0") {
									$uplink_remove = '';
								}

								$a = $this->response->html->a();
								$a->label = $this->lang['action_update'];
								$a->title = $this->lang['action_update'];
								$a->css   = 'edit';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, "update")."&vs_name=".$line[1];
								$update = $a->get_string();

								$body[] = array(
									'state' => $d['icon'],
									'vs_name'   => $line[1],
									'num_ports' => $line[2],
									'used_ports' => $line[3],
									'conf_ports' => $line[4],
									'mtu' => $line[5],
									'uplink' => $line[6],
									'edit' => $update,
									'action' => $uplink_remove,
								);
							}
						}
					}
				}
			}

			$h['state']['title'] = $this->lang['table_state'];
			$h['state']['sortable'] = false;
			$h['vs_name']['title'] = $this->lang['table_name'];
			$h['num_ports']['title'] = $this->lang['table_num_ports'];
			$h['used_ports']['title'] = $this->lang['table_used_ports'];
			$h['conf_ports']['title'] = $this->lang['table_conf_ports'];
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
