<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);


/**
 * Datacenter Dashboard
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class datacenter_dashboard
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'datacenter_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "datacenter_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'datacenter_tab';
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
		$t = $this->response->html->template($this->tpldir.'/datacenter-dashboard.tpl.php');

		if ( isset($_GET['report']) && ($_GET['report'] == 'report_dashboard') ) {
			$t = $this->response->html->template($this->tpldir.'/../../../tpl/repport-dashboard-h.tpl.php');
		}

		if ( isset($_GET['report']) && ($_GET['report'] == 'report_explorer') ) {
			$t = $this->response->html->template($this->tpldir.'/../../../tpl/report-explorer-h.tpl.php');
		}

		if ( isset($_GET['report']) && ($_GET['report'] == 'report_bills') ) {
			$t = $this->response->html->template($this->tpldir.'/../../../tpl/report-bills-h.tpl.php');
		}

		if ( isset($_GET['report']) && ($_GET['report'] == 'report_inactive') ) {
			$t = $this->response->html->template($this->tpldir.'/../../../tpl/report-inactive-h.tpl.php');
		}
		
		$t->add($this->lang['title'], 'title');
		$t->add($this->lang['load_headline'], 'load_headline');
		$t->add($this->lang['load_current'], 'load_current');
		$t->add($this->lang['load_last_hour'], 'load_last_hour');
		$t->add($this->lang['datacenter_load_overall'], 'datacenter_load_overall');
		$t->add($this->lang['appliance_load_overall'], 'appliance_load_overall');
		$t->add($this->lang['storage_load_overall'], 'storage_load_overall');
		$t->add($this->lang['inventory_headline'], 'inventory_headline');
		$t->add($this->lang['inventory_servers'], 'lang_inventory_servers');
		$t->add($this->lang['inventory_storages'], 'lang_inventory_storages');
		$t->add($this->lang['events_headline'], 'events_headline');
		$t->add($this->lang['events_date'], 'events_date');
		$t->add($this->lang['events_source'], 'events_source');
		$t->add($this->lang['events_description'], 'events_description');
		$t->add($this->lang['no_data_available'], 'no_data_available');


		//storage data:
		$data = $this->storage();
		$t->add($data);
		// --- end storage data ---
		
		
		//devices data:
		$data2 = $this->devices();
		$t->add($data2);
		// --- end storage data ---
		
		/*//appliances data:
		$data3 = $this->applianceselect();
		$t->add($data3);
		// --- end appliance data---
		*/
		//kvmhosts data:
		$data4 = $this->kvmhostselect();
		$t->add($data4);
		// --- end kvmhosts data---


		//esxhosts data:
		$data5 = $this->esxhostselect();
		$t->add($data5);
		// --- end esxhosts data---


		//esx storagesummary data:
		$rezdata = $this->esxstoragesummary($data5['ids']);

		$data6 = $this->calculationesxsummary($rezdata);
		$t->add($data6);
		// --- end esx storagesummary data---

		//esx version info:

		$data7 = $this->esxiversion();
		$t->add($data7);
		// --- end esx version info ---

		//esx vm summary info:
		$data8 = $this->esxvmsummary($data5['ids']);
		$t->add($data8);
		// --- end esx vm summary info ---


		// events select:
		$data9 = $this->eventselect($rezdata);
		$t->add($data9);
		// --- end eventselect ---


		// todo select:
		$data = $this->todoselect();
		$t->add($data);
		// --- end todoselect ---

		$data =$this->vmmaincount();
		$t->add($data);

		$data = $this->storagetaken();
		$t->add($data);

		$year = date('Y');
				$yearm1 = $year - 1;
				$yearm2 = $year - 2;
				$yearm3 = $year - 3;
				$yearm4 = $year - 4;
				$yearm5 = $year - 5;
				$yearm6 = $year - 6;
				$yearz = '<option val="'.$year.'">'.$year.'</option>';
				$yearz .= '<option val="'.$yearm1.'">'.$yearm1.'</option>';
				$yearz .= '<option val="'.$yearm2.'">'.$yearm2.'</option>';
				$yearz .= '<option val="'.$yearm3.'">'.$yearm3.'</option>';
				$yearz .= '<option val="'.$yearm4.'">'.$yearm4.'</option>';
				$yearz .= '<option val="'.$yearm5.'">'.$yearm5.'</option>';
				$yearz .= '<option val="'.$yearm6.'">'.$yearm6.'</option>';
				
				$querysel = "SELECT `cu_name` FROM `cloud_users`";
				$ressel = mysql_query($querysel);

				if ( ($_GET['report'] == 'report_dashboard') || ( ($_GET['base'] == 'aa_server') && $_GET['controller'] == 'datacenter') || ($_GET['report'] == 'report_bills') ) {
					$rowsel = '<option val="all">All</option>';
				} else {
					$rowsel = '';
				}

				while ($rezz = mysql_fetch_assoc($ressel)) {
					$rowsel .= '<option val="'.$rezz['cu_name'].'">'.$rezz['cu_name'].'</option>';
				}
				
				$yeardef = date("Y");
				$monthdef = date("n");
				$monthdef = $monthdef - 1;
				
				$t->add($yearz, 'reportyear');
				$t->add($rowsel, 'hidenuser');
				$t->add($monthdef, 'monthdefault');
				$t->add($yeardef, 'yeardefault');



