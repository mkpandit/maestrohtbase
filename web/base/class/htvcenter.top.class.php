<?php
ini_set('display_errors','Off');
/**
 * htvcenter Top
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_top
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang;


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param htvcenter $htvcenter
	 */
	//--------------------------------------------
	function __construct($response, $file, $htvcenter) {
		$this->response = $response;
		$this->file     = $file;
		$this->htvcenter  = $htvcenter;

		if (isset($_GET) && $_GET['report'] == 'yes') {
			
			if (isset($_POST)) {
				$user = $_POST['user'];
				$month = $_POST['month'];
				$year = $_POST['year'];
			}



			if (!empty($month) && !empty($year) && !empty($user)) {
				

				$query = "SELECT `cu_id` FROM `cloud_users` WHERE `cu_name`=\"".$user."\"";
				$res = mysql_query($query);
				$res = mysql_fetch_row($res);
				$userid = $res[0];


				mysql_select_db('htvcenter_billing');
				$query = "SELECT `ct_time`, `ct_ccu_charge`, `ct_ccu_balance`, `ct_comment` FROM `cloud_transaction` WHERE `ct_cu_id`=".$userid."";
				$res = mysql_query($query);
				$ccu =0;
				$detailtable = '<table class="table table-striped table-bordered"><tr class="info"><td>Date</td><td>CCU</td><td>Comment</td></tr>';
				while ($rez = mysql_fetch_assoc($res)) {
					$timestamp=$rez['ct_time'];
					$yeardb = gmdate("Y", $timestamp);
					$monthdb = gmdate("M", $timestamp);
					if ( ($year == $yeardb) && ($month == $monthdb) ) {
						$ccu = $ccu + $rez['ct_ccu_charge'];
						$tabledate = gmdate("Y-m-d h:i", $timestamp);
						$detailtable .= '<tr class="headertr"><td>'.$tabledate.'</td><td>'.$rez['ct_ccu_charge'].'</td><td>'.$rez['ct_comment'].'</td></tr>';
					}
				}
				$detailtable .= '</table>';
				mysql_select_db('htvcenter');

				$query = "SELECT `cc_value` FROM `cloud_config` WHERE `cc_key`='cloud_1000_ccus'";
				$res = mysql_query($query);
				$rez = mysql_fetch_row($res);
				
				$price = $rez[0];
				
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
				$cost ='$'.$cost;

				$result = '<p id="resulttotal">You have consumed <b>'.$ccu.' CCUs</b> for this period. <br/><span class="total">Total Cost:</span> <b>'.$cost.'</b></p><br/><a id="detailreport">Detail Report</a><div id="detailtable">'.$detailtable.'</div>';
				echo $result;
				die();
				


			} else {
				echo 'none'; die();
			}
		}
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
		$t = $this->response->html->template($this->tpldir.'/index_top.tpl.php');
		// only translate if user is not empty (configure mode)
		if($this->htvcenter !== '') {
			$t->add($this->htvcenter->user()->name, 'username');
			$t->add($this->htvcenter->user()->lang, 'userlang');
			$t->add($this->lang['account'], 'account');
			$t->add($this->lang['documentation'], 'documentation');
			$t->add($this->lang['info'], 'info');
			$t->add($this->lang['support'], 'support');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($this->htvcenter->user()->lang, 'userlang');
			$htvcenter_config = $this->htvcenter->get('config');
			$htvcenter_version =  $htvcenter_config['SERVER_VERSION'].".".$htvcenter_config['MINOR_RELEASE_VERSION'];
			$t->add($htvcenter_version, 'version');

			$a = $this->response->html->a();
			$a->href = '/htvcenter/';
			$a->label = '&#160;';
			$a->title = 'Logout';
			$a->css = 'fa fa-sign-out logout-url';
			$a->id = 'logoutbutton';
			$a->style = 'display:none;';
			$a->handler = 'onclick="Logout(this);return false;"';
			$logout  = $a->get_string();
			$logout .= '<script type="text/javascript">';
			$logout .= 'document.getElementById(\'logoutbutton\').style.display = "block";';
			$logout .= '</script>';
			
			$select          = $this->response->html->select();
			$select->css     = 'htmlobject_select selectpicker langselecto';
			$select->id      = 'Language_select';
			$select->name    = 'language';
			$select->handler = 'onchange="wait(); set_language();"';

			$languages = array();
			$files = $this->file->get_files($this->htvcenter->get('basedir').'/web/base/lang/', '', '*.htmlobjects.ini');
			foreach($files as $v) {
				$tmp = explode('.', $v['name']);
				$languages[] = $tmp[0];
			}

			foreach($languages as $lang) {
				$o = $this->response->html->option();
				$o->label = '&nbsp;'.$lang;
				//$o->css   = 'lang-'.$lang;
				$o->value = $lang;
				//$o->style = 'background-image: url(img/'.$lang.'.gif) no-repeat';
				if($lang === $this->htvcenter->user()->lang) {
					$o->selected = true; 
				}
				$select->add($o);
			}
			$box = $this->response->html->box();
			$box->id  = 'Language_box';
			
			$box->label = '';
			//'.$this->lang['language'].'
			$box->add($select);
			//$box->add($logout);

			$t->add($box, 'language_select');
		} else {
			$t->add('&#160;', 'language_select');
			$t->add('&#160;', 'account');
			$t->add('&#160;', 'documentation');
			$t->add('&#160;', 'info');
			$t->add('&#160;', 'support');
		}

		$options = $this->optionsselect(); 
		$t->add($options, 'scheduleroptions');

		$options = $this->optionsselectvol(); 
		$t->add($options, 'voloptions');

		$volid = $this->getstid(); 
		$t->add($volid, 'storageidvolvol');
		

	if ($_GET['base'] == 'callendar') {
		$todaydate = date("Y-m-d");
		$t->add($todaydate, 'todaydate');

		$calendarevents = '';

		$query = 'SELECT * FROM `callendar_rules` WHERE 1';
		$res = mysql_query($query);
		
		while($rez=mysql_fetch_assoc($res)) {
			$date = split('/',$rez['date']);
			$mounth = $date[0];
			$day = $date[1];
			$year = $date[2];

			if (strlen($day) < 2 ) {
				$day = '0'.$day;
			}

			if (strlen($mounth) < 2 ) {
				$mounth = '0'.$mounth;
			}
			$time = $rez['time'];

			$timearr = split(' ', $time);
				
				$timearr2 = split(':', $timearr[0]);
				$hour = $timearr2[0];
				$min = $timearr2[1];

				if (strlen($min) < 2 ) {
					$min = '0'.$min;
				}

				if (strlen($hour) < 2 ) {
					$hour = '0'.$hour;
				}


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

				$action = $rez['action'];
				switch($action) {
					case "start":
						$class = 'success';
					break;
					case "stop":
						$class = 'warning';
					break;
					case "remove":
						$class = 'danger';
					break;
					default:
						$class = 'info';
					break;
				}

				$sid = $rez['server_id'];
				$entryid = $rez['id'];
				$query2 = 'SELECT `appliance_name` FROM `appliance_info` WHERE `appliance_id` ='.$sid;
				$ref = mysql_query($query2);

				while($n = mysql_fetch_assoc($ref)) {
					$name = $n['appliance_name'];
				}

				$class = $class.' id_'.$entryid; 

				$calendarevents .= PHP_EOL.'
					{	
						id: \''.$entryid.'\',
						title: \''.$name.'\',
						start: \''.$year.'-'.$mounth.'-'.$day.'T'.$hour.':'.$min.':00\',
						className: \''.$class.'\'
					},'.PHP_EOL;

		}



		$query = 'SELECT * FROM `callendar_volgroup_rules` WHERE 1';
		$res = mysql_query($query);

		while($rez=mysql_fetch_assoc($res)) {
			$date = split('/',$rez['date']);
			$mounth = $date[0];
			$day = $date[1];
			$year = $date[2];
			$entryid = $rez['id'];

			if (strlen($day) < 2 ) {
				$day = '0'.$day;
			}

			if (strlen($mounth) < 2 ) {
				$mounth = '0'.$mounth;
			}
			$time = $rez['time'];

			$timearr = split(' ', $time);
				
				$timearr2 = split(':', $timearr[0]);
				$hour = $timearr2[0];
				$min = $timearr2[1];

				if (strlen($min) < 2 ) {
					$min = '0'.$min;
				}

				if (strlen($hour) < 2 ) {
					$hour = '0'.$hour;
				}


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

				$action = $rez['action'];
				switch($action) {
					case "Clone":
						$class = 'purple';
					break;
					default:
						$class = 'info';
					break;
				}

				$name = $rez['name'];
				$class = $class.' id_'.$entryid.' volumecal'; 

				$calendarevents .= PHP_EOL.'
					{	
						id: \''.$entryid.'\',
						title: \''.$name.'\',
						start: \''.$year.'-'.$mounth.'-'.$day.'T'.$hour.':'.$min.':00\',
						className: \''.$class.'\'
					},'.PHP_EOL;

		}

		$calendarevents = '['.$calendarevents.']';

		$t->add($calendarevents, 'calendarevents');
	}


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
		
		$queryc = 'SELECT `cu_name`  FROM `cloud_users`';
		$res = mysql_query($queryc);
		$cloudusers = '';
		while($rez = mysql_fetch_assoc($res)) {
			$cloudusers .= '<option val="'.$rez['cu_name'].'">'.$rez['cu_name'].'</option>';
		}
		
		
		$t->add($yearz, 'reportyear');
		$t->add($cloudusers, 'cloudusers');



		return $t;
	}


function optionsselect() {

	$query = 'SELECT `appliance_id`, `appliance_name` FROM `appliance_info` WHERE 1';
	$rez = mysql_query($query);
	
	if ($rez) {
		while($res = mysql_fetch_assoc($rez)) {
			$options .= '<option value="'.$res['appliance_id'].'">'.$res['appliance_name'].'</option>';
		}
	}

	return $options;
	
	}

function optionsselectvol() {
	$file = '/usr/share/htvcenter/plugins/kvm/web/storage/0.storage1.lv.stat';
	
	$voloptions = '';
	if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				
				if(count($lines) >= 1) {
					
					foreach($lines as $line) {
						if($line !== '') { 
							
							$arline = explode('@', $line);
								$voloptions .= '<option value="'.$arline[1].'">'.$arline[1].'</option>';
							}
						}
					}
				}
			
	return $voloptions;
}

function getstid() {
	$query = "SELECT `storage_id` FROM `storage_info` WHERE `storage_resource_id` = 0 and `storage_name` = 'htvcenter-bf'";
		$res = mysql_query($query);
		
		while ($rez=mysql_fetch_array($res)) {
			$stid = $rez['storage_id'];
		}
		
	return $stid;
}



}
