<?php
/**
 * Cloud Users Appliance Actions
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_ui_appliances
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui';

/**
* user
* @access public
* @var string
*/
var $user;
/**
* cloud-id
* @access public
* @var int
*/
var $cloud_id;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->htvcenter = $htvcenter;
		$this->response = $response;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->docrootdir = $_SERVER["DOCUMENT_ROOT"];
		// include classes and prepare ojects
		require_once $this->rootdir."/plugins/cloud/class/cloudappliance.class.php";
		$this->cloudappliance = new cloudappliance();
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloudrequest = new cloudrequest();
		require_once $this->rootdir."/plugins/cloud/class/cloudnat.class.php";
		$this->cloudnat	= new cloudnat();
		require_once $this->rootdir."/plugins/cloud/class/cloudimage.class.php";
		$this->cloudimage = new cloudimage();
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$table = $this->select();
		$template = $this->response->html->template($this->tpldir.'/cloud-ui.appliances.tpl.php');

		if (preg_match('@/htvcenter/base/index\.php\?plugin=cloud\&controller\=cloud\-user@', $_SERVER['REQUEST_URI'])) {
			$template = $this->response->html->template($this->tpldir.'/cloud-ui.appliances.tpl2.php');
		}
		$template->add($table, 'table');
		$template->add($this->lang['appliances']['label'], 'label');

		$query = "SELECT * FROM `cloud_selector` WHERE `type` = 'disk'";
		$res = mysql_query($query);

		$volumeselect = '<select id="volumeselect">';
		$rowarr = array();
		while($row = mysql_fetch_assoc($res)) {
			$volumeselect .= '<option value="'.$row['quantity'].'" ccu="'.$row['price'].'" >'.$row['name'].'</option>';
		}
		$volumeselect .=  '</select>';

		$template->add($volumeselect, 'volumeselect');
		return $template;
	}

	//--------------------------------------------
	/**
	 * Cloud Users Appliance Actions
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {

		if ( isset($_GET['action']) == true && $_GET['action'] == 'volumedatadel') {
			$num = $_GET['num'];
			$hostname = $_GET['hostname'];

			$appliance = new appliance();
				$ap = $appliance->get_instance_by_name($hostname);
				$instname = $ap->name;

				$resource = new resource();
				$l = $resource->get_instance_id_by_hostname($hostname);
				$resource->get_instance_by_id($l->id);
				$resource->get_instance_by_id($ap->virtualization);

				$resource->get_instance_by_id($resource->vhostid);
				
				$resip = $resource->ip;

				$command = 'sudo /usr/share/htvcenter/plugins/kvm/bin/volumesdata.sh remove '.$hostname.' '.$num;
				$resource->send_command($resip, $command);

				$numeo = $num - 2;
				$volname = $instname.'vol'.$numeo;



				$query = "DELETE FROM `cloud_volumes` WHERE `volume_name` = '$volname' AND `instance_name` = '$instname' ";
				mysql_query($query);

				$command = '/usr/share/htvcenter/plugins/kvm/bin/volumesdata.sh getlist '.$hostname;
				
				$resource->send_command($resip, $command);
				$localip = $_SERVER['HTTP_HOST'];
				$filesen = 'http://'.$resip.'/'.$hostname.'_volume_data';
				
				sleep(10);
				$data = file_get_contents($filesen);
				echo $data;
			
			
			die();
		}

		if ( isset($_GET['action']) == true && $_GET['action'] == 'volumedataadd') {
			$num = $_GET['num'];
			$hostname = $_GET['hostname'];
			$sizevol = $_GET['sizevol'];

			$username = $this->htvcenter->user()->name;
			$query = "SELECT `size` FROM `cloud_volumes` WHERE `user_name` = '$username' AND `instance_name` = '$hostname'";
			$res = mysql_query($query);
				
			$volsum = 0;
			while($rez = mysql_fetch_row($res)) {
				$volsum = $rez[0] + $volsum;
			}

			require_once "cloud.limits.class.php";
			$this->cloud_limits = new cloud_limits($this->htvcenter, $this->cloudconfig, $this->clouduserlimits, $this->cloudrequest);
			$limits = $this->cloud_limits->free('disk');
			
			$newsize = $volsum + $sizevol;

			if ($newsize > $limits) {
				echo 'no disk space';
				die();
			}

			


			$appliance = new appliance();
				$ap = $appliance->get_instance_by_name($hostname);
				$instname = $ap->name;
				$resource = new resource();
				$l = $resource->get_instance_id_by_hostname($hostname);
				$resource->get_instance_by_id($l->id);
				$resource->get_instance_by_id($ap->virtualization);

				$resource->get_instance_by_id($resource->vhostid);
				
				$resip = $resource->ip;

				$command = '/usr/share/htvcenter/plugins/kvm/bin/volumesdata.sh add '.$hostname.' '.$num.' '.$sizevol;
				
				$resource->send_command($resip, $command);
				
				$numeo = $num - 2;
				$name = $hostname.'vol'.$numeo;
				
				//$command = 'sudo ';
				$command = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm add';
				$command .= ' -n '.$name.' -m '.$sizevol;
				$command .= ' -o '.'raw';
				$command .= ' -t '.'kvm-bf-deployment'.' -v '.'storage1';
				$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->htvcenter->admin()->name;
				$command .= ' --htvcenter-cmd-mode background';

				//var_dump($command); die();
	
				$resource->send_command($resip, $command);
				$username = $this->htvcenter->user()->name;

				// get ccus:
				$query = "SELECT `price` FROM `cloud_selector` WHERE `quantity` = '$sizevol'";
				$res = mysql_query($query);
				while ($rez=mysql_fetch_row($res)) {
					$ccu = $rez[0];
				}



				$query = "INSERT INTO `cloud_volumes`(`instance_name`, `volume_name`, `size`, `type`, `user_name`, `ccu`) VALUES ('$instname', '$name', '$sizevol', 'raw', '$username', '$ccu')";
				mysql_query($query);



				$command = '/usr/share/htvcenter/plugins/kvm/bin/volumesdata.sh getlist '.$hostname;
				
				
				$resource->send_command($resip, $command);
				$localip = $_SERVER['HTTP_HOST'];
				$filesen = 'http://'.$resip.'/'.$hostname.'_volume_data';
				
				sleep(10);
				$data = file_get_contents($filesen);
				echo $data;
			
				
			
			die();
		}
		
		if ( isset($_GET['action']) == true && $_GET['action'] == 'volumedata') {
			$hostname = $_GET['hostname'];

				$appliance = new appliance();
				$ap = $appliance->get_instance_by_name($hostname);
				
				$resource = new resource();
				$l = $resource->get_instance_id_by_hostname($hostname);
				$resource->get_instance_by_id($l->id);
				$resource->get_instance_by_id($ap->virtualization);

				$resource->get_instance_by_id($resource->vhostid);
				
				$resip = $resource->ip;


		
				
				$command = '/usr/share/htvcenter/plugins/kvm/bin/volumesdata.sh getlist '.$hostname;
			
				
				$resource->send_command($resip, $command);
				$localip = $_SERVER['HTTP_HOST'];
				$filesen = 'http://'.$resip.'/'.$hostname.'_volume_data';
				
				sleep(5);
				$data = file_get_contents($filesen);
				echo $data;
			
			die();
		}
		
		if ( ( isset($_GET['action']) == true) && ($_GET['action'] == 'cdrom') ) {

			$hostname = $_GET['hostname'];
			$cdaction = $_GET['cdaction'];
			$isofile = $_GET['isofile'];



			if ( isset($cdaction) && ( $cdaction == 'insert' || $cdaction == 'eject' ) ) {
				
				$appliance = new appliance();
				$ap = $appliance->get_instance_by_name($hostname);
				
				$resource = new resource();
				$l = $resource->get_instance_id_by_hostname($hostname);
				$resource->get_instance_by_id($l->id);
				$resource->get_instance_by_id($ap->virtualization);

				$resource->get_instance_by_id($resource->vhostid);
				
				$resip = $resource->ip;
				
				if ($cdaction == 'insert') {
					$command = '/usr/share/htvcenter/plugins/kvm/bin/kvmcdrom.sh insert '.$hostname.' '.$isofile;
					$resource->send_command($resip, $command);
					echo 'Insert succesful'; die();
				} else {
					if ($cdaction == 'eject') {
						$command = '/usr/share/htvcenter/plugins/kvm/bin/kvmcdrom.sh eject '.$hostname;
						$resource->send_command($resip, $command);
						echo 'Eject succesful'; die();
					}
				}

				
				 
			}

			if ( isset($cdaction) && $cdaction == 'getlist' ) {
				$filecont = scandir('/linuxcoe-iso');
				$resrow = '';
				$first = true;
				foreach ($filecont as $filerow) {
					if (preg_match('@.iso@', $filerow)) {
						if ($first) {
							$resrow .= $filerow;
							$first = false;
						} else {
							$resrow .= ';'.$filerow;
						}
						
					}
				}
				echo $resrow; die();
				//echo 'getok;ssecondfile;onezzmorefile'; die();
			}
						
			
			
			
			
			
			die();
		}

		$h['state']['title'] = $this->lang['appliances']['state'];
		$h['id']['title']  = $this->lang['appliances']['id'];
		$h['id']['hidden'] = true;
		$h['name']['title']  = $this->lang['create']['hostname'];
		$h['name']['hidden'] = true;
		$h['type']['title']  = $this->lang['create']['type'];
		$h['type']['hidden'] = true;
		$h['cpu']['title']  = $this->lang['create']['cpu'];
		$h['cpu']['hidden'] = true;
		$h['ram']['title']  = $this->lang['create']['ram'];
		$h['ram']['hidden'] = true;
		$h['config']['title'] = '&#160;';
		$h['config']['sortable'] = false;
		$h['kernel']['title'] = $this->lang['create']['kernel'];
		$h['kernel']['hidden'] = true;
		$h['disk']['title'] = $this->lang['create']['disk'];
		$h['disk']['hidden'] = true;
		$h['image']['title'] = $this->lang['create']['image'];
		$h['image']['hidden'] = true;
		$h['comment']['title'] = '&#160;';
		$h['comment']['sortable'] = false;
		$h['action']['title'] = '&#160;';
		$h['action']['sortable'] = false;

		

		// mark sorted values
		$sort = $this->response->html->request()->get('cloud_table[sort]');
		if($sort === 'id') {
			$this->lang['appliances']['id'] = '<span>'.$this->lang['appliances']['id'].'</span>';
		}
		else if($sort === 'name') {
			$this->lang['create']['hostname'] = '<span>'.$this->lang['create']['hostname'].'</span>';
		}
		else if($sort === 'type') {
			$this->lang['create']['type'] = '<span>'.$this->lang['create']['type'].'</span>';
		}
		else if($sort === 'cpu') {
			$this->lang['create']['cpu'] = '<span>'.$this->lang['create']['cpu'].'</span>';
		}
		else if($sort === 'ram') {
			$this->lang['create']['ram'] = '<span>'.$this->lang['create']['ram'].'</span>';
		}
		else if($sort === 'kernel') {
			$this->lang['create']['kernel'] = '<span>'.$this->lang['create']['kernel'].'</span>';
		}
		else if($sort === 'disk') {
			$this->lang['create']['disk'] = '<span>'.$this->lang['create']['disk'].'</span>';
		}
		else if($sort === 'image') {
			$this->lang['create']['image'] = '<span>'.$this->lang['create']['image'].'</span>';
		}

		$cloudreq_array = $this->cloudrequest->get_all_ids_per_user($this->clouduser->id);
		$user_requests = array();
		// build an array of our appliance id's
		foreach ($cloudreq_array as $cr) {
			$user_requests[] = $cr['cr_id'];
		}

		$show_ip_mgmt = false;
		if (!strcmp($this->cloudconfig->get_value_by_key('ip-management'), "true")) {
			$show_ip_mgmt = true;
		}
		$sshterm_enabled = false;
		if (!strcmp($this->cloudconfig->get_value_by_key('show_sshterm_login'), "true")) {
			$sshterm_enabled = true;
		}
		$show_application_ha = false;
		if (!strcmp($this->cloudconfig->get_value_by_key('show_ha_checkbox'), "true")) {
			$show_application_ha = true;
		}
		$collectd_graph_enabled = false;
		if (!strcmp($this->cloudconfig->get_value_by_key('show_collectd_graphs'), "true")) {
			$collectd_graph_enabled = true;
		}
		$private_image_config_enabled = false;
		if (!strcmp($this->cloudconfig->get_value_by_key('show_private_image'), "true")) {
			$private_image_config_enabled = true;
		}
		$show_pause_button = false;
		$show_unpause_button = false;

		// now we go over all our appliances from the users request list
		$app_count = 0;
		$ta = array();
		foreach ($user_requests as $reqid) {
			$appliance = null;
			$this->cloudrequest->get_instance_by_id($reqid);
			if ((strlen($this->cloudrequest->appliance_id)) && ($this->cloudrequest->appliance_id != 0)) {
				$appliance = $this->htvcenter->appliance();
				$appliance->get_instance_by_id($this->cloudrequest->appliance_id);
			}

			$sshterm_login = false;
			$appliance_resources_str="";
			$res_ip_loop = 0;
			$resource = $this->htvcenter->resource();

			// prepare values
			$str_cpu = intval($this->cloudrequest->cpu_req);
			if($str_cpu === 0) {
				$str_cpu = 'Auto';
			}
			$str_ram = intval($this->cloudrequest->ram_req);
			if($str_ram === 0) {
				$str_ram = 'Auto';
			} else {
				if( $str_ram >= 1000 ) {
					$str_ram = round(($str_ram / 1000), 3, PHP_ROUND_HALF_DOWN).' GB';
				} else {
					$str_ram = $str_ram .' MB';
				}
			}
			
			if(isset($appliance)) {
				$appliance_resources = $appliance->resources;
				#if (!strlen($sshterm_login_ip)) {
					// in case no external ip was given to the appliance we show the internal ip
					$resource->get_instance_by_id($appliance->resources);
					#$appliance_resources_str .= $resource->ip;
					$sshterm_login_ip = $resource->ip;
					$sshterm_login = true;
				#}

				if ($appliance_resources >=0) {
					// check ip-mgmt
					if ($show_ip_mgmt) {
						if (file_exists($this->rootdir."/plugins/ip-mgmt/.running")) {
							require_once $this->rootdir."/plugins/ip-mgmt/class/ip-mgmt.class.php";
							$ip_mgmt = new ip_mgmt();
							$appliance_first_nic_ip_mgmt_id = $ip_mgmt->get_id_by_appliance($appliance->id, 1);
							if ($appliance_first_nic_ip_mgmt_id > 0) {
								$appliance_ip_mgmt_config_arr = $ip_mgmt->get_instance('id', $appliance_first_nic_ip_mgmt_id);
								if (isset($appliance_ip_mgmt_config_arr['ip_mgmt_address'])) {
									$sshterm_login_ip = $appliance_ip_mgmt_config_arr['ip_mgmt_address'];
									$appliance_resources_str .= ', '.$appliance_ip_mgmt_config_arr['ip_mgmt_address'];
									$appliance_resources_str = substr($appliance_resources_str,2);
									$sshterm_login = true;
								}
							}
						}
					}

					// check if we need to NAT the ip address
					$cn_nat_enabled = $this->cloudconfig->get_value_by_key('cloud_nat');  // 18 is cloud_nat
					if (!strcmp($cn_nat_enabled, "true")) {
						$appliance_resources_str = $this->cloudnat->translate($appliance_resources_str);
						$sshterm_login_ip = $this->cloudnat->translate($sshterm_login_ip);
					}

				} else {
					// an appliance with resource auto-select enabled
					$appliance_resources_str = "auto-select";
					$sshterm_login = false;
				}

				// state
				$state = $this->cloudrequest->getstatus($this->cloudrequest->id);
				$this->cloudappliance->get_instance_by_appliance_id($appliance->id);
				switch ($this->cloudappliance->state) {
					case 0:
						if($state === 'starting' || $resource->state === 'transition') {
							$cloudappliance_state = 'busy';
							$sshterm_login = false;
							$show_application_ha = false;
							$show_pause_button = false;
							$private_image_enabled = false;
						} else {
							$cloudappliance_state = "paused";
							$sshterm_login = false;
							$show_unpause_button = true;
							$show_pause_button = false;
							$show_application_ha = false;
							$private_image_enabled = false;
						}
						break;
					case 1:
						if ($resource->state === 'active' && $state === 'active') {
							$cloudappliance_state = "active";
							$sshterm_login = true;
							$show_application_ha = true;
							$show_pause_button = true;
							$private_image_enabled = true;
						} else {
							$cloudappliance_state = 'busy';
							$sshterm_login = false;
							$show_application_ha = false;
							$show_pause_button = false;
							$private_image_enabled = false;
						}
						break;
				}

				$kernel = $this->htvcenter->kernel();
				if(isset($appliance->kernelid) && $appliance->kernelid !== '') {
					$kernel->get_instance_by_id($appliance->kernelid);
				}
				$image_size = '';
				$image = $this->htvcenter->image();
				if(isset($appliance->imageid) && $appliance->imageid !== '') {
					$image->get_instance_by_id($appliance->imageid);
					// image disk size
					$this->cloudimage->get_instance_by_image_id($image->id);
					$image_size = $this->cloudimage->disk_size;
				}
				$virtualization = $this->htvcenter->virtualization();
				if(isset($appliance->virtualization) && $appliance->virtualization !== '') {
					$virtualization->get_instance_by_id($appliance->virtualization);
				}

				// prepare actions
				$cloudappliance_action = "";
				$plugin_action = "";
				// sshterm login
				if ($sshterm_enabled) {
					if (($sshterm_login) && (isset($sshterm_login_ip))) {
						// get the parameters from the plugin config file
						$htvcenter_PLUGIN_SSHTERM_CONFIG_FILE= $this->htvcenter->get('basedir')."/plugins/sshterm/etc/htvcenter-plugin-sshterm.conf";
						$store = htvcenter_parse_conf($htvcenter_PLUGIN_SSHTERM_CONFIG_FILE);
						extract($store);
						$sshterm_window = 'window'.str_replace('.','',$sshterm_login_ip);
						$sshterm_login_url="https://$sshterm_login_ip:$htvcenter_PLUGIN_WEBSHELL_PORT";
						$a = $this->response->html->a();
						$a->label   = '<i class="fa fa-terminal"></i> '.$this->lang['appliances']['plugin_ssh'];
						$a->handler = "";
						$a->css = 'plugin console myconsole';
						$a->href = '#';
						$a->handler = 'onclick="sshwindow = window.open(\''.$sshterm_login_url.'\',\''.$sshterm_window.'\', \'location=0,status=0,scrollbars=yes,resizable=yes,width=973,height=500,left=100,top=100,screenX=400,screenY=100\'); sshwindow.focus(); return false;"';
						$plugin_action .= $a->get_string();
					}
				}
				// application ha
/*
				if ($show_application_ha) {
					$lcmc_gui="lcmc/lcmc-gui.php";
					$icon_size = "width='21' height='21'";
					$icon_title = $this->lang['appliances']['plugin_ha'];
					$lcmc_url = "<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('$lcmc_gui','','location=0,status=0,scrollbars=1,width=1024,height=768,left=50,top=20,screenX=50,screenY=20');\">
						<image border=\"0\" alt=\"".$icon_title."\" title=\"".$icon_title."\" src=\"../img/ha.png\">
						</a>";
					$plugin_action .= $lcmc_url;
				}
*/
				// regular actions
				if ($show_pause_button) {
					// pause
					$a = $this->response->html->a();
					$a->title   = $this->lang['appliances']['action_pause'];
					$a->label   = '<span title="Pause" class="editvolumesm" ><i class="fa fa-pause"></i></span>';
					$a->handler = "";
					$a->css     = '';
					$a->href    = $this->response->get_url($this->actions_name, 'pause').'&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
					$cloudappliance_action .= $a->get_string();
					// restart
					$a = $this->response->html->a();
					$a->title   = $this->lang['appliances']['action_restart'];
					$a->label   = '<span title="Restart" class="editvolumesm" ><i class="fa fa-refresh"></i></span>';
					$a->handler = "";
					$a->css     = '';
					$a->href    = $this->response->get_url($this->actions_name, 'restart').'&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
					$cloudappliance_action .= $a->get_string();
				}
				if ($show_unpause_button) {
					// pause
					$a = $this->response->html->a();
					$a->title   = $this->lang['appliances']['action_unpause'];
					$a->label   = '<span title="Start" class="editvolumesm" ><i class="fa fa-play"></i></span>';
					$a->handler = "";
					$a->css     = '';
					$a->href    = $this->response->get_url($this->actions_name, 'unpause').'&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
					$cloudappliance_action .= $a->get_string();
				}
				if ($collectd_graph_enabled) {
					// system stats
					$data = $this->htvcenter->get('basedir').'/plugins/collectd/data/'.$appliance->name;
					if (file_exists($data)) {
						$a = $this->response->html->a();
						$a->label   = '<span title="system stats" class="editvolumesm" ><i class="fa fa-bar-chart"></i></span>';
						$a->handler = "";
						$a->css     = '';
						$a->href    = $this->response->get_url($this->actions_name, 'statistics').'&appliance_id='.$appliance->id;
						$plugin_action .= $a->get_string();
					}
				}
				// private images
				/*if (($private_image_enabled) && ($private_image_config_enabled)) {
					$a = $this->response->html->a();
					$a->label   = '<span title="Create private image" class="editvolumesm" ><i class="fa fa-plus"></i></span>';
					$a->title   = 'Create private image';
					$a->handler = "";
					$a->css     = '';
					$a->href    = $this->response->get_url($this->actions_name, 'image_private').'&appliance_id='.$appliance->id;
					$cloudappliance_action .= $a->get_string();
				}*/
				// noVNC
				if (!strcmp($this->cloudconfig->get_value_by_key('allow_vnc_access'), "true") && $show_pause_button) {
					$a = $this->response->html->a();
					$a->label   = '<i class="fa fa-laptop"></i> '.$this->lang['appliances']['plugin_novnc'];
					$a->handler = "";
					$a->css     = 'plugin novnc mynovnc';
					$a->href    = 'api.php?action=novnc&appliance_id='.$appliance->id;
					$a->target  = '_blank';
					$plugin_action .= $a->get_string();
				}

				if($cloudappliance_state === 'active' || $cloudappliance_state === 'paused') {
					// appliance update
					$a = $this->response->html->a();
					$a->title   = $this->lang['appliances']['action_update'];
					$a->label   = '<span title="Edit volumes" class="editvolumesm" ><i class="fa fa-pencil"></i></span>';
					$a->handler = "";
					$a->css     = '';
					$a->href    = $this->response->get_url($this->actions_name, 'appliance_update').'&'.$this->identifier_name.'='.$this->cloudappliance->id;
					$cloudappliance_action .= $a->get_string();
					// deprovision
					$a = $this->response->html->a();
					$a->title   = $this->lang['appliances']['action_deprovision'];
					$a->label   = '<span title="Remove" class="editvolumesm" ><i class="fa fa-close"></i></span>';
					$a->handler = "";
					$a->css     = '';
					$a->href    = $this->response->get_url($this->actions_name, 'deprovision').'&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
					$cloudappliance_action .= $a->get_string();
				}

				$disk = intval($image_size);
				if( $disk >= 1000 ) {
					$disk = round(($disk / 1000), 2, PHP_ROUND_HALF_DOWN).' GB';
				} else {
					$disk = $disk .' MB';
				}

				if (preg_match('@/htvcenter/base/@', $_SERVER['REQUEST_URI'])) {
					$config_column  = '<div class="appnamer panel-heading"><h3 class="panel-title">'.$appliance->name.'</h3></div>';
				} else {
					$config_column  = '';
				}
				$config_column  .= '<b>'.$this->lang['appliances']['id'].'</b> '.$this->cloudrequest->id.'<br>';
				$config_column .= '<b>'.$this->lang['create']['hostname'].'</b> <span class="hostnamee">'.$appliance->name.'</span><br>';
				$config_column .= '<b>'.$this->lang['create']['type'].'</b> '.$virtualization->name.'<br>';
				$config_column .= '<b>'.$this->lang['create']['cpu'].'</b> '.$str_cpu.'<br>';
				$config_column .= '<b>'.$this->lang['create']['ram'].'</b> '.$str_ram.'<br>';
				$config_column .= '<b>'.$this->lang['create']['kernel'].'</b> '.$kernel->name.'<br>';
				$config_column .= '<b>'.$this->lang['create']['disk'].'</b> '.$disk.'<br>';
				$config_column .= '<b>'.$this->lang['create']['image'].'</b> '.$image->name.'<br>';
				$config_column .= '<b>IP2</b> '.$appliance_resources_str;

				$comment = $appliance->comment.'<hr>'.$plugin_action;
				if($cloudappliance_state === 'busy') {
					$comment = $this->lang['appliances']['error_command_running'];
					$comment = $appliance->comment.'<hr><div class="busy_appliance">&#160;</div>';
				}
				if($cloudappliance_state === 'active') {
					$cdrom = '<a class="plugin cdrom">Insert CD</a>';
					$cdrom .= '<a class="plugin cdromeject" style="display:none">Eject CD</a>';
				}
				#$comment .= '<textarea id="'.$this->cloudrequest->id.'" style="height:60px;width:95%;font-size:10px;"></textarea><script>get_state("'.$this->cloudrequest->id.'");</script>';
				if($cloudappliance_state === 'active') {
					$cloudappliance_action .= '<span title="Edit volumes" class="editvolumesm editvolumesmpopup" ><i class="fa fa-hdd-o"></i></span>';
				}
				$ta[] = array(
					'id' => $this->cloudappliance->id,
					'state' => '<span class="pill '.$cloudappliance_state.'">'.$cloudappliance_state.'</span>',
					'name' => $appliance->name,
					'type' => $virtualization->name,
					'cpu' => $this->cloudrequest->cpu_req,
					'ram' => $this->cloudrequest->ram_req,
					'disk' => $image_size,
					'kernel' => $kernel->name,
					'image' => $image->name,
					'config' => $config_column,
					'comment' => $comment.$cdrom,
					'action' => $cloudappliance_action,
				);
				$app_count++;
			} else {
				$this->cloudrequest->get_instance_by_id($reqid);
				$state = $this->cloudrequest->getstatus($this->cloudrequest->id);
				if($state !== 'done') {
					$kernel = $this->htvcenter->kernel();
					if(isset($this->cloudrequest->kernel_id) && $this->cloudrequest->kernel_id !== '') {
						$kernel->get_instance_by_id($this->cloudrequest->kernel_id);
					}
					$image = $this->htvcenter->image();
					if(isset($this->cloudrequest->image_id) && $this->cloudrequest->image_id !== '') {
						$image->get_instance_by_id($this->cloudrequest->image_id);
					}
					$virtualization = $this->htvcenter->virtualization();
					if(isset($this->cloudrequest->resource_type_req) && $this->cloudrequest->resource_type_req !== '') {
						$virtualization->get_instance_by_id($this->cloudrequest->resource_type_req);
					}

					$disk = intval($this->cloudrequest->disk_req);
					if( $disk >= 1000 ) {
						$disk = round(($disk / 1000), 2, PHP_ROUND_HALF_DOWN).' GB';
					} else {
						$disk = $disk .' MB';
					}

					$config_column  = '<b>'.$this->lang['appliances']['id'].'</b> '.$this->cloudrequest->id.'<br>';
					$config_column .= '<b>'.$this->lang['create']['hostname'].'</b> '.$this->cloudrequest->appliance_hostname.'<br>';
					$config_column .= '<b>'.$this->lang['create']['type'].'</b> '.$virtualization->name.'<br>';
					$config_column .= '<b>'.$this->lang['create']['cpu'].'</b> '.$str_cpu.'<br>';
					$config_column .= '<b>'.$this->lang['create']['ram'].'</b> '.$str_ram.'<br>';
					$config_column .= '<b>'.$this->lang['create']['kernel'].'</b> '.$kernel->name.'<br>';
					$config_column .= '<b>'.$this->lang['create']['disk'].'</b> '.$disk.'<br>';
					$config_column .= '<b>'.$this->lang['create']['image'].'</b> '.$image->name.'<br>';
					$config_column .= '<b>IP</b>';

					$comment = '';
					if(isset($this->lang['appliances']['info_'.$state])) {
						$comment = $this->lang['appliances']['info_'.$state];
					}
					$ta[] = array(
						'id' => $this->cloudrequest->id,
						'state' => '<span class="pill '.$state.'">'.$state.'</span>',
						'name' => $this->cloudrequest->appliance_hostname,
						'type' => $virtualization->name,
						'cpu' => $this->cloudrequest->cpu_req,
						'ram' => $this->cloudrequest->ram_req,
						'disk' => $this->cloudrequest->disk_req,
						'kernel' => $kernel->name,
						'image' => $image->name,
						'config' => $config_column,
						'comment' => $comment,
						'action' => '',
					);
					$app_count++;
				}
			}
		}

		// redirect if $ta is empty
		if(count($ta) > 0) {
			$table = $this->response->html->tablebuilder( 'cloud_table', $this->response->get_array($this->actions_name, 'appliances'));
			$table->css          = 'htmlobject_table';
			$table->limit        = 10;
			$table->id           = 'cloud_appliances';
			$table->head         = $h;
			$table->sort         = 'state';
			$table->autosort     = true;
			$table->sort_link    = false;
			$table->actions_name = $this->actions_name;
			$table->form_action  = $this->response->html->thisfile;
			$table->form_method  = 'GET';
			$table->max          = $app_count;
			$table->body         = $ta;
			return $table;
		} else {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'create', $this->message_param, $this->lang['appliances']['msg_no_appliances_to_manage'])
			);
		}
	}

	//--------------------------------------------
	/**
	 * Get data as array
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function overview() {
		$return = array();
		$ids    = $this->cloudrequest->get_all_ids_per_user($this->clouduser->id);
		$i      = 0;
		foreach($ids as $id) {
			$this->cloudrequest->get_instance_by_id($id['cr_id']);
			$return[$i]['cr_id'] = $id['cr_id'];
 			$return[$i][$this->lang['create']['cpu']]      = $this->cloudrequest->cpu_req;
 			$return[$i][$this->lang['create']['ram']]      = $this->cloudrequest->ram_req;
 			$return[$i][$this->lang['create']['disk']]     = $this->cloudrequest->disk_req;
 			$return[$i][$this->lang['create']['hostname']] = $this->cloudrequest->appliance_hostname;
			$return[$i][$this->identifier_name] = $this->cloudappliance->get_id_by_cr($id['cr_id']);
			#$this->response->html->help($this->cloudrequest);
			$i++;
		}

		return $return;
	}

}
?>