/*
		$t->add($this->lang['resource_overview'], 'resource_overview');
		$t->add($this->lang['resource_load_physical'], 'resource_load_physical');
		$t->add($this->lang['resource_load_vm'], 'resource_load_vm');
		$t->add($this->lang['resource_available_overall'], 'resource_available_overall');
		$t->add($this->lang['resource_available_physical'], 'resource_available_physical');
		$t->add($this->lang['resource_available_vm'], 'resource_available_vm');
		$t->add($this->lang['resource_error_overall'], 'resource_error_overall');
		$t->add($this->lang['appliance_overview'], 'appliance_overview');
		$t->add($this->lang['appliance_load_peak'], 'appliance_load_peak');
		$t->add($this->lang['appliance_error_overall'], 'appliance_error_overall');
		$t->add($this->lang['storage_overview'], 'storage_overview');
		$t->add($this->lang['storage_load_peak'], 'storage_load_peak');
		$t->add($this->lang['storage_error_overall'], 'storage_error_overall');
*/

		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		
		
		$link_server = $this->response->html->a();
		$link_server->label = $this->lang['link_server_management'];
		$link_server->title = 'Server Management';
		$link_server->href = 'index.php?base=appliance';
		$link_server->css = 'btn add';
		
		$t->add($link_server, 'link_server_management');

		
		$link_storage = $this->response->html->a();
		$link_storage->label = $this->lang['link_storage_management'];
		$link_storage->title = 'Server Management';
		$link_storage->href = 'index.php?base=storage';
		$link_storage->css = 'btn add';
		$t->add($link_storage, 'link_storage_management');

		
		// Get dashboard quicklink from hook files
		$quicklinks = $this->build_quicklinks();
		if(count($quicklinks) > 0) {
			$t->add('<h2>Quicklinks</h2>', 'quicklinks_headline');
			$t->add(implode('', $quicklinks), 'quicklinks');
		} else {
			
			// TODO: find nicer way to 'unset' view markers if not needed
			//		 perhaps htmlobjects can do the job
			$t->add('', 'quicklinks_headline');
			$t->add('', 'quicklinks');
		}
		
		
		return $t;
	}

	function applianceselect() {
		
		$appliance_tmp = new appliance();
		$ul = '<ul class="storage-list" id="appliance-l">';
		$table = $this->response->html->tablebuilder( 'ipmgmt_appliance', $this->response->get_array($this->actions_name, 'applianceselect'));
		$table->css         = 'htmlobject_table';
		$table->border      = 0;
		$table->id          = 'Tabelle1';
		$table->head        = $head;
		$table->sort        = 'appliance_id';
		$table->autosort    = false;
		$table->limit       = 10;
		$table->sort_link   = false;
		$table->form_action = $this->response->html->thisfile;
		$table->max         = $appliance_tmp->get_count();
		$table->init();

		$arBody = array();
		$appliance_array = $appliance_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);


		if(count($appliance_array) > 0) {
			
			foreach ($appliance_array as $index => $appliance_db) {
				$appliance = new appliance();
				$appliance->get_instance_by_id($appliance_db["appliance_id"]);
				$resource = new resource();
				$appliance_resources=$appliance_db["appliance_resources"];
				if ($appliance_resources >=0) {
					// an appliance with a pre-selected resource
					$resource->get_instance_by_id($appliance_resources);
					$appliance_resources_str = "$resource->id/$resource->ip";
				} else {
					// an appliance with resource auto-select enabled
					$appliance_resources_str = "auto-select";
				}

				// active or inactive
				$resource_icon_default="/htvcenter/base/img/resource.png";
				$active_state_icon="/htvcenter/base/img/active.png";
				$inactive_state_icon="/htvcenter/base/img/idle.png";
				if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
					$state_icon=$active_state_icon;
				} else {
					$state_icon=$inactive_state_icon;
				}

				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
				$image = new image();
				$image->get_instance_by_id($appliance_db["appliance_imageid"]);
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
				$appliance_virtualization_type=$virtualization->name;

				

				$arBody[] = array(
					'appliance_state' => "<img src=$state_icon>",
					'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
					'appliance_id' => $appliance_db["appliance_id"],
					'appliance_name' => $appliance_db["appliance_name"],
					'appliance_kernelid' => $kernel->name,
					'appliance_imageid' => $image->name,
					'appliance_resources' => "$appliance_resources_str",
					'appliance_virtualization' => $appliance_virtualization_type,
					'edit' => $configure,
				);

			}


		}
		foreach ($arBody as $b) {
			switch ($b['appliance_virtualization']) {
				case 'Cloud Host':
					$icon = '<i class="fa fa-cloud"></i> ';
				break;

				case 'KVM Host':
					$icon = '<i class="fa fa-keyboard-o"></i> ';
				break;

				default:
					unset($icon);
				break;
			}
			$ul .= '<li>'.$icon.'<strong>'.$b['appliance_name'].'</strong></li>';
		}
				
		$ul .= '</ul>';
		$rez = array();
		$rez['applianceslist'] = $ul;
		return $rez;
	}

function devices () {

	$res = array();
	$dev = $this->selectdevices();
	
	$res['devicelist'] = '<ul class="storage-list">';
	$physer = 0;
	$bridger = 0;
	foreach ($dev as $d) {
		if (!empty($d['ip'])) {
			$ip = $d['ip'];
		} else {
			$ip = 'localhost';
		}
		$res['devicelist'] .= '<li><strong>'.$d['device'].'</strong> ('.$d['type'].'): '.$ip.'</li>';

		if ($d['type'] == 'Physical') {
			$physer = $physer + 1;
		} else {
			$bridger = $bridger + 1;
		}
	}

	$allp = $bridger + $physer;
	$onep = $allp/100;
	$physicalpercent = $physer/$onep;
	$bridgepercent = $bridger/$onep;

	
	$res['devicelist'] .= '</ul>';
	$res['physicalpercent'] = $physicalpercent;
	$res['bridgepercent'] = $bridgepercent;
	$res['bridgecount'] = $bridger;
	$res['physcount'] = $physer;
	return $res;
}



function selectdevices() {
	$response = $this->get_response();

		$b = array();
		$identifier_disabled = array();
		//$this->controller->__reload( $this->statfile, $this->resource );
	
		$statfile = $this->htvcenter->get('basedir').'/plugins/network-manager/web/storage/0.network_config';
		if ($this->file->exists($statfile)) {
			$result = trim($this->file->get_contents($statfile));
			$result = explode("\n", $result);
			if(is_array($result)) {
				$res = array();
				foreach($result as $v) {
					$res[] = explode('@', $v);
				}
				foreach ($res as $line) {
					$up = '';
					if($line[0] === 'n') {
						$identifier_disabled[] = $line[1];
						$type = 'Physical';
					}
					elseif($line[0] === 'b') {
						$type = 'Bridge';
						if(isset($line[4]) && $line[4] !== '') {
							$up = $line[4];
						}
					}

					$ip = '';
					if($line[3] !== '') {
						$tmp = explode('/', $line[3]);
						$ip = $tmp[0];
					}

					$b[] = array(
						'device' => $line[1],
						'type' => $type,
						'mac' => $line[2],
						'ip' => $ip,
						'up' => $up,
					);

					
				}
			}
			return $b;
		} else {
			return false;
		}

		

}

function get_response($mode = '') {

		$response = $this->response;
		#$form     = $response->get_form($this->actions_name, 'select');
		#$response->form = $form;

		return $response;
	}

function controllerinfo() {
	return $this->storage(true);
}

