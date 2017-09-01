<?php
/**
 * Cloud Users Home
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_ui_home
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud_ui';

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
	 * @param htvcenter $htvcenter
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->rootdir   = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->htvcenter = $htvcenter;
		$this->clouduser = $this->htvcenter->user();
		$this->response = $response;
		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/clouduserslimits.class.php";
		$this->clouduserlimits = new clouduserlimits();
		$this->clouduserlimits->get_instance_by_cu_id($this->htvcenter->user()->id);

		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/cloudappliance.class.php";
		$this->cloudappliance = new cloudappliance();
		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/cloudrequest.class.php";
		$this->cloudrequest = new cloudrequest();
		require_once $this->htvcenter->get('basedir')."/plugins/cloud/web/class/cloudconfig.class.php";
		$this->cloudconfig = new cloudconfig();

		require_once "cloud.limits.class.php";
		$this->cloud_limits = new cloud_limits($this->htvcenter, $this->cloudconfig, $this->clouduserlimits, $this->cloudrequest);


	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {

		$stop = 0;

		if (isset($_GET['report']) && $_GET['report'] == 'report_dashboard') {
			$t = $this->response->html->template("./tpl/report-dashboard.tpl.php");
			$t = $this->home($t);
			$t->add($this->response->html->thisfile, "thisfile");
			$stop = 1;
		}

		if (isset($_GET['report']) && $_GET['report'] == 'report_bills') {
			$t = $this->response->html->template("./tpl/report-bills.tpl.php");
			$t = $this->home($t);
			$t->add($this->response->html->thisfile, "thisfile");
			$stop = 1;
		}

		if (isset($_GET['report']) && $_GET['report'] == 'report_explorer') {
			$t = $this->response->html->template("./tpl/report-explorer.tpl.php");
			$t = $this->home($t);
			$t->add($this->response->html->thisfile, "thisfile");
			$stop = 1;
		}

		if (isset($_GET['report']) && $_GET['report'] == 'report_budget') {
			$t = $this->response->html->template("./tpl/report-budget.tpl.php");
			$t = $this->home($t);
			$t->add($this->response->html->thisfile, "thisfile");
			$stop = 1;
		}

		if (isset($_GET['report']) && $_GET['report'] == 'report_budget_create') {
			$t = $this->response->html->template("./tpl/report-budget-create.tpl.php");
			$t = $this->home($t);
			$t->add($this->response->html->thisfile, "thisfile");
			$stop = 1;
		}

		if ($stop == 0) { 
			$t = $this->response->html->template("./tpl/cloud-ui.home.tpl.php");
			$t = $this->home($t);
			$t->add($this->response->html->thisfile, "thisfile");
		}
			
		/* user cloudconfig ip-mgmt to get the first ip range */
		// check ip-mgmt
		$show_ip_mgmt = $this->cloudconfig->get_value_by_key('ip-management'); // ip-mgmt enabled ?
		$ip_mgmt_name = '';
		$ip_mgmt_list_per_user_arr = array();
		
		if (!strcmp($show_ip_mgmt, "true")) {

			require_once $this->rootdir."/plugins/ip-mgmt/class/ip-mgmt.class.php";
			$ip_mgmt = new ip_mgmt();
			$ip_mgmt_list_per_user = $ip_mgmt->get_list_by_user($this->clouduser->cg_id);
			array_pop($ip_mgmt_list_per_user);

			foreach($ip_mgmt_list_per_user as $list) {
				$ip_mgmt_id = $list['ip_mgmt_id'];
				$ip_mgmt_name = trim($list['ip_mgmt_name']);
				$ip_mgmt_address = trim($list['ip_mgmt_address']);
				$ip_mgmt_list_per_user_arr[] = array("value" => $ip_mgmt_id, "label" => $ip_mgmt_address.' ('.$ip_mgmt_name.')', "address" => $ip_mgmt_address);
			}
		
			if (!empty($ip_mgmt_list_per_user_arr) && is_array($ip_mgmt_list_per_user_arr)) {
				$t->add($ip_mgmt_id, 'ip_mgmt_id');
				$t->add($ip_mgmt_name, 'ip_mgmt_name');
				$t->add($ip_mgmt_list_per_user_arr[0]['address'], 'ip_mgmt_range_start');
				$t->add(end($ip_mgmt_list_per_user_arr)['address'], 'ip_mgmt_range_end');
				$t->add(date('F Y'), 'current_month');
			}

		}

		/* end user cloudconfig ip-mgmt to get the first ip range  */
	

		return $t;
	}

	//--------------------------------------------
	/**
	 * Cloud Users Home
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function home($t) {
	
		// Limits
		$t->add($this->lang['home']['label_limits'], "label_limits");
		$t->add($this->lang['home']['limit_resource'], "limit_resource");
		$t->add($this->cloud_limits->max('resource'), "resource_limit_value");
		$t->add($this->lang['home']['limit_memory'], "limit_memory");
		$t->add(round(($this->cloud_limits->max('memory') / 1000), 2, PHP_ROUND_HALF_DOWN).' GB', "memory_limit_value");
		$t->add($this->lang['home']['limit_disk'], "limit_disk");
		$t->add(round(($this->cloud_limits->max('disk') / 1000), 2, PHP_ROUND_HALF_DOWN).' GB', "disk_limit_value");
		$t->add($this->lang['home']['limit_cpu'], "limit_cpu");
		$t->add($this->cloud_limits->max('cpu'), "cpu_limit_value");
		$t->add($this->lang['home']['limit_network'], "limit_network");
		$t->add($this->cloud_limits->max('network'), "network_limit_value");

		// js translation
		$t->add($this->lang['home']['limit_resource'], 'lang_systems');
		$t->add($this->lang['home']['limit_disk'], 'lang_disk');
		$t->add($this->lang['home']['limit_memory'], 'lang_memory');
		$t->add($this->lang['home']['limit_cpu'], 'lang_cpu');
		$t->add($this->lang['home']['limit_network'], 'lang_network');

		return $t;
	}

}
?>
