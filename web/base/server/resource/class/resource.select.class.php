<?php
/**
 * Resource Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class resource_select
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
		$this->response = $response;
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;
		$this->response->add('resource_filter', $this->response->html->request()->get('resource_filter'));
		$this->response->add('resource_type_filter', $this->response->html->request()->get('resource_type_filter'));
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/resource-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();

		$h = array();
		$h['resource_state']['title'] = $this->lang['table_state'];
		$h['resource_state']['sortable'] = false;

		$h['resource_id']['title'] = $this->lang['table_id'];
		$h['resource_id']['hidden'] = true;

		$h['resource_hostname']['title'] = $this->lang['table_name'];
		$h['resource_hostname']['hidden'] = true;

		$h['resource_mac']['title'] = $this->lang['table_mac'];
		$h['resource_mac']['hidden'] = true;

		$h['resource_ip']['title'] = $this->lang['table_ip'];
		$h['resource_ip']['hidden'] = true;

		$h['resource_type']['title'] = $this->lang['table_type'];
		$h['resource_type']['sortable'] = false;
		$h['resource_type']['hidden'] = true;

		$h['resource_memtotal']['title'] = $this->lang['table_memory'];
		$h['resource_memtotal']['hidden'] = true;

		$h['resource_cpunumber']['title'] = $this->lang['table_cpu'];
		$h['resource_cpunumber']['hidden'] = true;

		$h['resource_nics']['title'] = $this->lang['table_nics'];
		$h['resource_nics']['hidden'] = true;

		$h['resource_load']['title'] = $this->lang['table_load'];
		$h['resource_load']['hidden'] = true;

		$h['data']['title'] = '&#160;';
		$h['data']['sortable'] = false;

		$h['hw']['title'] = '&#160;';
		$h['hw']['sortable'] = false;

		$resource = new resource();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

#$this->response->html->help($resource->find_resource('00:e0:53:13'));

		// unset unnecessary params
		unset($params['resource_type_filter']);
		unset($params['resource_filter']);
		unset($params['resource[sort]']);
		unset($params['resource[order]']);
		unset($params['resource[limit]']);
		unset($params['resource[offset]']);

		$table = $this->response->html->tablebuilder('resource', $params);
		$table->offset = 0;
		$table->sort = 'resource_id';
		$table->limit = 20;
		$table->order = 'ASC';
		$table->max = $resource->get_count('all');

		$table->init();

		// handle table params
		$tps = $table->get_params();
		$tp = '';
		foreach($tps['resource'] as $k => $v) {
			$tp .= '&resource['.$k.']='.$v;
		}

		$resource_filter = null;
		if( $this->response->html->request()->get('resource_filter') !== '') {
			$resource_filter = array();
			$ar = $resource->find_resource($this->response->html->request()->get('resource_filter'));
			if(count($ar) > 0) {
				foreach($ar as $k => $v) {
					$resource_filter[] = $v['resource_id'];
				}
			}
		}

		$resources = $resource->display_overview(0, 10000, $table->sort, $table->order);
		foreach ($resources as $index => $resource_db) {

			// prepare the values for the array
			$resource = new resource();
			$resource->get_instance_by_id($resource_db["resource_id"]);
			$res_id = $resource->id;

			if ($this->response->html->request()->get('resource_type_filter') === '' || ($this->response->html->request()->get('resource_type_filter') == $resource->vtype )) {

				// Skip all resources not in $resource_filter
				if(isset($resource_filter)) {
					if(!in_array($resource->id, $resource_filter)) {
						continue;
					}
				}

				$mem_total = $resource_db['resource_memtotal'];
				$mem_used = $resource_db['resource_memused'];
				$mem = "$mem_used/$mem_total";
				$swap_total = $resource_db['resource_swaptotal'];
				$swap_used = $resource_db['resource_swapused'];
				$swap = "$swap_used/$swap_total";
				$resource_mac = $resource_db["resource_mac"];

				// the resource_type
				$link = '';
				if ((strlen($resource->vtype)) && (!strstr($resource->vtype, "NULL"))){
					// find out what should be preselected
					$virtualization = new virtualization();
					$virtualization->get_instance_by_id($resource->vtype);
					$virtualization_plugin_name = $virtualization->get_plugin_name();
					$virtualization_vm_action_name = str_replace("-", "_", $virtualization_plugin_name);
					if ($virtualization->id == 1) {
						$resource_type = $virtualization->name;
					} else {
						$resource_type_link_text = $virtualization->name;
						if ($resource->id == $resource->vhostid) {
							// physical system or host
							$host_appliance = new appliance();
							$host_appliance->get_instance_by_virtualization_and_resource($virtualization->id, $resource->id);
							if (($virtualization->id > 0) && ($resource->id > 0)) {
								$link = '?plugin='.$virtualization_plugin_name.'&controller='.$virtualization_plugin_name.'-vm&'.$virtualization_vm_action_name.'_vm_action=edit&appliance_id='.$host_appliance->id;
								$resource_type_link_text = "<nobr>".$virtualization->name." Server ".$host_appliance->name."</nobr>";
							}
						} else {
							// vm
							$host_virtualization = new virtualization();
							$host_virtualization->get_instance_by_type($virtualization_plugin_name);
							$host_appliance = new appliance();
							if ($host_virtualization->id > 0) {
								$host_appliance->get_instance_by_virtualization_and_resource($host_virtualization->id, $resource->vhostid);
								$host_resource = new resource();
								$host_resource->get_instance_by_id($resource->vhostid);
								$link = '?plugin='.$virtualization_plugin_name.'&controller='.$virtualization_plugin_name.'-vm&'.$virtualization_vm_action_name.'_vm_action=edit&appliance_id='.$host_appliance->id;
								$resource_type_link_text = "<nobr>".$virtualization->name." on Res. ".$host_resource->hostname."</nobr>";
							}
						}
						$resource_type = $resource_type_link_text;
					}
				} else {
					$resource_type = "Unknown";
				}
				// htvcenter resource ?
				if ($resource->id == 0) {
					$resource_icon_default="/htvcenter/base/img/logo.png";
				} else {
					$resource_icon_default="/htvcenter/base/img/resource.png";
				}
				$state_icon = '<span class="pill '.$resource->state.'">'.$resource->state.'</span>';
				// idle ?
				if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
					$state_icon='<span class="pill idle">idle</span>';
				}
			
				$resource_cpus = $resource_db["resource_cpunumber"];
				if (!strlen($resource_cpus)) {
					$resource_cpus = '?';
				}
				$resource_nics = $resource_db["resource_nics"];
				if (!strlen($resource_nics)) {
					$resource_nics = '?';
				}
				isset($resource_db["resource_hostname"]) ? $name = $resource_db["resource_hostname"] : $name = '&#160;';
				isset($resource_db["resource_nics"]) ? $nics = $resource_db["resource_nics"] : $nics = '&#160;';
				isset($resource_db["resource_load"]) ? $load = $resource_db["resource_load"] : $load = '&#160;';

				// check for local VMs without an IP 
				$resip = $resource_db["resource_ip"];
				$resid = $resource_db["resource_id"];
				if ($resip == '0.0.0.0') {
					$state_icon = '<span class="pill transition">transition</span>';
					
				}
				
				$ip_validity = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $resip);
				
				if( $resip !== '0.0.0.0' && $ip_validity ){
					if (($virtualization->type == 'kvm-vm-local') || ($virtualization->type == 'vmware-esx-vm-local') || ($virtualization->type == 'OCH-vm-local')  || ($virtualization->type == 'citrix-vm-local') || ($virtualization->type == 'hyperv-vm-local') || ($virtualization->type == 'xen-vm-local')) {
						$a = $this->response->html->a();
						$a->title = $this->lang['action_edit'].' IP';
						$a->label = $resip;
						$a->css   = 'edit editippopupo';
						$a->href  = $this->response->get_url($this->actions_name, 'edit').'&resource_id='.$resid;
						$resip = $a->get_string();
				 	}
			 	}
				
				$tempResIp = explode(".", $resip);
				//echo count($tempResIp);

				$data = '<div class="panel-body">';
				
				$data  = '<b>'.$this->lang['table_id'].'</b>: '.$resource_db["resource_id"].'<br>';
				$data .= '<b>'.$this->lang['table_name'].'</b>: '.$name.'<br>';
				$data .= '<b>'.$this->lang['table_mac'].'</b>: '.$resource_mac.'<br>';
				
				if($resip !== '0.0.0.0' && $ip_validity){
					$data .= '<b>'.$this->lang['table_ip'].'</b>: '.$resip.'<br>';
				}
				
				$resource_type = str_replace('(localboot)', '', $resource_type);
				if (strlen($resource_type) > 36) {
					$resource_type = substr($resource_type, 0, 36);
					$resource_type = $resource_type.'...';
				}

				$resource_type = str_replace('KVM', 'OCH', $resource_type);


				$data .= '<b>'.$this->lang['table_type'].'</b>: '.$resource_type;
				$data.='</div>';
				$hw = '<div class="panel-body">';
				$hw  = '<b>'.$this->lang['table_cpu'].'</b>: '.$resource_cpus.'<br>';
				$hw .= '<b>'.$this->lang['table_memory'].'</b>: '.$mem.'<br>';
				$hw .= '<b>'.$this->lang['table_nics'].'</b>: '.$nics.'<br>';
				$hw .= '<b>'.$this->lang['table_load'].'</b>: '.$load;

				$hw .= '</div>';
				$b[] = array(
					'resource_state' => '<div class="appnamer panel-heading">
									<h3 class="panel-title">'.$name.'</h3>
						</div>'.$state_icon,
					'resource_id' => $resource_db["resource_id"],
					'resource_hostname' => $name,
					'resource_mac' => $resource_mac,
					'resource_ip' => $resip,
					'resource_type' => $resource_type,
					'resource_memtotal' => $mem,
					'resource_cpunumber' => $resource_cpus,
					'resource_nics' => $nics,
					'resource_load' => $load,
					'data' => $data,
					'hw' => $hw,
				);
			}
		}

		// Type Filter
		$virtulization_types = new virtualization();
		$list = $virtulization_types->get_list();
		$filter = array();
		$filter[] = array('', '');
		foreach( $list as $l) {
			$filter[] = array( $l['label'], $l['value']);
		}
		asort($filter);
		$select = $this->response->html->select();
		$select->add($filter, array(1,0));
		$select->name = 'resource_type_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('resource_type_filter'));
		$box1 = $this->response->html->box();
		$box1->add($select);
		$box1->id = 'resource_type_filter';
		$box1->css = 'htmlobject_box';
		$box1->label = $this->lang['lang_type_filter'];

		// Resource Filter
		$input = $this->response->html->input();
		$input->name = 'resource_filter';
		$input->value = $this->response->html->request()->get('resource_filter');
		$input->title = $this->lang['lang_filter_title'];
		$box2 = $this->response->html->box();
		$box2->add($input);
		$box2->id = 'resource_filter';
		$box2->css = 'htmlobject_box';
		$box2->label = $this->lang['lang_filter'];

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "add").''.$tp;

		$table->id = 'Tabellerr';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->head = $h;
		$table->body = $b;
		$table->max = count($b);
		$table->actions_name = $this->actions_name;
		$table->actions = array(
			array('reboot' => $this->lang['action_reboot']),
			array('poweroff' => $this->lang['action_poweroff']),
			array('remove' =>  $this->lang['action_remove'])
		);
		$table->identifier = 'resource_id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = array(0);
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['form']   = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add']    = $add->get_string();
		$d['resource_type_filter'] = $box1->get_string();
		$d['resource_filter'] = $box2->get_string();
		$d['table']  = $table;
		return $d;
	}

}
?>