function storage($controller=false) {

$d = array();

		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		require_once($RootDir.'class/storage.class.php');
		$storage = new storage();
		

		$params  = $this->response->get_array($this->actions_name, 'select');

		unset($params['storage_filter']);

		$table = $this->response->html->tablebuilder('storage', $params);

		$table->offset = 0;
		$table->sort = 'storage_id';
		$table->limit = 999;
		$table->order = 'ASC';
		$table->max = $storage->get_count();
		$table->init();

		$storages = $storage->display_overview(0, 10000, $table->sort, $table->order);

	
		$num = 0;
		$storage_list = '<ul class="storage-list">';
		
		foreach ($storages as $key => $value) {

			$storage = new storage();

			$storage->get_instance_by_id($value["storage_id"]);
			
			$resource = new resource();
			$resource->get_instance_by_id($storage->resource_id);
			
			$deployment = new deployment();
			$deployment->get_instance_by_id($storage->type);
			
			$cpumodel = str_replace('QEMU', 'OCH', $resource->cpumodel);

			$d['cpu'] = $cpumodel;
 			$d['memtotal'] = $resource->memtotal;
 			$d['memused'] = $resource->memused;
 			$d['swaptotal'] = $resource->swaptotal;
 			$d['swapused'] = $resource->swapused;

 			$mem = $d['memused']/($d['memtotal']/100);
 			$d['mempercent'] = round($mem);

 			$mem = $d['swapused']/($d['swaptotal']/100);
 			$d['swappercent'] = round($mem);

 			$d['ip'] = $resource->ip;
 			$d['mac'] = $resource->mac;
 			$d['hostname'] = $resource->hostname;
			
			switch ($deployment->storagetype) {
				case 'hybrid-cloud':
					$icon = '<i class="fa fa-cloud"></i> ';
				break;

				case 'linuxcoe':
					$icon = '<i class="fa fa-magic"></i> ';
				break;

				case 'local-server':
					$icon = '<i class="fa fa-server"></i> ';
				break;

				case 'kvm':
					$icon = '<i class="fa fa-keyboard-o"></i> ';
				break;

				default:
					unset($icon);
				break;
			}
			$storage_list .= '<li>'.$icon.' '.$value['storage_name'].'</li>';
			//$res =$this->get_storage_table($deployment->storagetype);
			$num++;
		}
		$storage_list .= '</ul>';

		$resource = new resource();
		$resource->get_instance_by_id(0);
			
			$deployment = new deployment();
			$deployment->get_instance_by_id($storage->type);
			
			$cpumodel = str_replace('QEMU', 'OCH', $resource->cpumodel);
			
			$d['cpu'] = $cpumodel;
 			$d['memtotal'] = $resource->memtotal;
 			$d['memused'] = $resource->memused;
 			$d['swaptotal'] = $resource->swaptotal;
 			$d['swapused'] = $resource->swapused;


 			$continfo['memory']['available'] = $d['memtotal'] - $d['memused'] ;
 			$continfo['memory']['used'] = $d['memused'];
 			$continfo['swap']['available'] = $d['swaptotal'] - $d['swapused'];
 			$continfo['swap']['used'] = $d['swapused'];

 			$mem = $d['memused']/($d['memtotal']/100);
 			$d['mempercent'] = round($mem);

 			$mem = $d['swapused']/($d['swaptotal']/100);
 			$d['swappercent'] = round($mem);

 			$d['ip'] = $resource->ip;
 			$d['mac'] = $resource->mac;
 			$d['hostname'] = $resource->hostname;

		$d['num'] = $num;
		$d['storage_list'] = $storage_list;


		$size = disk_total_space("/");
		$free = disk_free_space ("/");
		$used = $size - $free;
		$hddpercent = $used/($size/100);
		$hddpercent = round($hddpercent);
		$size = $this->getSymbolByQuantity($size);
		$free = $this->getSymbolByQuantity($free);
		$used = $this->getSymbolByQuantity($used);
		
		$d['size'] = $size;
		$d['free'] = $free;
		$d['used'] = $used;
		$d['hddpercent'] = $hddpercent;

		$continfo['storage']['available'] = $d['free'];
		$continfo['storage']['used'] = $d['used'];

		$continfo['conditions'] = 0;

		if ( ($d['hddpercent'] > 70) || ($d['mempercent'] > 70) || ($d['swappercent'] > 70) ) {
			$continfo['conditions'] = 1;
		}

		if ($controller == false) {
			return $d;
		} else {
			return $continfo;
		}

}




