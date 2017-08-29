<?php
/**
 * htvcenter Cloud Portal Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class cloud_controller
{
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
		$this->portaldir = $_SERVER["DOCUMENT_ROOT"].'/cloud-fortis';
		$this->tpldir = $this->portaldir.'/user/tpl/';
		$this->langdir = $this->portaldir.'/user/lang/';

		require_once($this->rootdir.'/class/file.handler.class.php');
		require_once($this->rootdir.'/class/htmlobjects/htmlobject.class.php');
		require_once($this->rootdir.'/class/htvcenter.htmlobjects.class.php');
		$html = new htvcenter_htmlobject();
		$file = new file_handler();
		$this->response = $html->response();




		// handle user
		$user = '';
		if(isset($_SERVER['PHP_AUTH_USER'])) {
			require_once($this->rootdir.'/plugins/cloud/class/clouduser.class.php');
			$user = new clouduser($_SERVER['PHP_AUTH_USER']);
			$user->get_instance_by_name($_SERVER['PHP_AUTH_USER']);
			// handle user lang
			$lang = $this->response->html->request()->get('langselect');
			if($lang !== '') {
				$user->update($user->id, array('cu_lang' => $lang));
				$user->get_instance_by_name($_SERVER['PHP_AUTH_USER']);
			}
		}

		// if htvcenter is unconfigured, set htvcenter empty
		if ($file->exists($this->rootdir.'/unconfigured')) {
			$this->htvcenter = '';
			$this->webdir  = $html->thisdir;
			$this->baseurl = $html->thisurl;
		} else {
			require_once($this->rootdir.'/class/htvcenter.class.php');
			$this->htvcenter = new htvcenter($file, $user, $html->response());
			$this->webdir  = $this->htvcenter->get('webdir');
			$this->baseurl = $this->htvcenter->get('baseurl');
		}

		if ( isset($_GET['budget']) && ($_GET['budget'] == 'yes') ) {
			
			$getbudgets = $_POST['getbudgets'];
			$create = $_POST['create'];
			$name = $_POST['name'];
			$date_start = $_POST['date_start'];
			$date_end = $_POST['date_end'];
			
			if ($date_start =='""') {
						$date_start = 'unlim';
			}

			if ($date_end =='""') {
						$date_end = 'unlim';
			}

			$date_start = str_replace('"', '', $date_start);
			$date_end = str_replace('"', '', $date_end);

			$cpu = $_POST['cpu'];
			$memory = $_POST['memory'];
			$storage = $_POST['storage'];
			$networking = $_POST['networking'];
			$vm = $_POST['vm'];
			$limit = $_POST['limit'];
			$rembudget = $_POST['rembudgets'];
			$edit = $_POST['editbudgets'];
			$globalid = $_POST['globalid'];
			$place = $_POST['place'];
			$editval = $_POST['editval'];

			if ( isset($edit) && ($edit == 1)) {
				
				if ($place == 'cpuedit') {
					$query = 'UPDATE `cloud_price_limits` SET `cpu`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'memoryedit') {
					$query = 'UPDATE `cloud_price_limits` SET `memory`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'storageedit') {
					$query = 'UPDATE `cloud_price_limits` SET `storage`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'networkedit') {
					$query = 'UPDATE `cloud_price_limits` SET `network`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'virtualedit') {
					$query = 'UPDATE `cloud_price_limits` SET `vm`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'datestartedit') {
					$editval = str_replace('"', '', $editval);
					if ($editval == '') {
						$editval = 'unlim';
					}
					$query = 'UPDATE `cloud_price_limits` SET `date_start`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'dateendedit') {
					$editval = str_replace('"', '', $editval);
					if ($editval == '') {
						$editval = 'unlim';
					}
					$query = 'UPDATE `cloud_price_limits` SET `date_end`="'.$editval.'" WHERE `id` = "'.$globalid.'"';
				}

				if ($place == 'percremove') {
					$editval = str_replace('"', '', $editval);
					$query = 'DELETE FROM `cloud_price_alert` WHERE `budget_id` = "'.$globalid.'" AND `percent` = "'.$editval.'"';
					
				}

				if ($place == 'percadd') {
					$editval = str_replace('"', '', $editval);
					$query = 'INSERT INTO `cloud_price_alert`( `percent`, `budget_id`) VALUES ("'.$editval.'", "'.$globalid.'")';
				}

				var_dump($query);
				mysql_query($query);
				echo 'edit'; die();
			}

			if (isset($rembudget) && $rembudget == 1) {
				$remid = $_POST['remid'];
				$query = 'DELETE FROM `cloud_price_alert` WHERE `budget_id` = "'.$remid.'"';
				mysql_query($query);
				$query = 'DELETE FROM `cloud_price_limits` WHERE `id` = "'.$remid.'"';
				mysql_query($query);

				echo 'remdone'; die();
			}

			if (isset($getbudgets) && $getbudgets == true) {

				$username = $this->htvcenter->user()->name;
				$queryb = 'SELECT * FROM `cloud_price_limits` WHERE `user`="'.$username.'"';
				$budgets = array();
				$res = mysql_query($queryb);
				$i = 0;
				$result = array();

				while ($rez = mysql_fetch_assoc($res)) {
					$i = $i + 1;
					$budgets[$i]['name'] = $rez['name'];
					$budgets[$i]['date_start'] = $rez['date_start'];
					$budgets[$i]['date_end'] = $rez['date_end'];

					$budgets[$i]['cpu'] = $rez['cpu'];
					$budgets[$i]['memory'] = $rez['memory'];
					$budgets[$i]['storage'] = $rez['storage'];
					$budgets[$i]['network'] = $rez['network'];
					$budgets[$i]['vm'] = $rez['vm'];
					$budgets[$i]['id'] = $rez['id'];
					$idlim = $rez['id'];
					$queryl = 'SELECT * FROM `cloud_price_alert` WHERE `budget_id` = '.$idlim;
					
					$ress = mysql_query($queryl);
					
					//var_dump($queryl);
					$budgets[$i]['havealerts'] = 0;
					if ($ress) {
						while($rezz = mysql_fetch_assoc($ress)) {
							$budgets[$i]['havealerts'] = 1;
							$budgets[$i]['alerts'][] = $rezz['percent'];
						}
					}	
				}

				
				//var_dump($budgets);
				$result = json_encode($budgets);
					echo $result; die();
				
				
			}


			$cpu = (int)$cpu;
			$memory = (int)$memory;
			$storage = (int)$storage;
			$networking = (int)$networking;
			$vm = (int)$vm;

			if ($create == 1) {
				
				

				if (is_int($cpu) && is_int($memory) && is_int($storage) && is_int($networking) && is_int($vm)) {
					$username = $this->htvcenter->user()->name;

					$date_start = str_replace('"', '', $date_start);
					$date_end = str_replace('"', '', $date_end);
					
					$query = "INSERT INTO `cloud_price_limits`( `name`, `date_start`, `date_end`, `cpu`, `memory`, `storage`, `network`, `vm`, `user`) VALUES ('$name','$date_start','$date_end','$cpu','$memory','$storage','$networking','$vm','$username')";
					
					mysql_query($query);

					
					if ($date_start == '') {
						$date_start = 'unlim';
					}

					if ($date_end == '') {
						$date_end = 'unlim';
					}

					$date_start = str_replace('"', '', $date_start);
					$date_end = str_replace('"', '', $date_end);


					$query2 = "SELECT `id` FROM `cloud_price_limits` WHERE `name` = '$name' AND `date_start` = $date_start AND `date_end` = $date_end AND `cpu` = '$cpu' AND `memory` = '$memory' AND `storage` = '$storage' AND `network` = '$networking' AND `vm` = '$vm' AND `user` = '$username'";

					
					$res = mysql_query($query2);
					while ($rez = mysql_fetch_assoc($res)) {
						$budgetid = $rez['id'];
					}

					
					$username = $this->htvcenter->user()->name;

					if ($limit != '') {
							$limits = explode(',', $limit);
							foreach ($limits as $key => $lim) {
								 $queryl = "INSERT INTO `cloud_price_alert`(`percent`, `budget_id`, `user`) VALUES ($lim, $budgetid, '$username')";
								 mysql_query($queryl);	
							}
					}
				}	
			}



			echo 'works'; die();

		}

		
		if (isset($_POST['explorer']) && isset($_POST['explorerajax'])) {
			$explorer = $_POST['explorer'];
			$explorerajax = $_POST['explorerajax'];
			$username = $_POST['uzer'];
			
			if ( $explorer == true && $explorerajax == 'getservers' ) {

				if ($username == '') {
					$userid = $this->htvcenter->user()->id;
				} else {
					$query = "SELECT `cu_id` FROM `cloud_users` WHERE `cu_name` = '".$username."'";
					$rezz = mysql_query($query);
					while($rezo = mysql_fetch_assoc($rezz)) {
						$userid = $rezo['cu_id'];
					}
				}
				
				$query = 'SELECT * FROM `cloud_requests` WHERE `cr_cu_id` = "'.$userid.'"';
				$res = mysql_query($query);
				$serverblocks = array();
				$i = 0;
				while($rez = mysql_fetch_assoc($res)) {
					$i++;
					$serverblocks[$i]['name'] = $rez['cr_appliance_hostname'];
					$serverblocks[$i]['ram'] = $rez['cr_ram_req'].' MB';
					$serverblocks[$i]['cpu'] = $rez['cr_cpu_req'];

					$hostname = $rez['cr_appliance_hostname'];
					$query = 'SELECT `appliance_state` FROM `appliance_info` WHERE `appliance_name` = "'.$hostname.'"';
					$stres = mysql_query($query);

					while($strez = mysql_fetch_assoc($stres)) {
						$state = $strez['appliance_state'];
					}

					if ($state == '') {
						$state = 'removed';
					}
					$serverblocks[$i]['status'] = $state;

					$aplid = $rez['cr_appliance_id'];
					$query = 'SELECT * FROM `cloud_transaction`';
					$costres = mysql_query($query);
					$vmmpoints = 0;
					while ($costrez = mysql_fetch_assoc($costres)) {
						if ( preg_match('@'.$aplid.'@', $costrez['ct_comment']) ) {
							$vmmpoints = $vmmpoints + $costrez['ct_ccu_charge'];
						}
					}

					$query = "SELECT `cc_value` FROM `cloud_config` WHERE `cc_key`='cloud_1000_ccus'";
					$resc = mysql_query($query);
					$rezc = mysql_fetch_row($resc);
					
					$price = $rezc[0];

					
					$cost = $vmmpoints/1000*$price;
					$cost = round($cost, 2);
					
					if (preg_match('@\.@', $cost)) {
						$cosa = explode('.', $cost);
						if (strlen($cosa[1]) == 1) {
							$cost = $cost.'0';
						}
					} else {
						$cost = $cost.'.00';
					}

					$cost = '$'.$cost;

					$serverblocks[$i]['price'] = $cost;
					
					$volsum = 0;
					$query = 'SELECT * FROM `cloud_volumes` WHERE `instance_name` = "'.$hostname.'"';
					$reso = mysql_query($query);
					
					if ($reso) {
						while ($rezo = mysql_fetch_assoc($reso)) {
							$volsum = $volsum + $rezo['size'];
						}
					}
					$allstorage = ($rez['cr_disk_req']+$volsum)/1000;
					$serverblocks[$i]['storage'] = $allstorage.' GB';
					
					// Create two new DateTime-objects...
					$start = gmdate("Y-m-d\TH:i:s", $rez['cr_start']);
					$stop = date("Y-m-d\TH:i:s");
					$date1 = new DateTime($start);
					$date2 = new DateTime($stop);

					// The diff-methods returns a new DateInterval-object...
					$diff = $date2->diff($date1);

					// Call the format method on the DateInterval-object
					$workingtime = $diff->format('%a days and %h hours');

					$serverblocks[$i]['created'] = gmdate("Y-m-d H:i:s", $rez['cr_request_time']);
					$serverblocks[$i]['worked'] = $workingtime;
				}
				

				$result = json_encode($serverblocks);
			}

			echo $result; die();
		}
		

		if (isset($_GET) && $_GET['report'] == 'yes') {
			
			if (isset($_POST)) {
				$user = $_POST['userdash'];
				if ($user == '') {
					$user = $this->htvcenter->user()->name;
				}
				$month = $_POST['month'];
				$year = $_POST['year'];
				$priceonly = $_POST['priceonly'];
				$detailcategory = $_POST['detailcategory'];
				$forbill = $_POST['forbill'];
				$forcsv = $_POST['forcsv'];
			}

			if ( isset($_GET['forbill']) && $_GET['forbill']=='true') {
				$forbill = true;
			}

			if (empty($month) || empty($user)) {
				$month = $_GET['month'];
				$user = $_GET['user'];
				$year = date('Y');
			}

			if (!empty($month) && !empty($year)) {
				
				if ($user != 'All') {
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

					//var_dump($rez); die();

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
				

				$detailtable .= '</table>';
				} else {


					$query = 'SELECT `cu_name` FROM `cloud_users`';
					$uzres = mysql_query($query);

					$uzerz = array();
					while ($uzrez = mysql_fetch_assoc($uzres)) {
						$uzerz[] = $uzrez['cu_name'];
					}


							$vmpoints = 0;
							$netpoints = 0;
							$rampoints = 0;
							$storagepoints = 0;
							$cpupoints = 0;
							$ccu =0;

					foreach ($uzerz as $user) {
							$query = "SELECT `cu_id` FROM `cloud_users` WHERE `cu_name`=\"".$user."\"";
							$res = mysql_query($query);
							$res = mysql_fetch_row($res);
							$userid = $res[0];

							mysql_select_db('cloud_transaction');
							$query = "SELECT `ct_time`, `ct_ccu_charge`, `ct_ccu_balance`, `ct_comment` FROM `cloud_transaction` WHERE `ct_cu_id`=\"".$userid."\"";

							

							

							$res = mysql_query($query);
							
							$detailtable = '<table class="table table-striped table-bordered"><tr class="info"><td>Date</td><td>CCU</td><td>Comment</td></tr>';

							

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
							

						$detailtable .= '</table>';

					}

					$jsonarr = array();
					$jsonarr['memory'] = $rampoints;
					$jsonarr['network'] = $netpoints;
					$jsonarr['virtualisation'] = $vmpoints;
					$jsonarr['storage'] = $storagepoints;
					$jsonarr['cpu'] = $cpupoints;
					$jsonarr['sum'] = $rampoints + $netpoints + $vmpoints + $storagepoints + $cpupoints;
				}

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

					if (isset($_GET['chatbot']) && $_GET['chatbot'] == 'true') {
						$result = json_encode($billjson);
						echo $result;
						die();
					}
					$billresult = json_encode($billjson);
				}


				if ($priceonly == true) {
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
						//$ip = $_SERVER['SERVER_ADDR'];
						
						$ip = $_SERVER['SERVER_NAME'];
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

		// translate
		if($user !== '') {
			$lang = $user->lang;
		} else {
			$lang = $this->response->html->request()->get('langselect');
		}
		$html->lang = $this->__translate($lang, $html->lang, $this->langdir, 'htmlobjects.ini');
		$file->lang = $this->__translate($lang, $file->lang, $this->langdir, 'file.handler.ini');

		require_once $this->rootdir.'/include/requestfilter.inc.php';
		$request = $html->request();
		$request->filter = $requestfilter;

		$this->file    = $file;
		$this->baseurl = '/cloud-fortis/';

		$ar = $this->response->html->request()->get('register_action');
		// templating default if logged in; otherwise templating login
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $_GET['cloud_ui'] == "create_modal") {
				$tpl = $this->portaldir."/user/tpl/index.empty.tpl.php";
			} else {
				$tpl = $this->portaldir."/user/tpl/index.default.tpl.php";
			}
		} else {
			$tpl = $this->portaldir."/user/tpl/index.login.tpl.php";
		}

		// die($tpl);


		// if($this->file->exists($this->portaldir."/user/tpl/index.tpl.php")) {
		// 	$tpl = $this->portaldir."/user/tpl/index.tpl.php";
		// }

		$this->tpl = $tpl;
	}

	//--------------------------------------------
	/**
	 * Register
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function register() {

		// add langselect to remember lang in register forms
		$this->response->add('langselect', $this->response->html->request()->get('langselect'));
		require_once($this->portaldir.'/user/class/cloud-register.controller.class.php');
		$controller = new cloud_register_controller($this->htvcenter, $this->response);
		$controller->lang = $this->__translate(
			$this->response->html->request()->get('langselect'),
			$controller->lang,
			$this->langdir,
			'cloud-register.ini'
		);
		$t = $this->response->html->template($this->tpl);
		$t->add($this->baseurl, 'baseurl');
		$t->add($controller->action(), 'content');
		$t->add($this->__lang($controller->actions_name, $controller->action, $controller->lang['account']['user_lang']), 'langbox');

		return $t;
	}

	//--------------------------------------------
	/**
	 * UI
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function ui() {
		require_once($this->portaldir.'/user/class/cloud-ui.controller.class.php');
		$user = $this->htvcenter->user();
		$controller = new cloud_ui_controller($this->htvcenter, $this->response);
		$controller->lang = $this->__translate(
			$user->lang,
			$controller->lang,
			$this->langdir,
			'cloud-ui.ini'
		);
		$text = sprintf($controller->lang['home']['logged_in_as'], $user->name);

		$t = $this->response->html->template($this->tpl);
		$t->add($this->baseurl, 'baseurl');
		$t->add($controller->action(), 'content');
		$t->add($this->__lang($controller->actions_name, $controller->action, $text, $controller), 'langbox');
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
		
		$hidenuser = $this->htvcenter->user()->name;
		$hide = '<input type="hidden" id="hiddenname" value="'.$hidenuser.'" />';
		$t->add($yearz, 'reportyear');
		$t->add($year, 'currentyear');
		$t->add($hide, 'hidenuser');

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
		
		require_once($this->portaldir.'/user/class/cloud-ui.controller.class.php');
		$controller = new cloud_ui_controller($this->htvcenter, $this->response, $this);
		$controller->lang = $this->__translate(
			$this->htvcenter->user()->lang,
			$controller->lang,
			$this->langdir,
			'cloud-ui.ini'
		);
		return $controller->api();
	}

	//--------------------------------------------
	/**
	 * json
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function json() {

		$file['path'] = $this->htvcenter->get('basedir').'/plugins/cloud/cloud-fortis/web/user/class/cloud-ui.controller.class.php';
		$file['name'] = 'cloud-ui.controller.class.php';

		require_once($file['path']);
		$controller = str_replace('.class.php', '', $file['name']);
		$ini        = str_replace('.controller', '', $controller);;
		$controller = str_replace('.', '_', $controller);
		$controller = str_replace('-', '_', $controller);
		$controller = new $controller($this->htvcenter, $this->response);

		// translate
		$controller->lang = $this->__translate(
			$this->htvcenter->user()->lang,
			$controller->lang,
			$this->langdir,
			$ini.'.ini'
		);

		$content['name'] =  str_replace('.controller.class.php','',  $file['name']);
		$content['object'] = $controller;
		$content['class'] = get_class($controller);
		if(get_class_methods($controller)) {
				$content['methods'] = get_class_methods($controller);
		} else {
				$content['methods'] = '';
		}
		$content['vars'] = get_class_vars(get_class($controller));

		$action = $this->response->html->request()->get('action');
		$help   = $this->response->html->request()->get('help');

		if($action === '') {
			if($help !== '') {
				$str = '';
				echo '<!DOCTYPE html><html><head><title>Json Help</title></head><body>';
				echo '<h2>Actions</h2>';
				foreach($content['methods'] as $method) {
					if(
						strripos($method, '__') === false &&
						strripos($method, 'action') === false &&
						strripos($method, 'api') === false
					) {
						$str .= 'action='.$method."\n";
					}
				}
				$this->response->html->help($str);
				echo '</body></html>';
			}
		}
		else if(in_array($action, $content['methods'])) {

			$file = 'cloud-ui.'.str_replace('_','.',$action).'.class.php';
			require($this->htvcenter->get('basedir').'/plugins/cloud/cloud-fortis/web/user/class/'.$file);

			$class = str_replace('.class.php', '', $file);
			$class = str_replace('.', '_', $class);
			$class = str_replace('-', '_', $class);
			$class = new $class($this->htvcenter, $this->response, $controller);

			$action2lang['account']          = $controller->lang['account'];
			$action2lang['create']           = $controller->lang['create'];
			$action2lang['appliances']       = $controller->lang;
			$action2lang['pause']            = $controller->lang['appliances'];
			$action2lang['unpause']          = $controller->lang['appliances'];
			$action2lang['restart']          = $controller->lang['appliances'];
			$action2lang['deprovision']      = $controller->lang['appliances'];
			$action2lang['appliance_update'] = $controller->lang;
			$action2lang['images']           = $controller->lang['appliances'];

			$class->basedir         = $this->htvcenter->get('basedir');
			$class->lang            = $action2lang[$action];
			$class->identifier_name = $controller->identifier_name;
			$class->clouduser       = $this->htvcenter->user();

			// response

			if(method_exists($class, 'get_response')) {
				$t_response = $class->get_response();
				if(count($t_response->params) > 0) {
					#$error = null;
					foreach($t_response->params as $k => $v) {
						if(!isset($_REQUEST[str_replace('[]','',$k)])) {
							$error[] = 'Missing param '.$k.' for action '.$action;
						} else {
							$check = $_REQUEST[str_replace('[]','',$k)];
print_r($check);

							if($check === '') {
								$error[] = 'Param '.$k.' for action '.$action.' must not be empty';
							}
							else if(is_array($check) && count($check) < 1) {
								$error[] = 'Param '.$k.' for action '.$action.' must not be empty';
							}
						}
					}
				}
			}
	
			// forms
		
			if(method_exists($class, 'form') && !isset($error)) {
				$response = $class->form();
				$form     = $response->form;

				$i = 0;
				$j = array();
				$rest_str = '';
				$elements = $form->get_elements();

				foreach($elements as $elem) {
					if(is_object($elem)) {
						$type = get_class($elem);
						#echo $type.'<br>';
						if($type === 'htmlobject_box') {

							$label = $elem->label;

							$input = $elem->get_elements();
							$input = $input[0];
							if(is_object($input)) {
								$type = get_class($input);
								switch ($type) {
									case 'htmlobject_input':
										$j[$i]['name']  = $input->name;
										$j[$i]['value'] = $input->value;
										$j[$i]['label'] = $label;
										if($help !== '') {
											$rest_str .= '&amp;'.$input->name.'='.$input->value;
											$json_arr[$i]['name']  = $input->name;
											$json_arr[$i]['value'] = $input->value;
										}
									break;
									case 'htmlobject_textarea':
										$j[$i]['name']  = $input->name;
										$j[$i]['value'] = $input->value;
										$j[$i]['label'] = $label;
										if($help !== '') {
											$rest_str .= '&amp;'.$input->name.'='.$input->value;
											$json_arr[$i]['name']  = $input->name;
											$json_arr[$i]['value'] = $input->value;
										}
									break;
									case 'htmlobject_select':
										$j[$i]['name']  = $input->name;
										$j[$i]['label'] = $label;
										$tmp = '';
										foreach($input->__elements as $v) {
											$j[$i]['options'][] = array('value' => $v->value, 'label' => $v->label);
											$tmp = $v->value;
										}
										$rest_str .= '&amp;'.$input->name.'='.$tmp;
										if($help !== '') {
											$rest_str .= '&amp;'.$input->name.'='.$tmp;
											$json_arr[$i]['name']  = $input->name;
											$json_arr[$i]['value'] = $tmp;
										}
									break;
								}
								$i++;
							}
						}
					}
				}

				// Output
				$j = json_encode($j);
				if(isset($response->error)) {
					$errors = array('errors' => $response->error);
					$j .= json_encode($errors);
				}
				elseif(isset($response->msg)) {
					$masg = array('errors' => $response->msg);
					$j .= json_encode($msg);
				}

				if($help !== '') {
					$j = str_replace('<',"&lt;",$j);
					$j = str_replace('{"name"',"\n{\"name\"",$j);
					$j = str_replace(']{"errors"',"\n]\n{\"errors\"]",$j);
					echo '<!DOCTYPE html><html><head><title>Json Help</title></head><body>';
					echo '<h2>Response</h2>';
					$this->response->html->help($j);
					echo '<h2>Request (Example)</h2>';
					echo '<h3>Rest</h3>';
					$this->response->html->help($this->response->html->thisfile.'?action='.$action.'&amp;resonse[submit]=true'.$rest_str);
					echo '<h3>Json</h3>';
					$tmp = str_replace('{"name"',"\n{\"name\"",json_encode($json_arr));
					$this->response->html->help($tmp);
					echo '</body></html>';
				} else {
					echo $j;
				}
			}

			// Tables

			else if (method_exists($class, 'overview') && !isset($error)) {
				$j = json_encode($class->overview());
				if($help !== '') {
					$j = str_replace('<',"&lt;",$j);
					$j = str_replace('{"name"',"\n{\"name\"",$j);
					$j = str_replace(']{"errors"',"\n]\n{\"errors\"]",$j);
					echo '<!DOCTYPE html><html><head><title>Json Help</title></head><body>';
					echo '<h2>Response</h2>';
					$this->response->html->help($j);
					echo '</body></html>';
				} else {
					echo $j;
				}
			}

			// Errors

			else if (isset($error)) {
				$j = json_encode(array('errors' => $error));
				if($help !== '') {
					$j = str_replace('<',"&lt;",$j);
					$j = str_replace(']{"errors"',"\n]\n{\"errors\"]",$j);
					echo '<!DOCTYPE html><html><head><title>Json Help</title></head><body>';
					echo '<h2>Response</h2>';
					$this->response->html->help($j);
					echo '</body></html>';
				} else {
					echo $j;
				}
			}

		}

	}

	//--------------------------------------------
	/**
	 * Translate
	 *
	 * @access protected
	 * @param array $array array to translate
	 * @param string $dir dir of translation files
	 * @param string $file translation file name
	 * @return array
	 */
	//--------------------------------------------
	function __translate( $lang, $array, $dir, $file ) {
		if($lang === '') {
			$lang = 'en';
		}
		$path = $dir.'/'.$lang.'.'.$file;
		if(file_exists($path)) {
			$tmp = parse_ini_file( $path, true );
			foreach($tmp as $k => $v) {
				if(is_array($v)) {
					foreach($v as $k2 => $v2) {
						$array[$k][$k2] = $v2;
					}
				} else {
					$array[$k] = $v;
				}
			}
		}
		// use en file as first fallback
		#else if(file_exists($dir.'/en.'.$file)) {
		#	$tmp = parse_ini_file( $dir.'/en.'.$file, true );
		#	foreach($tmp as $k => $v) {
		#		if(is_array($v)) {
		#			foreach($v as $k2 => $v2) {
		#				$array[$k][$k2] = $v2;
		#			}
		#		} else {
		#			$array[$k] = $v;
		#		}
		#	}
		#}
		return $array;
	}

	//--------------------------------------------
	/**
	 * Lang select form
	 *
	 * @access protected
	 * @param string $actions_name
	 * @param string $action
	 * @return string
	 */
	//--------------------------------------------
	function __lang($actions_name, $action, $text, $controller = null) {

		$response = $this->response->response();
		$response->id = 'langform';
		// remove response langselect param
		unset($response->params['langselect']);
		$form = $response->get_form($actions_name, $action);
		$form->remove('langform[cancel]');

		$select = $this->response->html->select();
		$select->name    = 'langselect';
		$select->id      = 'langselect';
		$select->handler = 'onchange="wait();this.form.submit();"';

		// get translation files to build select
		$files = $this->file->get_files($this->langdir, '', '*.htmlobjects.ini');
		foreach($files as $v) {
			$tmp = explode('.', $v['name']);
			$select->add(array($tmp[0]), array(0,0));
		}

		// handle selected lang
		$logout = '';
		if($this->htvcenter !== '') {
			$user = $this->htvcenter->user();
			if($user !== '') {
				$select->selected = array($user->lang);
				$a = $this->response->html->a();
				$a->href = '../';
				$a->label = '<i class="fa fa-sign-out fa-fw"></i> Logout';
				$a->css = 'btn btn-primary';
				$a->id = 'logoutbuttonz';
				$a->style = 'display:none;';
				$a->handler = 'onclick="Logout(this);return false;"';
				if(isset($controller->lang['home']['logout'])) {
					$a->title = $controller->lang['home']['logout'];
					$a->href = '../?register_msg='.$controller->lang['home']['msg_logout'].'&langselect='.$user->lang;
				}
				$logout = '<div id="avatarside"> Account <img src="/cloud-fortis/img/av1.png" class="ava" /></div>
      <div class="dropdownwin with-arrow">
                <div class="pad-all text-right">'.$a->get_string().'
                </div>
      </div>';
				
				$logout .= '<script type="text/javascript">';
				$logout .= 'document.getElementById(\'logoutbuttonz\').style.display = "block";';
				$logout .= '</script>';
			} else {
				if($this->response->html->request()->get('langselect') !== '') {
					$select->selected = array($this->response->html->request()->get('langselect'));
				} else {
					$select->selected = array('en');
				}
			}
		}

		$lang = $this->response->html->box();
		$lang->css = 'htmlobject_box';
		$lang->label = '';
		$lang->add($select);
		$lang->add($logout);

		$form->add($lang);
		// remove submit when JS is active
		$form->add('<script type="text/javascript">document.getElementsByName("langform[submit]")[0].style.display = "none";</script>');
		return $form->get_string();
	}

}
?>
