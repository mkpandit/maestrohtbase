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


		if (isset($_GET) && $_GET['report'] == 'yes') {
			
			if (isset($_POST)) {
				$user = $this->htvcenter->user()->name;
				$month = $_POST['month'];
				$year = $_POST['year'];
			}



			if (!empty($month) && !empty($year)) {
				
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

		// templating default or custom
		$tpl = $this->portaldir."/user/tpl/index.default.tpl.php";
		if($this->file->exists($this->portaldir."/user/tpl/index.tpl.php")) {
			$tpl = $this->portaldir."/user/tpl/index.tpl.php";
		}
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
		$t->add($hide, 'hidenuser');

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