function getSymbolByQuantity($bytes) {
    $symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $exp = $bytes ? floor(log($bytes) / log(1024)) : 0;

    return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}




	//--------------------------------------------
	/**
	 * build_quicklinks: Scan directories of started plugins for the 
	 * dashboard quicklink hook and call hook method
	 *
	 * @access private
	 * @return array
	 */
	//--------------------------------------------
	private function build_quicklinks() {
	
		$quicklinks = array();
		$plugin = new plugin();
		$started_plugins = $plugin->started();			// get list of running plugins
		
		foreach ($started_plugins as $plugin_name) {
			
			$hook_file = $this->htvcenter->get('webdir')."/plugins/".$plugin_name."/htvcenter-".$plugin_name."-dashboard-quicklink-hook.php";
			if (file_exists($hook_file)) {
				require_once $hook_file;
				
				$hook_function = 'get_'.$plugin_name.'_dashboard_quicklink';
				if(function_exists($hook_function)) {

					$link = $hook_function($this->response->html);
					if(is_object($link)) {
						$quicklinks[] = $link->get_string();
					}
				}
			}
		}

		return $quicklinks;
	}


	function kvmhostselect( $hidden = true ) {
		// set ENV
		$resource   = $this->htvcenter->resource();
		$virtualization = $this->htvcenter->virtualization();
		$virtualization->get_instance_by_type("kvm");
		$appliance = $this->htvcenter->appliance();
		$storage = $this->htvcenter->storage();
		$deployment = $this->htvcenter->deployment();

		$table = $this->response->html->tablebuilder('kvm_vm', $this->response->get_array($this->actions_name, 'select'));
		$table->sort      = 'appliance_id';
		$table->limit     = 10;
		$table->offset    = 0;
		$table->order     = 'ASC';
		$table->max       = $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort  = false;
		$table->sort_link = false;
		$table->init();

		// handle tab in tab
		if($this->response->html->request()->get('iplugin') !== '') {
			$strControler = 'icontroller';
		}
		else if($this->response->html->request()->get('rplugin') !== '') {
			$strControler = 'rcontroller';
		}
		else if($this->response->html->request()->get('aplugin') !== '') {
			$strControler = 'acontroller';
		} else {
			$strControler = 'controller';
		}

		// storages
		$deployment->get_instance_by_type('kvm');
		$storages = $storage->display_overview(0, 10000, 'storage_id', 'ASC');
		$s = array();
		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				if($deployment->storagetype === 'kvm') {
					// replace id by string 
					$v['storage_type'] = $deployment->type;
					$s[] = $v;
				}
			}
		}

		$servers = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);

		if(count($servers) >= 1) {
			foreach($servers as $k => $v) {
				$resource->get_instance_by_id($v["appliance_resources"]);
				$state_icon = '<span class="pill '.$resource->state.'">'.$resource->state.'</span>';

				$a = $this->response->html->a();
				$a->title   = $this->lang['title_vms'];
				$a->label   = 'VMS';
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($strControler, "kvm-vm").'&kvm_vm_action=edit&appliance_id='.$v['appliance_id'];
				$links = $a->get_string();

				// handle storages
				$slinks = '';
				foreach($s as $storage) {
					if($storage['storage_resource_id'] === $resource->id) {
						if($storage['storage_type'] === 'kvm-lvm-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_lvm'];
							$a->label   = 'LVM';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
						else if($storage['storage_type'] === 'kvm-bf-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_bf'];
							$a->label   = 'Blockfiles';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
						else if($storage['storage_type'] === 'kvm-gluster-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_glusterfs'];
							$a->label   = 'GlusterFS';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
					}
				}
				// handle missing storage
				if($slinks === '') {
					$a = $this->response->html->a();
					$a->title   = $this->lang['new_storage'];
					$a->label   = $this->lang['new_storage'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'add';
					$a->href    = $this->response->html->thisfile.'?base=storage&storage_action=add';
					$links .= $a->get_string();
				} else {
					$links .= $slinks;
				}

				// Network Manager
				$n = $this->response->html->a();
				$n->label   = $this->lang['network_manager'];
				$n->css     = 'enable';
				$n->handler = 'onclick="wait();"';
				$n->href    = $this->response->html->thisfile.'?plugin=network-manager&appliance_id='.$v['appliance_id'];
				$plugins = $n->get_string();
				// Sysinfo
				$n = $this->response->html->a();
				$n->label   = 'Sysinfo';
				$n->css     = 'enable';
				$n->handler = 'onclick="wait();"';
				$n->href    = $this->response->html->thisfile.'?plugin=kvm&controller=kvm-vm&kvm_vm_action=sysinfo&appliance_id='.$v['appliance_id'];
				$plugins .= $n->get_string();

				$data  = '<b>'.$this->lang['table_id'].'</b>: '.$v['appliance_id'].'<br>';
				$data .= '<b>'.$this->lang['table_name'].'</b>: '.$v['appliance_name'].'<br>';
				$data .= '<b>'.$this->lang['table_recource'].'</b>: '.$resource->hostname.'<br>';
				$data .= '<b>IP</b>: '.$resource->ip;

				$b[] = array(
					'state' => $state_icon,
					'appliance_id' => $v['appliance_id'],
					'name' => $v['appliance_name'],
					'appliance_resources' => $resource->id,
					'data' => $data,
					'comment' => $v['appliance_comment'].'<hr>'.$plugins,
					'action' => $links,
				);

				
			}
		}


		$hosts['kvmhosts'] = count($b);
		return $hosts; 
	}



	function esxhostselect() {

		$virtualization = new virtualization();
		$virtualization->get_instance_by_type('vmware-esx');
		$appliance = new appliance();

		$head['appliance_icon']['title'] = " ";
		$head['appliance_icon']['sortable'] = false;
		$head['appliance_id']['title'] = $this->lang['table_id'];
		$head['appliance_name']['title'] = $this->lang['table_name'];
		$head['appliance_comment']['title'] = $this->lang['table_comment'];
		$head['appliance_comment']['sortable'] = false;
		$head['appliance_action']['title'] = " ";
		$head['appliance_action']['sortable'] = false;

		$table = $this->response->html->tablebuilder('vmware_vm_select', $this->response->get_array($this->actions_name, 'select'));
		$table->sort            = 'appliance_id';
		$table->limit           = 10;
		$table->offset          = 0;
		$table->order           = 'ASC';
		$table->max		= $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort        = false;
		$table->sort_link       = false;
		$table->init();

		// handle tab in tab
		if($this->response->html->request()->get('iplugin') !== '') {
			$strControler = 'icontroller';
		}
		else if($this->response->html->request()->get('rplugin') !== '') {
			$strControler = 'rcontroller';
		}
		else if($this->response->html->request()->get('aplugin') !== '') {
			$strControler = 'acontroller';
		} else {
			$strControler = 'controller';
		}

		$vmware_esx_array = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($vmware_esx_array as $index => $esx) {
			$esx_appliance_id = $esx["appliance_id"];

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'VMS';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-esx-vm").'&vmware_esx_vm_action=edit&appliance_id='.$esx_appliance_id;
			$links = $a->get_string();

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'Datastore';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-esx-ds").'&vmware_esx_ds_action=edit&appliance_id='.$esx_appliance_id;
			$links .= $a->get_string();

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = 'Network';
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($strControler, "vmware-esx-vs").'&vmware_esx_vs_action=edit&appliance_id='.$esx_appliance_id;
			$links .= $a->get_string();

			$ta[] = array(
				'appliance_icon' => '<span class="pill active">active</span>',
				'appliance_id' => $esx["appliance_id"],
				'appliance_name' => $esx["appliance_name"],
				'appliance_comment' => $esx["appliance_comment"],
				'appliance_action' => $links,
			);
		}
       
       		$hosts['esxhosts'] = count($ta);
			$hosts['ids'] = $ta;
			return $hosts;
	}


function esxstoragesummary($ids) {

	$rezult = array();
	$i = 0;
	foreach ($ids as $esx) {


		$appliance_id = $esx['appliance_id'];
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
		$this->statfile = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';

	

	
	if($this->virtualization->type === 'vmware-esx') {

			$d['state'] = $this->resource->state;
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_nas'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_nas");
			$d['ds_add_nas'] = $a->get_string();

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_iscsi'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_iscsi");
			$d['ds_add_iscsi'] = $a->get_string();
			
			$body = array();

			$file = $this->htvcenter->get('webdir').'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';

			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));

				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							// prepare remove link
							$actions = '';
							$remove_action = '';
							$remove_action_link = '';
							$a = $this->response->html->a();
							if (!strcmp($line[2], "NFS"))  {
								$a->label = $this->lang['action_ds_remove'];
								$a->title = $this->lang['action_ds_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove_nas')."&volgroup=".$line[0];
								$actions .= $a->get_string();
							}
							if (!strcmp($line[2], "VMFS"))  {
								$a->label = $this->lang['action_ds_remove'];
								$a->title = $this->lang['action_ds_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove_iscsi')."&volgroup=".$line[0];
								$actions .= $a->get_string();
							}

							$l = $this->response->html->a();
							$l->label = $this->lang['action_edit'];
							$l->title = $this->lang['action_edit'];
							$l->css   = 'edit';
							$l->handler = 'onclick="wait();"';
							$l->href  = $this->response->get_url($this->actions_name, 'volgroup')."&volgroup=".$line[0];

							$actions .= $l->get_string();

							// format capacity + available
							$capacity = number_format($line[3], 2, '.', '');
							$available = number_format($line[4], 2, '.', '');

							// fill body
							$body[] = array(
								'name'   => $line[0],
								'location' => "<nobr>".$line[1]."</nobr>",
								'filesystem' => $line[2],
								'capacity' => $capacity." GB",
								'available' => $available." GB",
								'action' => $actions
							);


							$rezult[$i]['hostname'] = $esx['appliance_name'];
							$rezult[$i]['capacity'] = $capacity;
							$rezult[$i]['storagename'] = $line[0];
							$rezult[$i]['available'] = $available;
							$rezult[$i]['appliance'] = $esx['appliance_id'];
							$i++;
			
						}
					}
				}
			}

		} 
	

	}
	
	
	return $rezult;
}

