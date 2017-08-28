<?php
/**
 * htvcenter Controller
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_controller
{
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'content' => array(
		'tab' => 'License',
		'ltimeout' => 'Your htvcenter license has expired!',
		'lclients' => 'Your htvcenter client license has expired!',
	),
	'top' => array(
		'account' => 'Account',
		'support' => 'Support',
		'info' => 'Info',
		'documentation' => 'Documentation',
		'language' => 'Language',
	),
	'upload' => array(
		'tab' => 'Upload',
		'label' => 'Upload License File(s)',
		'public_key' => 'Public Key',
		'server_license' => 'Server License',
		'client_license' => 'Client Licenses (optional)',
		'welcome' => 'Welcome to your newly installed HyperTask Enterprise Edition',
		'explanation' => 'Please activate it by uploading the license key files you received by email.',
		'msg' => 'Uploaded License File %s',
	),
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct() {

		// handle timezone needed since php 5.3
		if(function_exists('ini_get')) {
			if(ini_get('date.timezone') === '') {
				date_default_timezone_set('Europe/Berlin');
			}
		}

		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base';
		$this->tpldir = $this->rootdir.'/tpl';

		require_once($this->rootdir.'/class/file.handler.class.php');
		$file = new file_handler();

		require_once($this->rootdir.'/class/htmlobjects/htmlobject.class.php');
		require_once($this->rootdir.'/class/htvcenter.htmlobjects.class.php');
		$html = new htvcenter_htmlobject();

		// if htvcenter is unconfigured, set htvcenter empty
		if ($file->exists($this->rootdir.'/unconfigured')) {
			$this->htvcenter = '';
			$this->webdir  = $this->rootdir;
			$this->baseurl = $html->thisurl;
		} else {
			require_once($this->rootdir.'/class/user.class.php');
			$user = new user($_SERVER['PHP_AUTH_USER']);
			$user->set_user();
			require_once($this->rootdir.'/class/htvcenter.class.php');
			$this->htvcenter = new htvcenter($file, $user, $html->response());
			$this->webdir  = $this->htvcenter->get('webdir');
			$this->baseurl = $this->htvcenter->get('baseurl');
		}

		// only translate if htvcenter is not empty (configure mode)
		if($this->htvcenter !== '') {
			$html->lang = $user->translate($html->lang, $this->rootdir."/lang", 'htmlobjects.ini');
			$file->lang = $user->translate($file->lang, $this->rootdir."/lang", 'file.handler.ini');
			$this->lang = $user->translate($this->lang, $this->rootdir."/lang", 'htvcenter.controller.ini');
		}

		require_once $this->rootdir.'/include/requestfilter.inc.php';
		$request = $html->request();
		$request->filter = $requestfilter;

		$this->response = $html->response();
		$this->request  = $this->response->html->request();
		$this->file     = $file;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {



		$ti = microtime(true);
		
		// get js translation file;
		// EN file serves master key for language labels
		$file = $this->rootdir.'/lang/en.javascript.ini'; // 'en.' prefix is needed and must not be renamed
		$jstranslation = '';
		if($this->file->exists($file)) {
			$lang = $this->file->get_ini($file);
			// only translate if htvcenter is not empty (configure mode)
			if($this->htvcenter !== '') {
				$lang = $this->htvcenter->user()->translate($lang, $this->rootdir."/lang", 'javascript.ini');
			}			
			$jstranslation  = '<script type="text/javascript">'."\n";
			$jstranslation .= '//<![CDATA['."\n";
			$jstranslation .= 'var jstranslation = {'."\n";
			$i = 0;
			foreach($lang as $key => $value) {
				$jstranslation .= $key.': "'.$value."\"";	// build js array
				if($i < count($lang)-1) {
					$jstranslation .= ",\n";
				}
				$i++;
			}
			$jstranslation .= "\n".'};'."\n";
			$jstranslation .= '//]]>'."\n";
			$jstranslation .= '</script>'."\n";
		}

		// handle scripts and stylesheets
		$style = '';
		$script = '';
		$basetarget = '<base target="MainFrame"></base>';


		
		if($this->request->get('plugin') !== '') {
			$plugin = $this->request->get('plugin');
			$style  = $this->__renderAssetInclude( '/plugins/'.$plugin.'/css/', 'css' );
			$script = $this->__renderAssetInclude( '/plugins/'.$plugin.'/js/', 'js' );
			$basetarget = '';
			$this->response->add('plugin', $plugin);
			if($this->request->get('controller') !== '') {
				$this->response->add('controller', $this->request->get('controller'));
			}
		}
		else if ( $this->request->get('base') !== '') {

			$plugin = $this->request->get('base');
			$style  = $this->__renderAssetInclude( '/server/'.$plugin.'/css/', 'css' );
			$script = $this->__renderAssetInclude( '/server/'.$plugin.'/js/', 'js' );
			$basetarget = '';
			$this->response->params['base'] = $plugin;
			if($this->request->get('controller') !== '') {
				$this->response->params['controller'] = $this->request->get('controller');
			}
		} else {
			$plugin = 'aa_server';
			$style  = $this->__renderAssetInclude( '/server/'.$plugin.'/css/', 'css' );
			$script = $this->__renderAssetInclude( '/server/'.$plugin.'/js/', 'js' );
			$basetarget = '';
		}


		

		if ($this->request->get('action') == 'todo') {
			$method = $this->request->get('method');
			$id = $this->request->get('taskid');



			if ( ($method == 'save' || $method == 'remove' || $method == 'removebytext')) {
					$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
					require_once ($RootDir.'include/htvcenter-database-functions.php');
					$db = htvcenter_get_db_connection();

				if ($method == 'save') {

					$text = $this->request->get('tasktext');

					if (!empty($text)) {
						$text = strip_tags($text);
						$sql = "INSERT INTO `todolist`(`task`) VALUES ('$text')";
							
						$rez = $db->Execute($sql);
					}
				}

				if ($method == 'remove') {
					if (is_numeric($id)) {
						$sql = "DELETE FROM `todolist` WHERE `id` = $id";
						$rez = $db->Execute($sql);
					}
				}

				if ($method == 'removebytext') {
					$text = $this->request->get('tasktext');
					if (!empty($text)) {
						$text = strip_tags($text);
						$sql = "DELETE FROM `todolist` WHERE `task` = '$text'";
						$rez = $db->Execute($sql);
					}
				}
			}
		}

		//require_once($this->rootdir.'/class/storage.class.php');
		//$ddd = new storage($this->htvcenter, $this->response);
		//var_dump($ddd);
		//$res = $ddd->storage();
			
		



		$t = $this->response->html->template($this->tpldir.'/index.tpl.php');
		
			
		$t = $this->response->html->template($this->tpldir.'/index.tpl.php');

		if (isset($_GET) && $_GET['inactive'] == 'yes') {
			
			if (isset($_POST)) {
				$user = $this->htvcenter->user()->name;
				$spl = $_POST['inactivespl'];
				$action = $_POST['explorerajax'];
			}

			if ( ($spl == '1') && ($action == 'getinactive') ) {
				$rez = $this->applianceselect();
				echo $rez; die();
			}



		}
		 
		/*
		if (isset($_GET) && $_GET['report'] == 'yes') {
			
			if (isset($_POST)) {
				$user = $this->htvcenter->user()->name;
				$month = $_POST['month'];
				$year = $_POST['year'];
				$priceonly = $_POST['priceonly'];
				$detailcategory = $_POST['detailcategory'];
				$forbill = $_POST['forbill'];
				$forcsv = $_POST['forcsv'];
			}


			if (!empty($month) && !empty($year)) {
				
				$query = "SELECT `cu_id` FROM `cloud_users` WHERE `cu_name`=\"".$user."\"";
				$res = mysql_query($query);
				$res = mysql_fetch_row($res);
				$userid = $res[0];

				mysql_select_db('cloud_transaction');
				$query = "SELECT `ct_time`, `ct_ccu_charge`, `ct_ccu_balance`, `ct_comment` FROM `cloud_transaction` WHERE `ct_cu_id`=\"".$userid."\"";


				$res = mysql_query($query);
				$ccu =0;
				$detailtable = '<table class="table table-striped table-bordered"><tr class="info"><td>Date</td><td>CCU</td><td>Comment</td></tr>';

				$vmpoints = 0;
				$netpoints = 0;
				$rampoints = 0;
				$storagepoints = 0;
				$cpupoints = 0;

				while ($rez = mysql_fetch_assoc($res)) {



					$timestamp=$rez['ct_time'];
					$yeardb = gmdate("Y", $timestamp);
					$monthdb = gmdate("M", $timestamp);
					if ( ($year == $yeardb) && ($month == $monthdb) ) {
						$ccu = $ccu + $rez['ct_ccu_charge'];
						$tabledate = gmdate("Y-m-d h:i", $timestamp);
						$detailtable .= '<tr class="headertr"><td>'.$tabledate.'</td><td>'.$rez['ct_ccu_charge'].'</td><td>'.$rez['ct_comment'].'</td></tr>';

						if ($detailcategory == true || $forbill == true) {
							$ccu = (int) $rez['ct_ccu_charge'];
							$done = 0;
							if ( preg_match('@CPU@',$rez['ct_comment']) && ($done == 0) ) {
								$cpupoints = $cpupoints + $ccu;
								$done = 1;
							}

							if ( ((preg_match('@Memory@',$rez['ct_comment'])) || (preg_match('#RAM#', $rez['ct_comment'])) ) && ($done == 0) ) {
								$rampoints = $rampoints + $ccu;
								$done = 1;
							}

							if ( ((preg_match('@Disk Space@',$rez['ct_comment'])) || (preg_match('@MB@',$rez['ct_comment'])) || (preg_match('@GB@',$rez['ct_comment'])) || (preg_match('@storage@',$rez['ct_comment'])) ) && ($done == 0) ) {
								$storagepoints = $storagepoints + $ccu;
								$done = 1;
							}

							if ( ((preg_match('@Kernel@',$rez['ct_comment'])) || (preg_match('#KVM#', $rez['ct_comment'])) || (preg_match('#VM#', $rez['ct_comment'])) ) && ($done == 0)  ) {
								$vmpoints = $vmpoints + $ccu;
								$done = 1;
							}

							

							if ( (preg_match('@Network@',$rez['ct_comment']) ) && ($done == 0) ) {
								$netpoints = $netpoints + $ccu;
								$done = 1;
							}

						}
					}
				}
				$jsonarr = array();
				$jsonarr['memory'] = $rampoints;
				$jsonarr['network'] = $netpoints;
				$jsonarr['virtualisation'] = $vmpoints;
				$jsonarr['storage'] = $storagepoints;
				$jsonarr['cpu'] = $cpupoints;
				$jsonarr['sum'] = $rampoints + $netpoints + $vmpoints + $storagepoints + $cpupoints;

				//var_dump($jsonarr);

				$detailtable .= '</table>';
				$categoryresult = json_encode($jsonarr);
				mysql_select_db('htvcenter');

				$query = "SELECT `cc_value` FROM `cloud_config` WHERE `cc_key`='cloud_1000_ccus'";
				$res = mysql_query($query);
				$rez = mysql_fetch_row($res);
				
				$price = $rez[0];

				if ($forbill == true) {
					$ccu = $cpupoints + $storagepoints + $rampoints + $vmpoints + $netpoints;
				}
				
				$cost = $ccu/1000*$price;
				$cost = round($cost, 2);
				
				if (preg_match('@\.@', $cost)) {
					$cosa = explode('.', $cost);
					if (strlen($cosa[1]) == 1) {
						$cost = $cost.'0';
					}
				} else {
					$cost = $cost.'.00';
				}



				if ( $forbill == true ) {
					$billjson = array();
					
					$billjson['cpu'] = round($cpupoints/1000*$price, 2);
					$billjson['storage'] = round($storagepoints/1000*$price, 2);
					$billjson['memory'] = round($rampoints/1000*$price, 2);
					$billjson['virtualization'] = round($vmpoints/1000*$price, 2);
					$billjson['networking'] = round($netpoints/1000*$price, 2);

					$billjson['cpu'] = '$'.$billjson['cpu'];
					$billjson['storage'] = '$'.$billjson['storage'];
					$billjson['memory'] = '$'.$billjson['memory'];
					$billjson['virtualization'] = '$'.$billjson['virtualization'];
					$billjson['networking'] = '$'.$billjson['networking'];
					$billjson['all'] = '$'.$cost;

					$billresult = json_encode($billjson);
				}


				if($priceonly == true) {
					$result = $cost;
				} else {

					$cost ='$'.$cost;

					$result = '<p id="resulttotal">You have consumed <b>'.$ccu.' CCUs</b> for this period. <br/><span class="total">Total Cost:</span> <b>'.$cost.'</b></p><br/><a id="detailreport">Detail Report</a><div id="detailtable">'.$detailtable.'</div>';

					if ($detailcategory == true) {
						$result = $categoryresult;
					}

					if ( $forbill == true ) {
						$result = $billresult;
					}

					if ( $forcsv == true ) {
						$csvname = 'csv_bill_'.$user.'_'.$month.'_'.$year.'.csv';
						$csvfile = '/usr/share/htvcenter/plugins/cloud/cloud-fortis/web/tmp/'.$csvname;
						$ip = $_SERVER['SERVER_ADDR'];
						$csvurl = 'http://'.$ip.'/cloud-fortis/tmp/'.$csvname;
						unlink($csvfile);

						header('Content-Type: text/csv; charset=utf-8');
						header('Content-Disposition: attachment; filename=data.csv');

						$output = fopen($csvfile, 'w');
						
						fputcsv($output, array('Cloud Spend Summary:', ' ', ' '));
						fputcsv($output, array('Detail', 'CCus', 'Total'));
						
						fputcsv($output, array('Storage', $storagepoints, $billjson['storage']));
						fputcsv($output, array('CPU', $cpupoints, $billjson['cpu']));
						fputcsv($output, array('Memory', $rampoints, $billjson['memory']));
						fputcsv($output, array('Networking', $netpoints, $billjson['networking']));
						fputcsv($output, array('Virtualisation', $vmpoints, $billjson['virtualization']));
						fputcsv($output, array('Total', $ccu, $billjson['all']));

						$result = $csvurl;
					}
					
				}

				
				echo $result;
				die();
				
			} else {
				//echo 'none'; die();
			}
		}
		*/
		
		// calendar and cron:

		if($_GET['base'] == 'callendar') {

			if (isset($_GET['action']) && $_GET['action'] == 'update') {
				$this->updatecron();
			}

			if (isset($_GET['action']) && $_GET['action'] == 'query') {

			

				$action = $_POST['action'];
				$servers = $_POST['servers'];
				$date = $_POST['date'];
				$time = $_POST['time'];

				$datearr = split('/', $date);

				$mounth = $datearr[0];
				$day = $datearr[1];
				$year = $datearr[2];

				
				$timearr = split(' ', $time);
				
				$timearr2 = split(':', $timearr[0]);
				$hour = $timearr2[0];
				$min = $timearr2[1];



				if ($timearr[1] == 'PM') {
					switch($hour) {
						case '1':
							$hour = '13';
						break;

						case '2':
							$hour = '14';
						break;

						case '3':
							$hour = '15';
						break;

						case '4':
							$hour = '16';
						break;

						case '5':
							$hour = '17';
						break;

						case '6':
							$hour = '18';
						break;

						case '7':
							$hour = '19';
						break;

						case '8':
							$hour = '20';
						break;

						case '9':
							$hour = '21';
						break;

						case '10':
							$hour = '22';
						break;

						case '11':
							$hour = '23';
						break;

						case '12':
							$hour = '24';
						break;
					}
				}

				if (strlen($min) < 2){
					$min = '0'.$min;
				}

				if (strlen($hour) < 2){
					$hour = '0'.$hour;
				}
				
				$serversarr = split(',',$servers);
				
				if (sizeof($serversarr) != 0) {
					foreach ($serversarr as $server) {
						$query = "INSERT INTO `callendar_rules`(`server_id`, `action`, `date`, `time`) VALUES ('$server','$action','$date','$time')";
						mysql_query($query);
				

				$query2 = "SELECT `id` FROM `callendar_rules` WHERE `server_id` = '".$server."' AND `action` = '".$action."' AND `date` = '".$date."' AND `time` = '".$time."'";
				$ress = mysql_query($query2);
				while ($rezz = mysql_fetch_assoc($ress)) {
					$taskid = $rezz['id'];
				}

				
				$ip = $_SERVER['HTTP_HOST'];
				$idserver = $server;

				unset($rule);

				$adminuzr = $this->htvcenter->user()->name;
				$adminpsw = $this->htvcenter->user()->password;

				switch($action) {
					case "start":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/start.php?id=".$idserver."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
					break;
					case "stop":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/stop.php?id=".$idserver."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\"> /dev/null 2>&1".PHP_EOL;
					break;
					case "remove":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/remove.php?id=".$idserver."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
					break;
					default:
						echo 'Error: bad action!'; die();
					break;
				}


				$output = shell_exec('crontab -l');
				file_put_contents('/tmp/crontab.txt', $output.$rule.PHP_EOL);
				echo exec('crontab /tmp/crontab.txt');
			}
			}
				echo 'ok'; die();
		}

			if (isset($_GET['action']) && $_GET['action'] == 'volquery') {

				$action = $_POST['action'];
				$volumes = $_POST['volumes'];
				$date = $_POST['date'];
				$time = $_POST['time'];
				$storageid = $_POST['storageid'];
				$volgroup = $_POST['volgroup'];
				$resido = $_POST['resido'];


				$datearr = split('/', $date);

				$mounth = $datearr[0];
				$day = $datearr[1];
				$year = $datearr[2];

				
				$timearr = split(' ', $time);
				
				$timearr2 = split(':', $timearr[0]);
				$hour = $timearr2[0];
				$min = $timearr2[1];



				if ($timearr[1] == 'PM') {
					switch($hour) {
						case '1':
							$hour = '13';
						break;

						case '2':
							$hour = '14';
						break;

						case '3':
							$hour = '15';
						break;

						case '4':
							$hour = '16';
						break;

						case '5':
							$hour = '17';
						break;

						case '6':
							$hour = '18';
						break;

						case '7':
							$hour = '19';
						break;

						case '8':
							$hour = '20';
						break;

						case '9':
							$hour = '21';
						break;

						case '10':
							$hour = '22';
						break;

						case '11':
							$hour = '23';
						break;

						case '12':
							$hour = '24';
						break;
					}
				}

				if (strlen($min) < 2){
					$min = '0'.$min;
				}

				if (strlen($hour) < 2){
					$hour = '0'.$hour;
				}
				
				
				$volumesarr = split(',',$volumes);
				
				if (sizeof($volumesarr) != 0) {
					foreach ($volumesarr as $volume) {

								if ($action == 'Snap') {
									$name = $volume.'s';
								}

								if ($action == 'Clone') {
									$name = $volume.'c';
								}

								$lvol = $volume;



						$query = "INSERT INTO `callendar_volgroup_rules`( `volgroup`, `lvol`, `name`, `action`, `storage_id`, `date`, `time`, `res_id`) VALUES ('".$volgroup."', '".$lvol."', '".$name."', '".$action."', '".$storageid."', '".$date."', '".$time."', '".$resido."')";
						
						mysql_query($query);
				

				$query2 = "SELECT `id` FROM `callendar_volgroup_rules` WHERE `action` = '".$action."' AND `date` = '".$date."' AND `time` = '".$time."' AND `lvol` = '".$lvol."' AND `name` = '".$name."' AND `volgroup` = '".$volgroup."' AND `storage_id` = ".$storageid."";
				//echo $query2;
				$ress = mysql_query($query2);
				while ($rezz = mysql_fetch_assoc($ress)) {
					$taskid = $rezz['id'];
				}

				
				$ip = $_SERVER['HTTP_HOST'];
				

				unset($rule);

				$adminuzr = $this->htvcenter->user()->name;
				$adminpsw = $this->htvcenter->user()->password;

				switch($action) {
					case "Clone":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/clone.php?resid=".$resido."&lvol=".$lvol."&volgroup=".$volgroup."&deptype=".$deptype."&storageid=".$storageid."&name=".$name."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
					break;
					case "Snap":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/snap.php?resid=".$resido."&lvol=".$lvol."&volgroup=".$volgroup."&deptype=".$deptype."&storageid=".$storageid."&name=".$name."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;	
					break;
					
					default:
						echo 'Error: bad action!'; die();
					break;
				}

				
				$output = shell_exec('crontab -l');
				file_put_contents('/tmp/crontab.txt', $output.$rule.PHP_EOL);
				echo exec('crontab /tmp/crontab.txt');
				
					}
				}

				echo 'ok'; die();

			}

			if (isset($_GET['action']) && $_GET['action'] == 'remove') {
				$serv = $_POST['serverid'];
				$volumer = $_POST['volumer'];


				if ($volumer == true) {
					$query = 'DELETE FROM `callendar_volgroup_rules` WHERE `id`='.$serv;
					mysql_query($query);
					$output = shell_exec('crontab -r');
					$this->updatecron();
				} else {
					$query = 'DELETE FROM `callendar_rules` WHERE `id`='.$serv;
					mysql_query($query);
					$output = shell_exec('crontab -r');
					$this->updatecron();
				}

				echo 'ok'; die();
			}

			$t = $this->response->html->template($this->tpldir.'/index_callendar.tpl.php');

			
			
		}

		// --- end calendar and cron ---
	
		// Configure switch
		if ($this->file->exists($this->webdir.'/unconfigured')) {
			$t->add('', "lang");
			$t->add('<nav id="mainnav-container">
				<div id="mainnav"><div id="mainnav-menu-wrap" >
					
								<ul id="mainnav-menu" class="list-group"><li id="setup_h1">Setup</li></ul></div></div></nav>', "menu");
			$content = $this->configure();
		} 
		else if ($this->response->html->request()->get('upload') !== '') {
			$t->add($this->htvcenter->user()->lang, "lang");
			$t->add($this->menu(), "menu");
			$content = $this->upload();
		} else {
			$t->add($this->htvcenter->user()->lang, "lang");
			$t->add($this->menu(), "menu");
			$content = $this->content();
			// handle scripts and styles of sub loaded objects (tab in tab)
			if($content->__elements['content'] instanceof htmlobject_tabmenu) {
				$tabs = $this->__renderTabs($content->__elements['content']);
				isset($tabs['script']) ? $script .= $tabs['script'] : null;
				isset($tabs['style'])  ? $style  .= $tabs['style']  : null;
			}
		}

		$t->add($content, "content");
		$t->add($this->baseurl, "baseurl");
		$t->add($basetarget, "basetarget");
		$t->add($jstranslation, "jstranslation");
		$t->add($style, "style");
		$t->add($script, "script");
		$t->add(date('Y'), "currentyear");
		$t->add($this->top(), "top");

		$memory = '';
		if(function_exists('memory_get_peak_usage')) {
			$memory = memory_get_peak_usage(false);
		}
		$t->add('Memory: '.$memory.' bytes', 'memory');
		$ti = (microtime(true) - $ti);
		$t->add('Time: '.$ti.' sec', 'time');


			
		$query = "SELECT `cc_value` FROM `cloud_config` WHERE `cc_key` = 'cloud_billing_enabled'";
		
		$rest = mysql_query($query);
		if ($rest) {
			while ($rezt = mysql_fetch_assoc($rest)) {
				$configbill = $rezt['cc_value'];
			}
		} else {
			$configbill = false;
		}

		$t->add($configbill, 'configbill');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Api
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function api() {
		if ($this->file->exists($this->rootdir.'/unconfigured')) {
			return '';
		} else {
			require_once($this->rootdir.'/class/htvcenter.api.class.php');
			$controller = new htvcenter_api($this);
			$controller->action();
		}
	}

	//--------------------------------------------
	/**
	 * CLI
	 *
	 * @access public
	 * @param string $argv console parameters
	 */
	//--------------------------------------------
	function cli($argv) {
		if ($this->file->exists($this->rootdir.'/unconfigured')) {
			return '';
		} else {
			require_once($this->rootdir.'/class/htvcenter.cli.class.php');
			$controller = new htvcenter_cli($this, $argv);
			$controller->action();
		}
	}

	//--------------------------------------------
	/**
	 * Build Top of page
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function top() {
		require_once($this->rootdir.'/class/htvcenter.top.class.php');
		$controller = new htvcenter_top($this->response, $this->file, $this->htvcenter);
		$controller->tpldir = $this->tpldir;
		$controller->lang = $this->lang['top'];
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Build menu
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function menu() {
		require_once($this->rootdir.'/class/htvcenter.menu.class.php');
		$controller = new htvcenter_menu($this->response, $this->file, $this->htvcenter->user());
		$controller->tpldir = $this->tpldir;
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Handle content
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function content() {
		require_once($this->rootdir.'/class/htvcenter.content.class.php');
		$controller = new htvcenter_content($this->response, $this->file, $this->htvcenter->user(), $this->htvcenter);
		$controller->tpldir = $this->tpldir;
		$controller->rootdir = $this->rootdir;
		$controller->lang = $this->lang['content'];
		$data = $controller->action();
		return $data;
	}

	//--------------------------------------------
	/**
	 * Configure htvcenter
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function configure() {
		require_once($this->rootdir.'/class/htvcenter.configure.class.php');
		$controller = new htvcenter_configure($this->response, $this->file);
		$controller->tpldir = $this->tpldir;
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Upload
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function upload() {
		require_once($this->rootdir.'/class/htvcenter.upload.class.php');
		$controller = new htvcenter_upload($this->response, $this->htvcenter);
		$controller->tpldir = $this->tpldir;
		$controller->lang = $this->lang['upload'];
		return $controller->action();
	}	
	
	//--------------------------------------------
	/**
	 * Render js/css include strings
	 *
	 * @access protected
	 * @param $path path to css/js dir
	 * @param $mode enum [css|js]
	 * @return string
	 */
	//--------------------------------------------
	protected function __renderAssetInclude( $path, $mode ) {
		$str   = '';
		$files = $this->file->get_files($this->webdir.$path, '', '*.'.$mode);
		foreach($files as $file) {
			if($mode === 'css') {
				$str.= '<link rel="stylesheet" href="'.$this->baseurl.$path.$file['name'].'" type="text/css">'."\n";
			}
			else if($mode === 'js') {
				$str.= '<script src="'.$this->baseurl.$path.$file['name'].'" type="text/javascript"></script>'."\n";
			}
		}
		return $str;
	}

	//--------------------------------------------
	/**
	 * Read pluginroot attrib (tab in tab)
	 *
	 * @access protected
	 * @param htmloject_tabmenu $obj
	 * @return array
	 */
	//--------------------------------------------
	protected function __renderTabs( $obj ) {
		$return['script'] = '';
		$return['style']  = '';
		foreach($obj->__data as $a) {
			if(
				isset($a['active']) && 
				$a['active'] === true &&
				isset($a['value']) && 
				$a['value'] instanceof htmlobject_tabmenu
			){
				if(isset($a['value']->pluginroot)) {
					$return['style']  .= $this->__renderAssetInclude( $a['value']->pluginroot.'/css/', 'css' );
					$return['script'] .= $this->__renderAssetInclude( $a['value']->pluginroot.'/js/', 'js' );
				}
				$tabs = $this->__renderTabs($a['value']);
				isset($tabs['script']) ? $return['script'] .= $tabs['script'] : null;
				isset($tabs['style'])  ? $return['style']  .= $tabs['style']  : null;
				break;
			}
		}
		return $return;
	}




function getsdata() {

	$d = array();


		

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
			
			
			$d['cpu'] = $resource->cpumodel;
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
			
			
			$d['cpu'] = $resource->cpumodel;
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
		return $d;

	}


function updatecron() {

				shell_exec('crontab -r');
				shell_exec('rm -rf /tmp/crontab.txt');
				
				$adminuzr = $this->htvcenter->user()->name;
				$adminpsw = $this->htvcenter->user()->password;


				$rule = "59 23 31 dec * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/index.php?base=callendar&action=update\" > /dev/null 2>&1".PHP_EOL;
				

					$output = shell_exec('crontab -l');
					file_put_contents('/tmp/crontab.txt', $output.$rule.PHP_EOL);
					echo exec('crontab /tmp/crontab.txt');
				

				$query = "SELECT * FROM `callendar_rules`";
				$res = mysql_query($query);
				while ($rez = mysql_fetch_assoc($res)) {
					$taskid = $rez['id'];
					$idserver = $rez['server_id'];
					$action = $rez['action'];
					$date = $rez['date'];
					$time = $rez['time'];

					
					$idserver = $server;

					unset($rule);

					$datearr = split('/', $date);

				$mounth = $datearr[0];
				$day = $datearr[1];
				$year = $datearr[2];

				$thisyear = date("Y");
				$thisyear = (int) $thisyear;
				$year = (int) $year;
				
			if (($thisyear > $year) || ($thisyear == $year)) {

				
				$timearr = split(' ', $time);
				
				$timearr2 = split(':', $timearr[0]);
				$hour = $timearr2[0];
				$min = $timearr2[1];



				if ($timearr[1] == 'PM') {
					switch($hour) {
						case '1':
							$hour = '13';
						break;

						case '2':
							$hour = '14';
						break;

						case '3':
							$hour = '15';
						break;

						case '4':
							$hour = '16';
						break;

						case '5':
							$hour = '17';
						break;

						case '6':
							$hour = '18';
						break;

						case '7':
							$hour = '19';
						break;

						case '8':
							$hour = '20';
						break;

						case '9':
							$hour = '21';
						break;

						case '10':
							$hour = '22';
						break;

						case '11':
							$hour = '23';
						break;

						case '12':
							$hour = '24';
						break;
					}
				}

				if (strlen($min) < 2){
					$min = '0'.$min;
				}

				if (strlen($hour) < 2){
					$hour = '0'.$hour;
				}

				$adminuzr = $this->htvcenter->user()->name;
				$adminpsw = $this->htvcenter->user()->password;

					switch($action) {
						case "start":
							$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/start.php?id=".$idserver."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
						break;
						case "stop":
							$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/stop.php?id=".$idserver."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
						break;
						case "remove":
							$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/remove.php?id=".$idserver."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
						break;
						default:
							echo 'Error: bad action!'; die();
						break;
					}




					$output = shell_exec('crontab -l');
					file_put_contents('/tmp/crontab.txt', $output.$rule.PHP_EOL);
					echo exec('crontab /tmp/crontab.txt');

				} else {
					$query2 = 'DELETE FROM `callendar_rules` WHERE `id`='.$taskid;
					mysql_query($query2);
					
				}

				}

	$this->volupdatecron();			
}


function volupdatecron() {

				$ip = $_SERVER['HTTP_HOST'];

				$query = "SELECT * FROM `callendar_volgroup_rules`";
				$res = mysql_query($query);
				while ($rez = mysql_fetch_assoc($res)) {
					
				$resido = $rez['res_id'];
				$lvol = $rez['lvol'];
				$volgroup = $rez['volgroup'];
				$deptype = 'kvm-bf-deployment';
				$storageid = $rez['storage_id'];
				$name = $rez['name'];
				$taskid = $rez['id'];
				$action = $rez['action'];
				$date = $rez['date'];
				$time = $rez['time'];
 
				unset($rule);

				$datearr = split('/', $date);

				$mounth = $datearr[0];
				$day = $datearr[1];
				$year = $datearr[2];

				$thisyear = date("Y");
				$thisyear = (int) $thisyear;
				$year = (int) $year;
			
			if (($thisyear > $year) || ($thisyear == $year)) {

				
				$timearr = split(' ', $time);
				
				$timearr2 = split(':', $timearr[0]);
				$hour = $timearr2[0];
				$min = $timearr2[1];



				if ($timearr[1] == 'PM') {
					switch($hour) {
						case '1':
							$hour = '13';
						break;

						case '2':
							$hour = '14';
						break;

						case '3':
							$hour = '15';
						break;

						case '4':
							$hour = '16';
						break;

						case '5':
							$hour = '17';
						break;

						case '6':
							$hour = '18';
						break;

						case '7':
							$hour = '19';
						break;

						case '8':
							$hour = '20';
						break;

						case '9':
							$hour = '21';
						break;

						case '10':
							$hour = '22';
						break;

						case '11':
							$hour = '23';
						break;

						case '12':
							$hour = '24';
						break;
					}
				}

				if (strlen($min) < 2){
					$min = '0'.$min;
				}

				if (strlen($hour) < 2){
					$hour = '0'.$hour;
				}

				$adminuzr = $this->htvcenter->user()->name;
				$adminpsw = $this->htvcenter->user()->password;

			switch($action) {
					case "Clone":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/clone.php?resid=".$resido."&lvol=".$lvol."&volgroup=".$volgroup."&deptype=".$deptype."&storageid=".$storageid."&name=".$name."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;
					break;
					case "Snap":
						$rule = "$min $hour $day $mounth * wget -O- --user=".$adminuzr." --password=".$adminpsw." \"http://".$ip."/htvcenter/base/server/appliance/cron/snap.php?resid=".$resido."&lvol=".$lvol."&volgroup=".$volgroup."&deptype=".$deptype."&storageid=".$storageid."&name=".$name."&taskid=".$taskid."&user=".$adminuzr."&password=".$adminpsw."\" > /dev/null 2>&1".PHP_EOL;	
					break;
					
					default:
						echo 'Error: bad action!'; die();
					break;
				}

				
				$output = shell_exec('crontab -l');
				file_put_contents('/tmp/crontab.txt', $output.$rule.PHP_EOL);
				echo exec('crontab /tmp/crontab.txt');
				
				} else {
					//var_dump('here'); die();
					$query2 = 'DELETE FROM `callendar_volgroup_rules` WHERE `id`='.$taskid;
					mysql_query($query2);
					
				}

				}

}


	function applianceselect() {
		
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		require_once($RootDir.'class/appliance.class.php');
		
		$appliance_tmp = new appliance();
	
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
			$j = 0;
		
			foreach ($appliance_array as $index => $appliance_db) {
					$storage = 'No Info';
					$cpu = 'No Info';
					$memory = 'No Info';

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
					$add = 0;
					$state_data = 'active';
				} else {
					$add = 1;
					$j = $j+1;
					$state_data = 'inactive';
				}

				$kernel = new kernel();
				$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
				$image = new image();
				$image->get_instance_by_id($appliance_db["appliance_imageid"]);
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
				$appliance_virtualization_type=$virtualization->name;

				if ($add == 1) {
					$namee = $appliance_db["appliance_name"];
					$query = "SELECT `appliance_starttime`, `appliance_stoptime` FROM `appliance_info` WHERE `appliance_name` = '".$namee."'";
					$res = mysql_query($query);

					while ($rez = mysql_fetch_assoc($res)) {
						$startd = $rez['appliance_starttime'];
						$endd = $rez['appliance_stoptime'];
					}

					if ($startd == NULL) {
						$startd = 'No Info in database';
						$workingtime = 'No Info in database';
					} else {
						// Create two new DateTime-objects...
						$start = gmdate("Y-m-d\TH:i:s", $startd);
						$stop = gmdate("Y-m-d\TH:i:s", $endd);
						$date1 = new DateTime($start);
						$date2 = new DateTime($stop);

						// The diff-methods returns a new DateInterval-object...
						$diff = $date2->diff($date1);

						// Call the format method on the DateInterval-object
						$workingtime = $diff->format('%a days and %h hours');
						$startdisplay = gmdate("F d, Y h:ia", $startd);
					}


					$stop = gmdate("Y-m-d\TH:i:s", $endd);
					$today = date("Y-m-d\TH:i:s");

					$date1 = new DateTime($today);
					$date2 = new DateTime($stop);
					$diff = $date2->diff($date1);	
					$days = $diff->format('%a days');

					
					$query = "SELECT `appliance_id`, `appliance_cpunumber`, `appliance_memtotal` FROM `appliance_info` WHERE `appliance_name` = '".$namee."'";

					$res = mysql_query($query);

					while($rez = mysql_fetch_assoc($res)) {
						$cpu = $rez['appliance_cpunumber'];
						$memory = $rez['appliance_memtotal'];
						$aplid = $rez['appliance_id'];
					}

					
						
						$query = 'SELECT * FROM `cloud_requests` WHERE `cr_appliance_id` = "'.$aplid.'"';
						$ress = mysql_query($query);

						if ($ress) {
							while ($rezz = mysql_fetch_assoc($ress)) {
								$cpu = $rezz['cr_cpu_req'];
								$memory = $rezz['cr_ram_req'];
								$storage = $rezz['cr_disk_req'];
							}
						}


						$query = "SELECT `resource_cpunumber`, `resource_memtotal`, `resource_ip` FROM `resource_info` WHERE `resource_hostname` = '".$namee."'";			
						$resa = mysql_query($query);
						$stat = 0;

						if ($resa) {
							while ($reza = mysql_fetch_assoc($resa)) {
								$cpu = $reza['resource_cpunumber'];
								$memory = $reza['resource_memtotal'];
								$ippp = $reza['resource_ip'];
								$stat = 1;

							}
							
						}

						if ($stat == 1) {

							
							$query = 'SELECT `vmw_esx_ad_ip` FROM `vmw_esx_auto_discovery`';
							$resips = mysql_query($query);
							
							if ($resips) {
								while($iprez = mysql_fetch_assoc($resips)) {
									$ippp = $iprez['vmw_esx_ad_ip'];

											$statfile = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$ippp.'.vm_list';
											$file = $statfile;
											
											if(file_exists($file)) {
												$lines = explode("\n", file_get_contents($file));

												if(count($lines) >= 1) {
													foreach($lines as $line) {
														if($line !== '') {
															$line = explode('@', $line);

															if ($line[0] == $namee) {
																$storage = $line[9];
															}


														
														}
													}
												}
											}
											// ----
									}
								}
							}


							

					

					if ( !empty($memory) && ($memory != NULL) ) {
						$memory = $memory.' MB';
					}


					if (!empty($storage) && ($storage != 'No Info')) {
						$storage = $storage / 1000;
						$storage = $storage.' GB';
					}


					if ($memory == NULL) {
						$memory = 'No Info in database';
					}

					if ($cpu == NULL) {
						$cpu = 'No Info in database';
					}

					if ( ($storage == NULL) || ($storage == 'No Info') ) {
						$storage = 'No Info in database';
					}

					$arBody['name'] = $namee;
					$arBody['status'] = $state_data;
					$arBody['days'] = $days;
					$arBody['cpu'] = $cpu;
					$arBody['ram'] = $memory;
					$arBody['storage'] = $storage;
					$arBody['created'] = $startdisplay;
					$arBody['worked'] = $workingtime;
					$arBody['servid'] = $aplid;
					$jsonb[$j]=$arBody;	
					
				}

			}


		}
				
		$jsone = json_encode($jsonb);
		//$jsone = str_replace('[', '{', $jsone);
		//$jsone = str_replace(']', '}', $jsone);
		return $jsone;
	}


}
