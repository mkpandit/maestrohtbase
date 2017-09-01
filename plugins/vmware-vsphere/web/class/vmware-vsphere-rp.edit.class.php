<?php
/**
 * vSphere ResourcePool Manager
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_rp_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_rp_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_rp_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_rp_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_rp_id';
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
		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.rp_list';
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
		$data = $this->rp();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-vs-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
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
	 * ResourcePool Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function rp() {

		if($this->virtualization->type === 'vmware-vsphere') {
			$state_icon='<span class="pill active">active</span>';

			$d['state'] = $state_icon;
			$d['name'] = $this->appliance->name;

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

							if ($line['name'] == 'Resources') {
								$identifier_disabled[] = $line['name'];
							}
							
							$ui_name = str_replace('@', ' ', $line['name']);
							
							$green_icon="/htvcenter/base/img/active.png";
							$orange_icon="/htvcenter/base/img/transition.png";
							$red_icon="/htvcenter/base/img/error.png";
							$rp_icon = $orange_icon;
							switch ($line[overallStatus]) {
								case 'green':
									$rp_icon = $green_icon;
									break;
								case 'orange':
									$rp_icon = $orange_icon;
									break;
								case 'red':
									$rp_icon = $red_icon;
									break;
							}
							$d['icon'] = "<img width=24 height=24 src=".$rp_icon.">";

							$cpusection = '<nobr><b>'.$this->lang['table_cpureservation'].'</b>: '.$line['cpureservation'].'</nobr><br>';
							$cpusection .= '<nobr><b>'.$this->lang['table_cpuexpandablereservation'].'</b>: '.$line['cpuexpandablereservation'].'</nobr><br>';
							$cpusection .= '<nobr><b>'.$this->lang['table_cpulimit'].'</b>: '.$line['cpulimit'].'</nobr><br>';
							$cpusection .= '<nobr><b>'.$this->lang['table_cpushares'].'</b>: '.$line['cpushares'].'</nobr><br>';
							$cpusection .= '<nobr><b>'.$this->lang['table_cpulevel'].'</b>: '.$line['cpulevel'].'</nobr><br>';
							$cpusection .= '<nobr><b>'.$this->lang['table_cpuoverallusage'].'</b>: '.$line['cpuoverallusage'].'</nobr><br>';
							$cpusection .= '<nobr><b>'.$this->lang['table_cpumaxusage'].'</b>: '.$line['cpumaxusage'].'</nobr><br>';
							
							
							$memorysection = '<nobr><b>'.$this->lang['table_memoryreservation'].'</b>: '.$line['memoryreservation'].'</nobr><br>';
							$memorysection .= '<nobr><b>'.$this->lang['table_memoryexpandablereservation'].'</b>: '.$line['memoryexpandablereservation'].'</nobr><br>';
							$memorysection .= '<nobr><b>'.$this->lang['table_memorylimit'].'</b>: '.$line['memorylimit'].'</nobr><br>';
							$memorysection .= '<nobr><b>'.$this->lang['table_memoryshares'].'</b>: '.$line['memoryshares'].'</nobr><br>';
							$memorysection .= '<nobr><b>'.$this->lang['table_memorylevel'].'</b>: '.$line['memorylevel'].'</nobr><br>';
							$memorysection .= '<nobr><b>'.$this->lang['table_memoryoverallusage'].'</b>: '.$line['memoryoverallusage'].'</nobr><br>';
							$memorysection .= '<nobr><b>'.$this->lang['table_memorymaxusage'].'</b>: '.$line['memorymaxusage'].'</nobr><br>';
							
							$vmsection = '';
							$vm_arr = explode(",", $line['vm']);
							foreach ($vm_arr as $vm) {
								if (!strlen($vm)) {
									continue;
								}
								$vm_name = str_replace('@', ' ', $vm);
								
								// relocate vm
								$relocate_button = $this->response->html->a();
								$relocate_button->label = $vm_name;
								$relocate_button->title = $this->lang['table_relocate'];
								$relocate_button->css   = 'edit';
								$relocate_button->handler = 'onclick="wait();"';
								$relocate_button->href  = "/htvcenter/base/index.php?plugin=vmware-vsphere&controller=vmware-vsphere-vm&appliance_id=".$this->appliance->id."&vmware_vsphere_vm_action=relocate&vm_name=".$vm."&datastore=0&resourcepool=".$line['name'];
								$location = $relocate_button->get_string();
								$vmsection .= '<nobr>'.$location.'</nobr><br>';
							}

							$a = $this->response->html->a();
							$a->label = $this->lang['action_add'];
							$a->css   = 'add';
							$a->handler = 'onclick="wait();"';
							$a->href  = $this->response->get_url($this->actions_name, "add")."&resourcepool=".$line['name'];
							$actions = $a->get_string();

							$a = $this->response->html->a();
							$a->label = $this->lang['action_update'];
							$a->title = $this->lang['action_update'];
							$a->css   = 'edit';
							$a->handler = 'onclick="wait();"';
							$a->href  = $this->response->get_url($this->actions_name, "update")."&resourcepool=".$line['name'];
							$actions .= $a->get_string();

							
							$body[] = array(
								'state' => $d['icon'],
								'rname'   => $line['name'],
								'rp_name'   => $ui_name,
								'parent'   => $line['parent'],
								'cpu'   => $cpusection,
								'memory' => $memorysection,
								'vm' => $vmsection,
								'action' => $actions,
							);
						}
					}
				}
			}

			$h['state']['title'] = '&#160;';
			$h['state']['sortable'] = false;
			$h['rname']['title'] = $this->lang['table_rp'];
			$h['rp_name']['title'] = '&#160;';
			$h['rp_name']['hidden'] = true;
			$h['parent']['title'] = $this->lang['table_parent'];
			$h['cpu']['title'] = $this->lang['table_cpu'];
			$h['memory']['title'] = $this->lang['table_memory'];
			$h['vm']['title'] = $this->lang['table_vm'];
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;


			$table = $this->response->html->tablebuilder('vmware_rp_list', $this->response->get_array($this->actions_name, 'edit'));
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
			$max_rp = count($body);
			if ($max_rp > 1) {
				$table->identifier      = 'rp_name';
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