function calculationesxsummary($rezdata) {

	unset($counted);
	$counted = array();
	$countedid = array();
	$summary = '';
	unset($rez);
	unset($rez2);
	unset($available);
	unset($capacity);
	unset($used);


	foreach ($rezdata as $rez) {

		$host = $rez['hostname'];
		$appliance = $rez['storagename'];
		if (!in_array($host, $counted)) {

			$capacity = $rez['capacity'];
			$available = $rez['available'];
			foreach ($rezdata as $rez2) {
				if ($host == $rez2['hostname'] && $appliance != $rez2['storagename']) {
					$capacity += $rez2['capacity'];
					$available += $rez2['available'];
				}
			}

			$used = $capacity - $available;
			if ($used < 0) {
				$used = -1*$used;
			}
			
			$counted[] = $host;

			$percent = $used/($capacity/100);
			//var_dump($percent); die();
			$percent = round($percent);

			//var_dump($percent); die();

			$used = round($used, 2);
			
			if ($available/1024 > 0) {
				$available = $available/1024;
				$availtype = 'TB';
			} else {
				$availtype = 'GB';
			}

			if ($used/1024 > 0) {
				$used = $used/1024;
				$usedtype = 'TB';
			} else {
				$usedtype = 'GB';
			}

			if ($capacity/1024 > 0) {
				$capacity = $capacity/1024;
				$capacitytype = 'TB';
			} else {
				$capacitytype = 'GB';
			}

			$capacity = round($capacity, 2);
			$available = round($available, 2);
			$used = round($used, 2);
			
			if (!empty($host) && !empty($appliance) && !empty($available) && !empty($used) && !empty($capacity) ) {
			$summary .='	
				<div class="onestorage">
				<span class="hoshead">'.$host.'</span><br/>
				<a class="datastoredetail" href="index.php?plugin=vmware-esx&controller=vmware-esx-ds&vmware_esx_ds_action=edit&appliance_id='.$appliance.'">Datastore detail</a>
				<div class="row">
				<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
					<div class="esxileft leftstorageblock">
						<b>'.$available.'</b> '.$availtype.' <br/>
						<span>free (physical)</span>
					</div>
				</div>
				<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 esxright sizeroside">
					<div class="totalinfor">
						<b>Used:</b> '.$used.' '.$usedtype.'<br/>
						<b>Total:</b> '.$capacity.' '.$capacitytype.' <br/>
					</div>

				</div>


					<div class="progress memoryprogress nutanixprogress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: '.$percent.'%;">
					    <span class="sr-only">'.$percent.'% used</span>
					  </div>
					</div>
				</div>
				</div>
			';
			}
		}

	}

	if ($summary == '') {
		$summary = 'Have not got storages info';
	}
	$d['summary'] = $summary;
	return $d; 
}


function esxiversion() {
	$command = 'vmware-cmd --version | grep "vSphere SDK for Perl version:" |awk \'{print $6}\'';
	$d['esxversion'] = exec($command);
	return $d;
}



