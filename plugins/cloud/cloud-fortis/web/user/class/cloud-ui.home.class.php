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
		$this->htvcenter = $htvcenter;
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
