<?php
/**
 * Appliance Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class appliance_select {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
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
		$this->user     = $htvcenter->user();
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

		$t = $this->response->html->template($this->tpldir.'/appliance-select.tpl.php');

		if (isset($_GET['hostpools']) && ($_GET['hostpools'] == 'true')) {
			$t = $this->response->html->template($this->tpldir.'/../../../tpl/hostpools.tpl.php');
			$servpools = $this->getpools();
			$t->add($servpools, 'hostpoolserveroptions');
		}

		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
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
		$appliance = new appliance();
		$params = $this->response->get_array($this->actions_name, 'select');
		$b = array();
		// unset unnecessary params
		unset($params['resource_type_filter']);
		unset($params['resource_filter']);
		unset($params['appliance[sort]']);
		unset($params['appliance[order]']);
		unset($params['appliance[limit]']);
		unset($params['appliance[offset]']);

		$table = $this->response->html->tablebuilder('appliance', $params);
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 2000;
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
		
		//$appliances = $appliance->display_overview(0, 10000, $table->sort, $table->order);
		$appliances = $appliance->display_overview(0, 10000, 'appliance_id', 'ASC');

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
						#$edit_resource_ip = $a->get_string();
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
					$state_text_value = "active";
				} else {
					$state_icon=$inactive_state_icon;
					$state_text_value = "inactive";
				}
				// no resource ip yet ?
				if ($resource->ip == '0.0.0.0') {
					$state_icon = '<span class="pill transition">transition</span>';
					$state_text_value = "transition";
				}
				// link to image edit
				if (strlen($image->name) > 29) {
					$imageros = substr($image->name,0,29).'...';
				} else {
					$imageros = $image->name;
				}

				if ($imageros == 'htvcenter') {
					$imageros = 'HyperTask';
				}

				if ($image->id > 0) {
					$link  = '?base=image';
					$link .= '&amp;image_action=edit';
					$link .= '&amp;image_id='.$image->id;
					$image_edit_link = '<a class="imagebtn" href="'.$this->response->html->thisfile.$link.'" onclick="wait();">'.$imageros.'</a>';
				} else {
					$image_edit_link = $imageros;
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
						//$a->css   = 'enable btn-labeled fa fa-refresh';
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
				
				if (preg_match('@VM \(localboot\)@', $appliance_virtualization_name)) {
					$clonelink = '<div class="vmaplfeatures_ clone-spanshot"><span class="clonera"><i class="fa fa-clone"></i> Clone</span> <span class="protera"><i class="fa fa-files-o"></i> Snapshot</span></div>';
				} else {
					$clonelink ='';
				}

				if ($appliance_virtualization_name == 'KVM VM (localboot)') {
					$appliance_virtualization_name = 'KVM VM';
				}

				if ($appliance_virtualization_name == 'OCH VM (localboot)') {
					$appliance_virtualization_name = 'OCH VM';
				}

				if ($appliance_virtualization_name == 'KVM VM') {
					$appliance_virtualization_name = 'OCH VM';
				}

				if ($appliance_virtualization_name == 'KVM Host') {
					$appliance_virtualization_name = 'OCH Host';
				}

				if ($appliance_virtualization_name == 'ESX VM (localboot)') {
					$appliance_virtualization_name = 'ESX VM';
				}

				if ($appliance_db["appliance_name"] == 'htvcenter') {
					$appliance_db["appliance_name"] = 'HyperTask';
				}

				if ($kernel->name == 'htvcenter') {
					$kernel->name = 'HyperTask';
				}

				if ($spanname == 'htvcenter') {
					$spanname = 'HyperTask';
				}
				$str = '<div class="appnamer panel-heading" appid="'.$appliance_db["appliance_id"].'"><h3 class="panel-title">'.$spanname.'</h3></div><div class="panel-body"><strong>'.$this->lang['table_id'].':</strong> '.$appliance_db["appliance_id"].'<br>'.$clonelink.'
					<strong>'.$this->lang['table_name'].':</strong> '.$appliance_db["appliance_name"].'<br>
					<strong>Type:</strong> '.$appliance_virtualization_name.'<br>
					<strong>Kernel:</strong> '.$kernel->name.'<br>
					<strong>Image:</strong> '.$image_edit_link.'<br/>';

				$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
				require_once "$RootDir/include/htvcenter-database-functions.php";
				$db = htvcenter_get_db_connection();
				$appliance_id = $appliance_db["appliance_id"];
				$ip = '';
				if($resource->ip !== '10.100.0.1'){
					$ip = '<br><strong>IP:</strong> '.$resource->ip;
				} else {
					$array = '';
					$array = $db->Execute("select * from ip_mgmt where ip_mgmt_appliance_id='$appliance_id'");
					if(isset($array->fields['ip_mgmt_address'])) {
						$ip = '<br><strong>IP:</strong> '.print_r($array->fields['ip_mgmt_address'], true);
					}
				}
				#if (isset($resource->ip) && $resource->ip !='') {
				$str .='<strong>Resource:</strong> '.$appliance_resources_str.$ip;
				#$str .='<strong>IP:</strong> '.$resource->ip.'<br/>';
				#}

				if(strpos($virtualization->type, "-vm") && isset($resource->vhostid) && ($resource->vhostid != '')) {
					$happliance = new appliance();
					$hresource = $happliance->get_ids_per_resource($resource->vhostid);
					if(isset($hresource[0]['appliance_id'])) {
						$happliance->get_instance_by_id($hresource[0]['appliance_id']);
						$link  = '?base=appliance';
						$link .= '&amp;appliance_action=edit';
						$link .= '&amp;appliance_id='.$happliance->id;
						$href  = '<a href="'.$this->response->html->thisfile.$link.'" onclick="wait();">'.$happliance->name.'</a>';
						$str  .= '<br><strong>Host:</strong> '.$href;
					}
				}

				// appliance edit
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label   = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				//$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, 'edit').'&appliance_id='.$appliance->id.''.$tp;
				$strEdit    = $a->get_string();

				// appliance start
				$strStart = '';
				if($appliance_resources !== '0') {
					$a = $this->response->html->a();
					$a->handler = 'onclick="wait();"';
					if ($appliance->stoptime == 0) {
						$a->title = $this->lang['action_stop'];
						$a->label = $this->lang['action_stop'];
						//$a->css   = 'disable';
						$a->href  = $this->response->get_url($this->actions_name, 'stop').'&'.$this->identifier_name.'[]='.$appliance->id.''.$tp;
					} else {
						$a->title = $this->lang['action_start'];
						$a->label = $this->lang['action_start'];
						//$a->css   = 'enable btn-labeled fa fa-play';
						$a->href  = $this->response->get_url($this->actions_name, 'start').'&'.$this->identifier_name.'[]='.$appliance->id.''.$tp;
					}
					$strStart = $a->get_string();
				}

				// build the plugin link section
				$appliance_link_section = '';
				// add link to continue if appliance has unfinished wizard
				$disabled = array();
				if(isset($appliance->wizard) && strpos($appliance->wizard, 'wizard') !== false) {
					$params = explode(',', $appliance->wizard);
					$wizard_step = explode('=', $params[0]);
					$wizard_user = explode('=', $params[1]);
					if ($wizard_user[1] === $this->user->name) {
						// continue button
						$a = $this->response->html->a();
						$a->title   = $this->lang['action_continue'];
						$a->label   = $this->lang['action_continue'];
						$a->handler = 'onclick="wait();"';
						$a->css     = 'badge continue';
						$a->href    = $this->response->get_url($this->actions_name, $wizard_step[1]).'&appliance_wizard_id='.$appliance->id.''.$tp;
						$appliance_comment = $a->get_string();
					} else {
						$appliance_comment = sprintf($this->lang['appliance_create_in_progress'], $wizard_user[1]);
					}
					// disable all buttons
					$disabled[] = $appliance->id;
					$strEdit = '';
					$strStart = '';
					$strStop = '';
					$release_resource = '';
				} else {
					$plugin = new plugin();
					$enabled_plugins = $plugin->enabled();
					$alinkcount = 0;
					foreach ($enabled_plugins as $index => $plugin_name) {
						$plugin_appliance_link_section_hook = $this->htvcenter->get('webdir')."/plugins/".$plugin_name."/htvcenter-".$plugin_name."-appliance-link-hook.php";
						if (file_exists($plugin_appliance_link_section_hook)) {
							require_once "$plugin_appliance_link_section_hook";
							$appliance_get_link_function = str_replace("-", "_", "get_"."$plugin_name"."_appliance_link");
							if(function_exists($appliance_get_link_function)) {
								$p = $plugin->get_config($plugin_name);
								$alink = $appliance_get_link_function($appliance->id);
								if(is_object($alink)) {
									if ($alinkcount == 0) {
										$appliance_link_section .= '<div class="alinkleft">';
									}

								//	$alink->handler = $alink->handler.' onclick="wait();"';
									//$alink->css = 'enable';
									$alink->css = 'en-app fa fa-plus';
									$alink->title = preg_replace('~(.*?)<a.*>(.*?)</a>(.*?)~i', '$1$2$3', $p['description']);

									$alink = $alink->get_string();
									
									if ($alinkcount == 5) {
										$alinkcount = 0;
										$appliance_link_section .='</div>';
									}
									$alinkcount++;
								}
								$appliance_link_section .= $alink;
								
							}
						}
					}

					if ($edit_resource_ip != '') {
						$edit_resource_ip = '<span id="erip">'.$edit_resource_ip.'</span>';
					}

					$appliance_link_section = '<br/><div class="appliance_links">'.$edit_resource_ip.' '.$appliance_link_section.'</div>';
					if($appliance_db["appliance_comment"] !== '') {
						$appliance_comment  = $appliance_db["appliance_comment"];
						$appliance_comment .= "<hr>";
						$appliance_comment .= $appliance_link_section;
					} else {
						$appliance_comment = "<br/><hr>".$appliance_link_section;
					}
				}
				$b[] = array('appliance_id' => $appliance_db["appliance_id"], 'appliance_name' => $appliance_db["appliance_name"], 'appliance_ip' => $resource->ip, 'appliance_values' => $str, 'appliance_comment' => $appliance_comment, 'appliance_virtualization' => $appliance_db["appliance_virtualization"], 'appliance_image' => $image_edit_link, 'appliance_total_memory' => $resource->memtotal, 'appliance_used_memory' => $resource->memused, 'appliance_cpu' => $resource->cpunumber, 'appliance_load' => $resource->load, 'appliance_edit' => $strEdit, 'appliance_start' => $strStart, 'appliance_release' => $release_resource, 'appliance_state' => $state_icon, 'appliance_state_value' => $state_text_value, 'appliance_link_section' => $appliance_link_section,);
			}
		}
		// Filter
		$virtulization_types = new virtualization();
		$list = $virtulization_types->get_list();
		$filter = array();
		$filter[] = array('', '');
		foreach( $list as $l) {
			//$filter[] = array( $l['label'], $l['value']);
			if (!preg_match('@networkboot@', $l['label'])) {
				$valll = str_replace('KVM', 'OCH', $l['label']);
				$valll = str_replace('(localboot)', '', $valll);
				$filter[] = array( $valll, $l['value']);
			}
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
		$add->href    = $this->response->get_url($this->actions_name, "step1").''.$tp;

		$table->id = 'Tabellerr-list';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = true;
		$table->sort_link = false;
		$table->sort_form = false;
		$table->max = count($b);
		$table->head = $h;
		$table->body = $b;
		#$table->form_action = $this->response->html->thisfile;
		$table->actions_name = $this->actions_name;
		$table->actions = array(
			array('start' => $this->lang['action_start']),
			array('stop' => $this->lang['action_stop']),
			array('remove' => $this->lang['action_remove'])
		);
		$table->identifier = 'appliance_id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = $disabled;
		
		$div_html = '';
		
		$row_headers = array('ID', 'VM Name', 'VM IP', 'Image', 'Total Memory', 'Memory Used', 'CPU', 'CPU Used', 'Status', '...');
		
		$div_html = '<table class="table table-hover nowrap dataTable dtr-inline" id="cloud_appliances_table" role="grid" style="width: 100%;"><thead><tr>';

		foreach ($row_headers as $head) {
			$div_html .= '<th>'.$head.'</th>';
		}
		$div_html .= '</tr></thead><tbody>';
		for ($i = 0; $i < count($b); $i++) {
			$div_html .= '<tr class="hoverbg" id="' . $i . '">';
			$div_html .= '<td>' . $i . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_name'] . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_ip'] . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_image'] . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_total_memory'] . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_used_memory'] . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_cpu'] . '</td>';
			$div_html .= '<td>' .  $b[$i]['appliance_load'] . '</td>';
			$div_html .= '<td class="status ' . $b[$i]['appliance_state_value'] .'">' .  $b[$i]['appliance_state_value'] . '</td>';
			$div_html .= '<td class="toggle-graph" row-id="' . $i . '"><a href="#"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a></td>';
			$div_html .= '<td class="app_start">'.$b[$i]['appliance_start'].'</td>';
			$div_html .= '<td class="app_edit">'.$b[$i]['appliance_edit'].'</td>';
			$div_html .= '<td class="app_release">'.$b[$i]['appliance_release'].'</td>';
			$div_html .= '<td class="app_clone">'.$clonelink.'</td>';
			$div_html .= '<td class="svaccess">'.$b[$i]['appliance_link_section'].'</td>';
			$div_html .= '<td class="appliance_id">'.$b[$i]['appliance_id'].'</td>';
			$div_html .= '</tr>'; 
		}
		$div_html .=	'</tbody></table>';
		
		$d['form']   = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add']    = $add->get_string();
		$d['table']  = $table;
		$d['div_html'] = $div_html;
		$d['resource_type_filter'] = $box1->get_string();
		$d['resource_filter'] = $box2->get_string();


		$query = "SELECT `storage_id` FROM `storage_info` WHERE `storage_resource_id` = 0 and `storage_name` = 'htvcenter-bf'";
		$res = mysql_query($query);
		
		while ($rez=mysql_fetch_array($res)) {
			$stid = $rez['storage_id'];
		}
		$d['storagekvmid'] = $stid;
		return $d;
	}




	function getpools() {

		
        $options = '';
		$appliance = new appliance();
		
		$b = array();

		

		$disabled = array();
		$appliances = $appliance->display_overview(0, 10000, 'appliance_id', 'ASC');
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
					if (($virtualization->type == 'kvm-vm-local') || ($virtualization->type == 'vmware-esx-vm-local') || ($virtualization->type == 'xen-vm-local')  || ($virtualization->type == 'citrix-vm-local') || ($virtualization->type == 'OCH-vm-local')) {
						$a = $this->response->html->a();
						$a->title = $this->lang['action_edit'].' IP';
						$a->label = $resource->ip;
						$a->css   = 'edit';
						$a->href  = '?base=resource&resource_filter=&resource_type_filter=&resource_action=edit&resource_id='.$resource->id;
						#$edit_resource_ip = $a->get_string();
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


				if (strlen($image->name) > 29) {
					$imageros = substr($image->name,0,29).'...';
				} else {
					$imageros = $image->name;
				}

				if ($image->id > 0) {
					$link  = '?base=image';
					$link .= '&amp;image_action=edit';
					$link .= '&amp;image_id='.$image->id;
					$image_edit_link = '<a class="imagebtn" href="'.$this->response->html->thisfile.$link.'" onclick="wait();">'.$imageros.'</a>';
				} else {
					$image_edit_link = $imageros;
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
						//$a->css   = 'enable btn-labeled fa fa-refresh';
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
				

				$vname = $appliance_virtualization_name;

				$id = $appliance_db["appliance_id"];
				$name = $appliance_db["appliance_name"];

				if ( ( preg_match('@Host@', $vname)) && ($id != 1) ) {
					$options .= '<option value="'.$id.'">'.$name.'</option>';
				}

				

				
			}

		}

		
		return $options;
	}


}
?>