function esxvmsummary($hostsid) {

$activecnt = 0;
$inactivecnt = 0;
$count = 0;
$importcnt = 0;

foreach ($hostsid as $hoste) {
		$imported = 0;
		$appliance_id = $hoste["appliance_id"];
		//var_dump($appliance_id); die();
		$virtualization	= new virtualization();
		$appliance		= new appliance();
		$resource		= new resource();
		$htvcenter_server = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$htvcenter_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		
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

			$d['state'] = $this->resource->state;
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label   = $this->lang['action_add_local_vm'];
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
			//var_dump($resource->ip); die();
			$body = array();
			$file = $this->htvcenter->get('webdir').'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
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
										$vm_state_icon = 'idle';
									} else {
										$vm_state_icon = 'idle';
									}
									$a_update = $update_button->get_string();
								} else {
									if  (!strcmp($line[1],  'active')) {
										$vm_state_icon = 'active';
										$console = $a_vnc;
									} else {
										$state = $line[1];
										if($line[1] === 'inactive') {
											$state = 'idle';
										}
										$vm_state_icon = $state;
										$a_update = $update_button->get_string();
									}
								}
							} 
							else if ($virtualization->type == "vmware-esx-vm-net") {
								if (($vm_resource->kernelid == 1) && ($vm_resource->imageid == 1)) {
									if  (!strcmp($line[1],  'active')) {
										$vm_state_icon = 'idle';
										$console = $a_vnc;
									} else {
										$vm_state_icon = 'off';
									}
									$a_update = $update_button->get_string();
								} else {
									if  (!strcmp($line[1],  'active')) {
										$vm_state_icon = 'active';
										$console = $a_vnc;
									} else {
										$vm_state_icon = 'error';
										$a_update = $update_button->get_string();
									}
								}
							} else {
								// VM not yet imported
								$imported = 1;
								$vm_state_icon = $line[1];
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

						

						 $count++;
						 switch($vm_state_img) {
						 	case 'active':
						 		$activecnt++;
						 	break;
						
						 	default:
						 		if ($imported == 1) {
						 			$importcnt++;
						 		} else {
						 			$inactivecnt++;
						 		}
						 	break;

						 }
						
						}
					}
				}
			}
		} 
	}


							

	$res = array();
		$res['esxvmactive'] = $activecnt ;
		$res['esxvminactive'] = $inactivecnt;
		$res['esxvmcount'] = $count;
		$res['esxvmimport'] = $importcnt;

		
		return $res;
		
	}


	function eventselect() {

		$resid = $this->getesxresourses();
		unset($errors);
		unset($warnings);
		unset($all);
		unset($errorcount);
		unset($warningcount);
		unset($allerwarningcount);
		unset($allermessagecount);
		unset($allererrorcount);
		unset($allerr);
		$allerr = array();

		$errorcount = 0;
		$warningcount = 0;
		$noticebreak = 0;
		$errorbreak = 0;
		$allererrorcount = 0;
		$allermessagecount = 0;
		$allerwarningcount = 0;

		$h = array();
		$h['event_id']['title'] = $this->lang['table_id'];
		$h['event_id']['hidden'] = true;
		$h['event_id']['sortable'] = false;
		$h['event_priority']['title'] = $this->lang['table_state'];
		$h['event_time']['title'] = $this->lang['table_date'];
		$h['event_source']['title'] = $this->lang['table_source'];
		$h['event_description']['title'] = $this->lang['table_description'];
		$h['event_description']['sortable'] = false;

		$event = new event();
		$b     = array();

		$table = $this->response->html->tablebuilder('events', $this->response->get_array($this->actions_name, 'select'));
		$table->offset = 0;
		$table->limit = 100;
		$table->sort = 'event_time';
		$table->order = 'DESC';

		switch ($this->response->html->request()->get('event_filter')) {
			case '':
			case 'all':
				$table->max = $event->get_count();
				break;
			case 'active':
				$table->max = $event->get_count('active');
				break;
			case 'error':
				$table->max = $event->get_count('error');
				break;
			case 'acknowledge':
				$table->max = $event->get_count('acknowledge');
				break;
			case 'warning':
				$table->max = $event->get_count('warning');
				break;
		}

		$table->init();
		switch ($this->response->html->request()->get('event_filter')) {
			case '':
			case 'all':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order);
				break;
			case 'active':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'active');
				break;
			case 'error':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'error');
				break;
			case 'acknowledge':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'acknowledge');
				break;
			case 'warning':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'warning');
				break;
		}
		
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$i = 0;
		foreach ($events as $key => $value) {
			$icon="transition.png";
			switch ($value['event_priority']) {
					case 0:
						$icon = 'off'; break;
					case 1:
					case 2:
					case 3:
						$icon = 'error'; break; // error event
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
						$icon = 'notice'; break; // undefined event
					case 9:	
						$icon = 'running'; break; // active event
					case 10:
						$icon = 'ok'; break; // notice event
			}
			if ($value['event_status'] === '1') {
					$icon = 'ack'; // acknowledged event
			}
			$description = '';
			if (strstr($value['event_description'], "ERROR running token")) {
				$error_token = str_replace("ERROR running token ", "", $value['event_description']);
				$cmd_file = $this->rootdir."/server/event/errors/".$error_token.".cmd";
				$error_file = $this->rootdir."/server/event/errors/".$error_token.".out";

				// get command and error strings
				if (($this->file->exists($cmd_file)) && ($this->file->exists($error_file))) {
					$oq_cmd = $this->file->get_contents($cmd_file);
					$oq_cmd = str_replace('"','', $oq_cmd);
					$oq_cmd_error = $this->file->get_contents($error_file);
					$oq_cmd_error = str_replace('"','', $oq_cmd_error);
					// set the event to error in any way
					$event_fields = array();
					$event_fields["event_priority"] = 1;
					$event->update($value['event_id'], $event_fields);
					$event->get_instance_by_id($value['event_id']);
					$icon = 'error';
					// set the description
					$description  = "<a href=\"/htvcenter/base/server/event/errors/".$error_token.".out\" title=\"".$oq_cmd_error."\" target=\"_BLANK\">Error</a> running htvcenter <a href=\"/htvcenter/base/server/event/errors/".$error_token.".cmd\" title=\"".$oq_cmd."\"target=\"_BLANK\">command</a>";
					
					$a = $this->response->html->a();
					$a->title   = $this->lang['action_rerun'];
					$a->label   = $this->lang['action_rerun'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'start pull-right';
					$a->href    = $this->response->get_url($this->actions_name, 'rerun').'&token='.$error_token.'&event_id='.$event->id;
					$rerun = $a->get_string();
					$description .= $rerun;
				} else {
					// we are currently re-running the token, do not show the links
					$description = "Error running htvcenter command<br><strong>Currently re-running token $error_token</strong>";
				}
			} else {
				$description = $value['event_description'];
			}	

			$allerr[$i]['event_id'] = $value['event_id'];
			$allerr[$i]['event_priority'] = $icon;
			$allerr[$i]['event_time'] = date('d.m.Y H:i:s', $value['event_time']);
			$allerr[$i]['event_source'] = $value['event_source'];
			$allerr[$i]['event_description'] = $description;

			if (preg_match('@esx|ESX|Esx|VMware|vmware|VMWARE@',$value['event_source']) || preg_match('@esx|ESX|Esx|VMware|vmware|VMWARE@',$description)) {
				$b[$i]['event_id'] = $value['event_id'];
				$b[$i]['event_priority'] = $icon;
				$b[$i]['event_time'] = date('d.m.Y H:i:s', $value['event_time']);
				$b[$i]['event_source'] = $value['event_source'];
				$b[$i]['event_description'] = $description;
			} else {
				foreach ($resid as $idd) {
					if (preg_match('@'.$idd.'@',$value['event_source']) || preg_match('@'.$idd.'@',$description)) {
							$b[$i]['event_id'] = $value['event_id'];
							$b[$i]['event_priority'] = $icon;
							$b[$i]['event_time'] = date('d.m.Y H:i:s', $value['event_time']);
							$b[$i]['event_source'] = $value['event_source'];
							$b[$i]['event_description'] = $description;
					}
				}
			}

			$i++;
		}

		


		foreach ($b as $one) {
			if( $one['event_priority'] == 'error') {
				$errorcount++;
				if ($errorbreak <= 2) {
					$errors .='	<div class="eventside">
					<h3>'.$one['event_source'].'</h3> 
					<span class="time"><i class="fa fa-clock-o"></i> '.$one['event_time'].'</span>
					<div class="row">
					<p>'.$one['event_description'].'</p>
					</div>
					</div>'; 
				}
				$errorbreak++;
			}

			if( $one['event_priority'] == 'notice') {
				$warningcount++;
				if ($noticebreak <= 2) {
					$notice .='	<div class="eventside">
					<h3>'.$one['event_source'].'</h3> 
					<span class="time"><i class="fa fa-clock-o"></i> '.$one['event_time'].'</span>
					<div class="row">
					<p>'.$one['event_description'].'</p>
					</div>
					</div>'; 
				}
				$noticebreak++;
			}

			$all .= '	<div class="eventside">
				<h3>'.$one['event_source'].'</h3> 
				<span class="time"><i class="fa fa-clock-o"></i> '.$one['event_time'].'</span>
				<div class="row">
				<p>'.$one['event_description'].'</p>
				</div>
			</div>'; 


		}

		if (empty($errors)) {
			$errors = '<div class="eventside nodataevent">Have not got any data for this block</div>';
		}

		if (empty($notice)) {
			$notice = '<div class="eventside nodataevent">Have not got any data for this block</div>';
		}

		if (empty($all)) {
			$all = '<div class="eventside nodataevent">Have not got any data for this block</div>';
		}

		$countall = 0;
		$countwarnings = 0;
		$counterrors = 0;
		$allcountwarnings = 0;

		//var_dump(count($allerr)); die();

		foreach ($allerr as $row) {
		unset($buf);

			if (empty($row['event_description'])) {
				$row['event_description'] = 'empty description';
			}

			if ($row['event_priority'] == 'notice') {
				$allcountwarnings++;

				$buf = '<a class="list-group-item">
											<span class="badge badge-info badge-icon  orangero pull-left"></span>  <span class="eventtime"> '.$row['event_time'].'</span><br/><span class="eventsource"> '.$row['event_source'].':</span> <br/> <p class="eventdescr">'.$row['event_description'].'</p>
										</a>';

				if ($countwarnings < 6) {
					$preeventsnotice .= $buf;
					$countwarnings++;
				}	
				
			}

			if ($row['event_priority'] == 'error') {
				$buf = '<a class="list-group-item">
											<span class="badge badge-info badge-icon redero pull-left"></span> <span class="eventtime"> '.$row['event_time'].'</span><br/><span class="eventsource"> '.$row['event_source'].':</span> <br/> <p class="eventdescr">'.$row['event_description'].'</p>
										</a>';

				if ($counterrors < 6) {
					$preeventserror .= $buf;
					$counterrors++;
				}

								
			}

			if ($countall < 6 && !empty($buf)) {
				$preeventsall .= $buf;
				$countall++;
			}


			
			
			

		}


		$rez['esxeventerrors'] = $errors;
		$rez['esxeventwarnings'] = $notice;
		$rez['esxeventsall'] = $all;
		$rez['esxerrorcount'] = $errorcount;
		$rez['esxwarningcount'] = $warningcount;
		$rez['preeventsall'] = $preeventsall;
		$rez['preeventsnotice'] = $preeventsnotice;
		$rez['preeventserror'] = $preeventserror;
		$rez['allcountwarnings'] = $allcountwarnings;
		return $rez;
	}


	function getesxresourses() {

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
				$state_icon = $resource->state;
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
				if (($virtualization->type == 'kvm-vm-local') || ($virtualization->type == 'vmware-esx-vm-local') || ($virtualization->type == 'xen-vm-local')  || ($virtualization->type == 'citrix-vm-local')) {
					$a = $this->response->html->a();
					$a->title = $this->lang['action_edit'].' IP';
					$a->label = $resip;
					$a->css   = 'edit';
					$a->href  = $this->response->get_url($this->actions_name, 'edit').'&resource_id='.$resid;
					$resip = $a->get_string();
				 }
				
				$data  = '<b>'.$this->lang['table_id'].'</b>: '.$resource_db["resource_id"].'<br>';
				$data .= '<b>'.$this->lang['table_name'].'</b>: '.$name.'<br>';
				$data .= '<b>'.$this->lang['table_mac'].'</b>: '.$resource_mac.'<br>';
				$data .= '<b>'.$this->lang['table_ip'].'</b>: '.$resip.'<br>';
				if (strlen($resource_type) > 36) {
					$resource_type = substr($resource_type, 0, 36);
					$resource_type = $resource_type.'...';
				}

				$data .= '<b>'.$this->lang['table_type'].'</b>: '.$resource_type;

				$hw  = '<b>'.$this->lang['table_cpu'].'</b>: '.$resource_cpus.'<br>';
				$hw .= '<b>'.$this->lang['table_memory'].'</b>: '.$mem.'<br>';
				$hw .= '<b>'.$this->lang['table_nics'].'</b>: '.$nics.'<br>';
				$hw .= '<b>'.$this->lang['table_load'].'</b>: '.$load;

				$patt = '@esx|Esx|ESX|VMware|VMWARE|vmware@';
				if (preg_match($patt, $resource_type) || preg_match($patt, $name) || preg_match($patt, $data)) {
					$b[] = $resource_db["resource_id"];
				}





			}
		}

		return $b;
	}

