<?php
/**
 * chatbot select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class chatbot_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'chatbot_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "chatbot_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'chatbot_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'chatbot_identifier';
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

public $config = array();

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
		$this->htvcenter  = $htvcenter;
		$this->file       = $htvcenter->file();
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->tpldir   = $this->rootdir.'/plugins/chatbot/tpl';
		$this->statfile  = $this->htvcenter->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$response = $this->select();
		$t = $this->response->html->template($this->tpldir.'/chatbot-select.tpl.php');
		$t->add('Maestro ChatBot', 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		
		$contents = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config');
		$config = unserialize($contents);

		if (!empty($config['slack_token'])) {
			$t->add($config['slack_token'], 'slack');
		} else { 
			$t->add('', 'slack');
		}

		if (!empty($config['useralert'])) {
			$t->add($config['useralert'], 'useralert');
		} else {
			$t->add('', 'useralert');
		}

		if (!empty($config['ip'])) {
			$t->add($config['ip'], 'ip');
		} else {
			$t->add($_SERVER['SERVER_NAME'], 'ip');
		}
		
		if (!empty($config['port'])) {
			$t->add($config['port'], 'port');
		} else {
			$t->add($_SERVER['SERVER_PORT'], 'port');
		}

		if (!empty($config['htaccess']['login'])) {
			$t->add($config['htaccess']['login'], 'login');
		} else {
			$t->add('htvcenter', 'login');
		}

		if (!empty($config['htaccess']['password'])) {
			$t->add($config['htaccess']['password'], 'password');
		} else {
			$t->add('htvcenter', 'password');
		}

		if (!empty($config['state'])) {
			$t->add($config['state'], 'state');
			if ($config['state'] == 'false') {
				$t->add('Start the ChatBot', 'buttontext');
			} else {
				$t->add('Stop the ChatBot', 'buttontext');
			}
			
		} else {
			$t->add( 'false', 'state');
			$t->add('Start the ChatBot', 'buttontext');
		}

		
		
		
		$this->config = $config;
		return $t;

	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->response;
		$htvcenter = $this->htvcenter;
			
			if ( isset($_POST['login']) && isset($_POST['password']) && isset($_POST['ip']) ) {
				if (isset($_POST['ip'])) {
					$config['ip'] = $_POST['ip'];
				}

				if (isset($_POST['port'])) {
					$config['port'] = $_POST['port'];
				}

				if (isset($_POST['login'])) {
					$config['htaccess']['login'] = $_POST['login'];
				}

				if (isset($_POST['password'])) {
					$config['htaccess']['password'] = $_POST['password'];
				}

				if (isset($_POST['slack'])) {
					$config['slack_token'] = $_POST['slack'];
				}

				if (isset($_POST['useralert'])) {
					$config['useralert'] = $_POST['useralert'];
				}

				if (isset($_POST['state'])) {
					$res = new resource();
					if ($_POST['state'] == 'false') {
						$config['state'] = 'true';
						$res->send_command('localhost', '/usr/share/htvcenter/plugins/chatbot/thebot/start');
					} else {
						$config['state'] = 'false';
						//$res->send_command('localhost', 'sudo /usr/share/htvcenter/plugins/chatbot/thebot/kill');
					}
				}

				file_put_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config', serialize($config));
			}

			if ( isset($_GET['cmd']) ) {
				switch($_GET['cmd']) {
					case 'vminfo':
						$info = $this->botApiVminfo($htvcenter, $response);
						break;

					case 'vmlist':
						$info = $this->botApiVmList($htvcenter, $response);
						break;

					case 'hddinfo':
						$info = $this->botApiHddinfo($htvcenter, $response);
						break;

					case 'hostsinfo':
						$info = $this->botApiHostsinfo($htvcenter, $response);
						break;

					case 'serversinfo':
						$info = $this->botApiServersinfo($htvcenter, $response);
						break;

					case 'controllerinfo':
						$info = $this->botApiControllerinfo($htvcenter, $response);
						break;

					case 'memoryinfo':
						$info = $this->botApiMemoryinfo($htvcenter, $response);
						break;

					case 'networksinfo':
						$info = $this->botApiNetworksinfo($htvcenter, $response);
						break;

					case 'iprange':
						$name = $_GET['netname'];
						$info = $this->botApiNetdetails($htvcenter, $response, $name);
						break;

					case 'networkdetails':
						$name = $_GET['netname'];
						$info = $this->botApiNetdetails($htvcenter, $response, $name);
						break;

					case 'netipnumber':
						$name = $_GET['netname'];
						$info = $this->botApiIpnumber($htvcenter, $response, $name);
						break;

					case 'stopvm':
						$name = $_GET['netname'];
						$info = $this->botApiStopVM($htvcenter, $response, $name);
						break;

					case 'startvm':
						$name = $_GET['netname'];
						$info = $this->botApiStartVM($htvcenter, $response, $name);
						break;

					case 'removevm':
						$name = $_GET['netname'];
						$info = $this->botApiRemoveVM($htvcenter, $response, $name);
						break;

					case 'checkvm':
						$name = $_GET['netname'];
						$info = $this->botApiCheckVM($name);
						break;

					case 'haenable':
						$name = $_GET['netname'];
						$info = $this->botApiEnableHAVM($htvcenter, $response, $name);
						break;

					case 'hadisable':
						$name = $_GET['netname'];
						$info = $this->botApiDisableHAVM($htvcenter, $response, $name);
						break;

					case 'vmnetwork':
						$name = $_GET['netname'];
						$info = $this->botApiVmNetwork($htvcenter, $response, $name);
						break;

					case 'cloudcharge':
						$user = $_GET['user'];
						$month = $_GET['month'];
						$contents = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config');
						$config = unserialize($contents);
						$info = $this->botApiCharge($user, $month, $config);
						$info = json_decode($info);
						break;

					case 'checkalerts':
						$info = $this->botApiCheckalerts($htvcenter, $response);
						break;

					case 'inactivevms':
						$info = $this->botApiInactiveVMs($htvcenter, $response);
						break;
					
					case 'image':
						$info = $this->botApiImages($htvcenter, $response);
						break;
					case 'cpuinfo':
						$server_name = $_GET['servername'];
						$info = $this->botCPUInfo($htvcenter, $response, $server_name);
						break;
					case 'createvm':
						$params = unserialize($_GET['params']);
						$vmname = $params['vmname'];
						$vmsize = $params['vmsize'];
						$vncpass = $params['vncpass'];
						$hostname = $params['hostname'];
						$info = $this->botCreateVM($htvcenter, $response, $vmname, $vmsize, $vncpass, $hostname);
						break;
				}

				$res = json_encode($info);
				echo $res;
				die();
			}


			
		
		return $response;
	}

	public function botApiVminfo($htvcenter, $response) {
		require_once('/usr/share/htvcenter/web/base/server/aa_server/class/datacenter.dashboard.class.php');
		$dash = new datacenter_dashboard($htvcenter, $response);
		$info = $dash->vmmaincount();
		return $info;
	}

	public function botApiVmList($htvcenter, $response) {
		$vmlist = [];
		$appliance = new appliance();
		$appliances = $appliance->display_overview(0, 10000, 'appliance_id', 'ASC');
		foreach($appliances as $index => $appliance_db){
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
			$appliance_virtualization_name = $virtualization->name;			
			$vmlist[] = array('appliance_id' => $appliance_db['appliance_id'], 'appliance_name' => $appliance_db['appliance_name'], 'appliance_virtualization' => $appliance_virtualization_name);
		}
		return $vmlist;
	}
	
	public function botCPUInfo($htvcenter, $response, $server_name) {
		$cpuarray = [];
		$appliance = new appliance();
		$appliances = $appliance->display_overview(0, 10000, 'appliance_id', 'ASC');
		
		foreach($appliances as $index => $appliance_db){
			if($server_name == $appliance_db['appliance_name']){
				$appliance = new appliance();
				$appliance->get_instance_by_id($appliance_db["appliance_id"]);
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				$cpuarray[] = array('cpu_number' => $resource->cpunumber);
			}
		}
		return $cpuarray;
	}
	
	public function botCreateVM($htvcenter, $response, $vmname, $vmsize, $vncpass, $hostname){
		$vmname = trim($vmname);
		$vmsize = trim($vmsize);
		$vncpass = trim($vncpass);
		$hostname = trim($hostname);
		
		$err_array = array();
		$inf_array = array();
		array_push($inf_array, $vmname);
		array_push($inf_array, $vmsize);
		array_push($inf_array, $vncpass);
		array_push($inf_array, $hostname);
		
		if(empty($vmname)){
			array_push($err_array, "Virtual machine name can not be empty.");
		}
		if(empty($hostname)){
			array_push($err_array, "Host name can not be empty.");
		}
		
		$resource = new resource();
		$resource->get_instance_id_by_hostname($hostname);
		$rs = new resource();
		if($resource->id == ""){
			array_push($err_array, "Host name does not exists. VM creation terminated.");
		}
		if ($this->file->exists($this->statfile)) {
			$lines = explode("\n", $this->file->get_contents($this->statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						$check = $line[1];
						if($vmname === $check) {
							$errors[] = sprintf($this->lang['error_exists'], $vmname);
							array_push($err_array, $vmname." can not be used. VM creation terminated.");
						}
					}
				}
			}
		}
		if(count($err_array) == 0){
			$rs->get_instance_by_id($resource->id);
			$vtype = $rs->vtype;
			$rIP = $rs->ip;
			$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$ip = rand(11, 1111);
			$CMD="(date; cat /proc/interrupts) | md5sum | sed -r 's/^(.{10}).*\$/\\1/; s/([0-9a-f]{2})/\\1:/g; s/:\$//;' | tr '[:lower:]:' '[:upper:]-' | sed -e 's/-/:/g'";
			$GEN_MAC=exec($CMD);
			$GEN_MAC="00:".$GEN_MAC;
			$mac = strtolower($GEN_MAC);

			$htvcenter = new htvcenter_server();
			$htvcenter->send_command('htvcenter_server_add_resource '.$id.' '.$mac.' '.$ip);
			array_push($inf_array, "Resource added to Maestro Controller.");

			$res = new resource();
			$fields["resource_id"] = $id;
			$fields["resource_ip"] = $ip;
			$fields["resource_mac"] = $mac;
			$fields["resource_localboot"] = 0;
			$fields["resource_vtype"] = $vtype;
			$fields["resource_vhostid"] = $rs->vhostid;
			$fields["resource_vname"] = $vmname;
			$res->add($fields);
			if($id){
				array_push($inf_array, "Resource added into htvcenter database.");
			} else {
				array_push($err_array, "Resource are not added into htvcenter database.");
			}
		
			$vnc = $vncpass;
			$vnckeymap = 'en-us';
			$disk_interface_parameter = ' -o virtio';
			$vnckeymap_parameter = ' -l en-us';

			$command  = $this->htvcenter->get('basedir').'/plugins/kvm/bin/htvcenter-kvm-vm create';
			$command .= ' -n '.$vmname;
			$command .= ' -y kvm-vm-local';
			$command .= ' -m '.$mac;
			$command .= ' -r 1024';
			$command .= ' -c 1';
			$command .= ' -t virtio';
			$command .= ' -z br0';

			$command .= ' -b local';
			$command .= ' -v '.$vncpass;
			$command .= $iso_path;
			$command .= $vnckeymap_parameter;
			$command .= $disk_interface_parameter;
			$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode regular';
		
			//echo "Command: " . $command;
			$comRes = new resource();
			$comRes->send_command($rIP, $command);
			array_push($inf_array, "Virtual machine created successfully. VM ID: " . $id);

			$app = new appliance();
			$now=$_SERVER['REQUEST_TIME'];
			$appliance_name = str_replace("_", "-", strtolower(trim($vmname)));
			$new_appliance_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$afields['appliance_id'] = $new_appliance_id;
			$afields['appliance_name'] = $appliance_name;
			$afields['appliance_resources'] = $id;
			$afields['appliance_kernelid'] = '1';
			$afields['appliance_imageid'] = '1';
			$afields["appliance_virtual"]= 0;
			$afields["appliance_virtualization"]=$vtype;
			$afields['appliance_wizard'] = '';
			$afields['appliance_comment'] = 'KVM VM for resource '.$id;
			$app->add($afields);
			if($appliance_name){
				array_push($inf_array, "Appliance infomration added into htvcenter database.");
			} else {
				array_push($err_array, "Failed to add appliance information into database.");
			}
			$aufields['appliance_stoptime']=$now;
			$aufields['appliance_starttime']='';
			$aufields['appliance_state']='stopped';
			$app->update($new_appliance_id, $aufields);
			if($new_appliance_id){
				array_push($inf_array, "Appliance infomration updated into htvcenter database.");
			} else {
				array_push($err_array, "Failed to update appliance information into database.");
			}
		}
		if(count($err_array) == 0){
			return $inf_array;
		} else {
			return $err_array; //array($vmname, $vmsize, $vncpass, $hostname, "Error"); //$command;
		}
	}
	
	public function botApiImages($htvcenter, $response){
		$image = new image();
		$image_arr = $image->display_overview(0, 10000, 'image_id', 'ASC');
		return $image_arr;
	}

	public function botApiHddinfo($htvcenter, $response) {
		require_once('/usr/share/htvcenter/web/base/server/aa_server/class/datacenter.dashboard.class.php');
		$dash = new datacenter_dashboard($htvcenter, $response);
		$info = $dash->storagetaken();
		$info['sfree'] = str_replace('<b>', '', $info['sfree']);
		$info['sfree'] = str_replace('</b>', '', $info['sfree']);
		$info['spercent'] = str_replace('%', '', $info['spercent']);
		return $info;
	}

	public function botApiHostsinfo($htvcenter, $response) {
		require_once('/usr/share/htvcenter/web/base/server/resource/class/resource.select.class.php');
		$res = new resource_select($htvcenter, $response);
		$rez = $res->hostsinfo();
		$info['hosts'] = $rez['hosts'];
		return $info;
	}

	public function botApiServersinfo($htvcenter, $response) {
		$info['hostsinfo'] = $this->botApiHostsinfo($htvcenter, $response);
		$info['storageinfo'] = $this->botApiHddinfo($htvcenter, $response);
		$info['vminfo'] = $this->botApiVminfo($htvcenter, $response);
		return $info;
	}

	public function botApiControllerinfo($htvcenter, $response) {
		require_once('/usr/share/htvcenter/web/base/server/aa_server/class/datacenter.dashboard.class.php');
		$dash = new datacenter_dashboard($htvcenter, $response);
		$info = $dash->controllerinfo();
		return $info;
	}

	public function botApiMemoryinfo($htvcenter, $response) {
		/*require_once('/usr/share/htvcenter/web/base/server/resource/class/resource.select.class.php');
		$res = new resource_select($htvcenter, $response);
		$info = $res->hostsinfo();
		return $info;*/
		
		$memarray = [];
		$appliance = new appliance();
		$appliances = $appliance->display_overview(0, 10000, 'appliance_id', 'ASC');
		
		foreach($appliances as $index => $appliance_db){
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);
			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);
			if($resource->memtotal){
				$memarray[] = $appliance->name . ' : '.$resource->memtotal;
			}
		}
		return $memarray;
	}

	public function botApiNetworksinfo($htvcenter, $response) {
		require_once('/usr/share/htvcenter/plugins/ip-mgmt/web/class/ip-mgmt.select.class.php');
		$net = new ip_mgmt_select($htvcenter, $response);
		$info = $net->select(true);
		return $info;
	}

	public function botApiNetdetails($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/plugins/ip-mgmt/web/class/ip-mgmt.details.class.php');
		$netdet = new ip_mgmt_details($htvcenter, $response);
		$info = $netdet->details($name);
		return $info;
	}

	public function botApiIpnumber($htvcenter, $response, $name) {
		$info = $this->botApiNetdetails($htvcenter, $response, $name);
		$count = 0;
		foreach ($info['ips'] as $ip) {
			if (empty($ip['ip_mgmt_appliance_id'])) {
				$count = $count + 1;
			}
		}
		$res['range'] = $info['range'];
		$res['ip_available'] = $count;
		return $res;
	}

	public function botApiStopVM($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		require_once('/usr/share/htvcenter/web/base/server/appliance/class/appliance.stop.class.php');
		
		$apl = new appliance();
		$apli = new appliance_stop($htvcenter, $response);
		
		$apliance = $apl->get_instance_by_name($name);
		$apli->stopApi($apliance);
		$rez['done'] = true;
		return $rez;
	}


	public function botApiRemoveVM($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		require_once('/usr/share/htvcenter/web/base/server/appliance/class/appliance.remove.class.php');
		
		$apl = new appliance();
		$apli = new appliance_remove($htvcenter, $response);
		$apliance = $apl->get_instance_by_name($name);
		$apli->removeApi($apliance);
		$rez['done'] = true;
		return $rez;
	}

	public function botApiStartVM($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		require_once('/usr/share/htvcenter/web/base/server/appliance/class/appliance.start.class.php');
		
		$apl = new appliance();
		$apli = new appliance_start($htvcenter, $response);
		
		$apliance = $apl->get_instance_by_name($name);
		$apli->startApi($apliance);
		$rez['done'] = true;
		return $rez;
	}

	public function botApiCheckVM($name) {
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		$apl = new appliance();
		$apliance = $apl->get_instance_by_name($name);
		$rez['state'] = $apliance->state;
		return $rez;
	}

	public function botApiEnableHAVM($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/plugins/highavailability/web/class/highavailability.controller.class.php');
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		
		$apl = new appliance();
		$apliance = $apl->get_instance_by_name($name);
		$id = $apliance->id;

		$ha = new highavailability_controller($htvcenter, $response);
		$info['enable'] = $ha->enable($id);
		return $info;
	}

	public function botApiDisableHAVM($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/plugins/highavailability/web/class/highavailability.controller.class.php');
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		
		$apl = new appliance();
		$apliance = $apl->get_instance_by_name($name);
		$id = $apliance->id;

		$ha = new highavailability_controller($htvcenter, $response);
		$info['enable'] = $ha->disable($id);
		return $info;
	}


	public function botApiVmNetwork($htvcenter, $response, $name) {
		require_once('/usr/share/htvcenter/plugins/ip-mgmt/web/class/ip-mgmt.details.class.php');
		require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
		$netdet = new ip_mgmt_details($htvcenter, $response);
		$info = $netdet->details($name);
		
		$vmids = [];
		foreach ($info['details'] as $key => $value) {
			if ($key == 'ip_mgmt_appliance_id' && !empty($value)) {
				$vmids[] = $value;
			}
		}

		
		if (count($vmids) > 0) {
			$apl = new appliance();
			foreach ($vmids as $id) {
				$vm = $apl->get_instance_by_id($id);
				$rez['vms'][] = $vm->name;
			}
		} else {
			$rez['vms'] = 'NULL';
		}
		return $rez;
	}

	public function botApiCharge($user, $month, $config) {
		$url = 'http://'.$config['ip'].':'.$config['port'].'/cloud-fortis/user/index.php?report=yes&month='.$month.'&user='.$user.'&chatbot=true&forbill=true';

		$username = $config['htaccess']['login'];
		$password = $config['htaccess']['password'];
 
		$context = stream_context_create(array(
		    'http' => array(
		        'header'  => "Authorization: Basic " . base64_encode("$username:$password")
		    )
		));
		$res = file_get_contents($url, false, $context);
		return $res;
	}

	public function botApiCheckalerts($htvcenter, $response) {
		$controller = $this->botApiControllerinfo($htvcenter, $response);
		$hdd = $this->botApiHddinfo($htvcenter, $response);
		$memory = $controller['memory'];
		$res = array();


		$oneperc = $memory['available']/100;
		$perc = $memory['used']/$oneperc;
		$perc = round($perc);
		

		if ($hdd['spercent'] > 85) {
			$res['hdd'] = $hdd['spercent'];
		} else {
			$res['hdd'] = 'ok';
		}

		if ($perc > 85) {
			$res['memory'] = $perc;
		} else {
			$res['memory'] = 'ok';
		}

		
		return $res;
	}

	public function botApiInactiveVMs($htvcenter, $response) {
		require_once('/usr/share/htvcenter/web/base/class/htvcenter.controller.class.php');
		$controller = new htvcenter_controller($htvcenter, $response);
		$res = $controller->applianceselect();
		$res = json_decode($res);
		return $res;
	}

	
	
}
?>