function todoselect() {
	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
	require_once ($RootDir.'include/htvcenter-database-functions.php');

	$db = htvcenter_get_db_connection();
	$sql = 'SELECT * FROM `todolist`';
	$rez = $db->GetAll($sql);
	
	$tasks = '';
	foreach ($rez as $row) {
		$tasks .='<li class="list-group-item"><label class="form-checkbox form-icon form-text"><input type="checkbox"><span taskid="'.$row['id'].'">'.$row['task'].'</span></label></li>';
	
	}
	$res['tasks'] = $tasks;
	return $res;
}


function vmmaincount() {

		$allvm = 0;
		$allactive = 0;
		$allinactive = 0;

		$appliance = new appliance();
		$params = $this->response->get_array($this->actions_name, 'select');
		$b = array();

		

		$table = $this->response->html->tablebuilder('appliance', $params);
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 20;
		$table->order = 'ASC';
		$table->max = $appliance->get_count();
		$table->init();

		// handle table params
		#$tps = $table->get_params();
		$tp = '';
		#foreach($tps['appliance'] as $k => $v) {
		#	$tp .= '&appliance['.$k.']='.$v;
		#}

		$resource_filter = null;
		if( $this->response->html->request()->get('resource_filter') !== '') {
			$resource = $this->htvcenter->resource();
			$resource_filter = array();
			$ar = $resource->find_resource($this->response->html->request()->get('resource_filter'));
			if(count($ar) > 0) {
				foreach($ar as $k => $v) {
					$resource_filter[] = $v['resource_id'];
				}
			}
		}

		$disabled = array();
		$appliances = $appliance->display_overview(0, 10000, $table->sort, $table->order);
		foreach ($appliances as $index => $appliance_db) {
			
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);
			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);
			$appliance_resources=$appliance_db["appliance_resources"];
			$kernel = new kernel();
			$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
			$image = new image();
			$image->get_instance_by_id($appliance_db["appliance_imageid"]);
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
			$appliance_virtualization_name = $virtualization->name;
			$virtualization_plugin_name = $virtualization->get_plugin_name();
			$resource_is_local_server = false;
			$edit_resource_ip = '';

			if ($this->response->html->request()->get('resource_type_filter') === '' || ($this->response->html->request()->get('resource_type_filter') == $resource->vtype )) {

				// Skip all resources not in $resource_filter
				if(isset($resource_filter)) {
					if(!in_array($resource->id, $resource_filter)) {
						continue;
					}
				}

				if ($appliance_resources >=0) {
					// an appliance with a pre-selected resource
					$resource->get_instance_by_id($appliance_resources);
					$resource_state_icon = '<span class="pill2 '.$resource->state.'">'.$resource->state.'</span>';
					// idle ?
					if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
						$resource_state_icon = '<span class="pill2 idle">idle</span>';
					}
					// link to resource list
					$virtualization_vm_action_name = $virtualization->name;
					if (strstr($resource->capabilities, "TYPE=local-server")) {
						$resource_is_local_server = true;
					}
					$appliance_resources_str = '';
					if (strpos($virtualization->type, "-vm")) {
						$host_resource = new resource();
						if(isset($resource->vhostid) && $resource->vhostid !== '') {
							$host_resource->get_instance_by_id($resource->vhostid);
							$host_virtualization = new virtualization();
							$host_virtualization_name = $virtualization->get_plugin_name();
							$host_virtualization->get_instance_by_type($host_virtualization_name);
							$host_appliance = new appliance();
							$host_appliance->get_instance_by_virtualization_and_resource($host_virtualization->id, $resource->vhostid);
							$link  = '?base=appliance&appliance_action=load_select';
							$link .= '&aplugin='.$virtualization_plugin_name;
							$link .= '&amp;acontroller='.$virtualization_plugin_name.'-vm';
							$link .= '&amp;'.$virtualization_plugin_name.'_vm_action=update';
							$link .= '&amp;appliance_id='.$host_appliance->id;
							$link .= '&amp;vm='.$resource->hostname;
							$appliance_resources_str = '<a href="'.$this->response->html->thisfile.$link.'" onclick="wait();">'.$resource->hostname.'</a> '.$resource_state_icon;
						}
					}
					else {
						$appliance_resources_str = $resource->hostname.' '.$resource_state_icon;
					}
					// check for local VMs without an IP 
					if (($virtualization->type == 'kvm-vm-local') || ($virtualization->type == 'vmware-esx-vm-local') || ($virtualization->type == 'xen-vm-local')  || ($virtualization->type == 'citrix-vm-local')) {
						$a = $this->response->html->a();
						$a->title = $this->lang['action_edit'].' IP';
						$a->label = $resource->ip;
						$a->css   = 'edit';
						$a->href  = '?base=resource&resource_filter=&resource_type_filter=&resource_action=edit&resource_id='.$resource->id;
						$edit_resource_ip = $a->get_string();
					 }
					
				} else {
					// an appliance with resource auto-select enabled
					$appliance_resources_str = "auto-select";
				}

				// active or inactive
				$resource_icon_default="/htvcenter/base/img/appliance.png";
				$active_state_icon='<span class="pill active">active</span>';
				$inactive_state_icon='<span class="pill inactive">inactive</span>';
			
				if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
					$state_icon=$active_state_icon;
				} else {
					$state_icon=$inactive_state_icon;
				}
				// no resource ip yet ?
				if ($resource->ip == '0.0.0.0') {
					$state_icon = '<span class="pill transition">transition</span>';
				}

				// link to image edit
				if ($image->id > 0) {
					$link  = '?base=image';
					$link .= '&amp;image_action=edit';
					$link .= '&amp;image_id='.$image->id;
					$image_edit_link = '<a class="imagebtn" href="'.$this->response->html->thisfile.$link.'" onclick="wait();">'.$image->name.'</a>';
				} else {
					$image_edit_link = $image->name;
				}

				// release resource
				$release_resource = '';
				if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
					$release_resource = '';
				} else {
					if ($appliance->resources != -1) {
						$a = $this->response->html->a();
						$a->label = $this->lang['action_release'];
						$a->title = $this->lang['resource_release'];
						$a->css   = 'enable';
						$a->href  = $this->response->get_url($this->actions_name, 'release').'&appliance_id='.$appliance->id.''.$tp;
						$release_resource = $a->get_string();
					}
				}


				// appname my code here:
				if (strlen($appliance_db["appliance_name"]) > 18) {
					$spanname = substr($appliance_db["appliance_name"], 0, 18);
					$spanname .= '...';
				} else {
					$spanname = $appliance_db["appliance_name"];
				}
				// --- end appname my code ---
			
				if (strlen($image_edit_link) > 29) {
					$imageros = substr($image_edit_link,0,29).'...';
				} else {
					$imageros = $image_edit_link;
				}

				$str = '<div class="appnamer panel-heading" appid="'.$appliance_db["appliance_id"].'"><h3 class="panel-title">'.$spanname.'</h3></div><div class="panel-body"><strong>'.$this->lang['table_id'].':</strong> '.$appliance_db["appliance_id"].'<br>
						<strong>'.$this->lang['table_name'].':</strong> '.$appliance_db["appliance_name"].'<br>
						<strong>Type:</strong> '.$appliance_virtualization_name.'<br>
						<strong>Kernel:</strong> '.$kernel->name.'<br>
						<strong>Image:</strong> '.$imageros.'<br/>
						<strong>Resource:</strong> '.$appliance_resources_str.'<br>
						<strong>IP:</strong> '.$resource->ip;


					if (preg_match('@VM \(localboot\)@', $appliance_virtualization_name) == true) {
						$allvm = $allvm + 1;

						if ($state_icon == $active_state_icon) {
							$allactive = $allactive + 1;
						} else {
							$allinactive = $allinactive + 1;
						}

					}

			}

		}

		$b['allvmcount'] = $allvm;
		$b['activeallvm'] = $allactive;
		$b['inactiveallvm'] = $allinactive;

		return $b;
}

function storagetaken() {
	$lizurl = 'http://'.$_SERVER['SERVER_NAME'].':9425/mfs.cgi';
	$lizard = file_get_contents($lizurl);

	$free = disk_free_space('/usr/share/htvcenter/storage');
	$total = disk_total_space('/usr/share/htvcenter/storage');
	$used = $total - $free;

	$onepercent = $total/100;
	$percent = $used/$onepercent;
	$percent = round($percent);

	$freearr = array();
	$totalarr = array();
	$usedarr = array();

	$freearr = $this->gethumanvalue($free);
	$totalarr = $this->gethumanvalue($total);
	$usedarr = $this->gethumanvalue($used);

	$b['sfree']= '<b>'.$freearr[0].'</b> '.$freearr[1];
	$b['stotal']= $totalarr[0].' '.$totalarr[1];
	$b['sused']= $usedarr[0].' '.$usedarr[1];;
	$b['spercent'] = $percent;
	
	return $b;
}

function gethumanvalue($size) {
	$bytes = array( ' KB', ' MB', ' GB', ' TB' );
    foreach ($bytes as $val) {

        

        $size = $size/1024;
        if (1024 >= $size) {
        	break;
        }
    }
   
 
    $res = array();
    $res[0]= round( $size, 1 );
   	$res[1] = $val;

   	return $res;

}







}
?>