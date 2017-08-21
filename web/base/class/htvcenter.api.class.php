<?php
/**
 * htvcenter Content
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_api
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* absolute path to webroot
* @access public
* @var string
*/
var $rootdir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter_controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->response   = $this->controller->response;
		$this->htvcenter    = $this->controller->htvcenter;
		$this->file       = $this->controller->htvcenter->file();
		$this->user       = $this->controller->htvcenter->user();

		$this->htvcenter->init();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get('action');
		switch( $action ) {
			case 'get_top_status':
				$this->get_top_status();
			break;
			case 'get_info_box':
				$this->get_info_box();
			break;
			case 'set_language':
				$this->set_language();
			break;
			case 'plugin':
				$this->plugin();
			break;
			case 'base':
				$this->base();
			break;
			case 'lock':
				$this->lock();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get values for top status
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_top_status() {
		$appliance = new appliance();
		$appliance_all = $appliance->get_count();
		$appliance_active = $appliance->get_count_active();

		$resource = new resource();
		$resource_all = $resource->get_count("all");
		$resource_active = $resource->get_count("online");
		$resource_inactive = $resource->get_count("offline");
		$resource_error = $resource->get_count("error");

		$event = new event();
		$event_error_count = $event->get_count('error');
		$event_active_count = $event->get_count('active');
		echo $appliance_all."@".$appliance_active."@".$resource_all."@".$resource_active."@".$resource_inactive."@".$resource_error."@".$event_error_count."@".$event_active_count;
	}

	//--------------------------------------------
	/**
	 * Set language
	 *
	 * @access public
	 */
	//--------------------------------------------
	function set_language() {
		$name = $this->response->html->request()->get('user');
		$lang = $this->response->html->request()->get('lang');
		$user = new user($name);
		$user->set_user_language($name, $lang);
	}


	//--------------------------------------------
	/**
	 * Get values for info box
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_info_box() {
		$now = $_SERVER['REQUEST_TIME'];
		if ($this->htvcenter->l[3] == 0) {
			$valid = 'unlimited';
		} else {
			$valid = date("F j, Y", $this->htvcenter->l[3]);
		}
		$bd = $this->htvcenter->get('baseurl');
		$green = '<img src="'.$bd.'/img/active_small.png" alt="Active" title="Active">';
		$red = '<img src="'.$bd.'/img/error_small.png" alt="Expired" title="Expired">';
		$yellow = '<img src="'.$bd.'/img/transition_small.png" alt="Expiring soon" title="Expiring soon">';
		$icon = $red;
		$ex = '';
		echo '<p class="justify">';
		echo "htvcenter Enterprise developed by HTBase Corp.<br>
			All source code and content (c) Copyright 2015, htvcenter Enterprise unless specifically noted otherwise. This source code is released under the htvcenter Enterprise Server and Client License	unless otherwise agreed with HTBase Corp.
			. By using this software, you acknowledge having read this license and agree to be bound thereby.<br>";
		echo "</p>";
		echo "<br><b>htvcenter Enterprise Server License</b>";
		echo "<hr>";
//		echo "License Number: ".$this->htvcenter->l[0]."<br>";
		echo "License Version: ".$this->htvcenter->l[1];
		echo "<br>Included Clients: ".$this->htvcenter->l[2];
		echo "<br>Server valid until: ".$valid;
		echo "<br>";
		echo "<br>";
		echo "<b>htvcenter Enterprise Client License(s)</b>";
		echo "<hr>";
		echo "Total valid Clients License(s): ".$this->htvcenter->tc;
		echo "<br>";
		echo "Clients License(s) details:";
		echo "<br>";
		echo "<br>";

		if (isset($this->htvcenter->c)) {
			foreach ($this->htvcenter->c as $c) {
				if ($c[1] == $this->htvcenter->l[1]) {
					$soon = $c[3] - $this->htvcenter->cd;
					if ($c[3] < $now) {
						$icon = $red;
						$ex = 'expired at';
					} else {
						if ($now > $soon) {
							$icon = $yellow;
							$ex = 'expiring soon';
						} else {
							$icon = $green;
							$ex = 'expiry at';
						}
					}
					echo "<nobr>$icon Additional Clients: ".$c[2]." (".$ex.": ".date("F j, Y", $c[3]).")</nobr>";
					echo "<br>";
				}
			}
		}
		echo "<a class='badge pull-right' href='/htvcenter/base/index.php?upload=true'>Upload license files</a>";
		echo "<a class='hidden badge pull-right green' href='#'>Buy additional licenses</a>";
	}

	//--------------------------------------------
	/**
	 * Load plugins
	 *
	 * @access public
	 */
	//--------------------------------------------
	function plugin() {
		$plugin = $this->response->html->request()->get('plugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';
		$path   = $this->controller->rootdir.'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
		if($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->htvcenter, $this->response);
			if(method_exists($controller, 'api')) {
				$controller->api();
			}
		}
	}

	//--------------------------------------------
	/**
	 * Load Base
	 *
	 * @access public
	 */
	//--------------------------------------------
	function base() {
		$plugin = $this->response->html->request()->get('base');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';
		$path   = $this->controller->rootdir.'/server/'.$plugin.'/class/'.$name.'.controller.class.php';
		if($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->htvcenter, $this->response);
			if(method_exists($controller, 'api')) {
				$controller->api();
			}
		}
	}



	//--------------------------------------------
	/**
	 * global lock
	 *
	 * @access public
	 */
	//--------------------------------------------
	function lock() {
		require_once($this->controller->rootdir.'/class/lock.class.php');
		require_once($this->controller->rootdir.'/class/event.class.php');
		$event = new event();

		$lock_cmd = $this->response->html->request()->get('lock');
		$resource_id = $this->response->html->request()->get('resource_id');
		$section = $this->response->html->request()->get('section');
		if ((!strlen($lock_cmd)) || (!strlen($resource_id)) || (!strlen($section))) {
			$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "htvcenter.api.class.php", "Got empty paramater for lock, section or resource_id!", "", "", 0, 0, 0);
			return;
		}
		$lock = new lock();
		switch( $lock_cmd ) {
			case 'aquire':
				$description = $this->response->html->request()->get('description');
				$token = $this->response->html->request()->get('token');
				$lock_fields['lock_resource_id'] = $resource_id;
				$lock_fields['lock_section'] = $section;
				$lock_fields['lock_description'] = $description;
				$lock_fields['lock_token'] = $token;
				$lock_id = $lock->add($lock_fields);
				if (strlen($lock_id)) {
					echo $lock_id;
					$event->log("lock", $_SERVER['REQUEST_TIME'], 5, "htvcenter.api.class.php", "Section ".$section." is now locked by ".$resource_id."!", "", "", 0, 0, 0);
				} else {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "htvcenter.api.class.php", "Section ".$section." is still locked!", "", "", 0, 0, 0);
				}
			break;

			case 'release':
				$lock->get_instance_by_section($section);
				if (!strlen($lock->id)) {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "htvcenter.api.class.php", "Resource ".$resource_id." trying to remove lock but no lock active for section ".$section, "", "", 0, 0, 0);
					return;
				}
				if ($resource_id == $lock->resource_id) {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 5, "htvcenter.api.class.php", "Resource ".$resource_id." released lock for section ".$section, "", "", 0, 0, 0);
					echo $lock->id;
					$lock->remove_by_section($section);
				} else {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "htvcenter.api.class.php", "Resource ".$resource_id." trying to remove lock from ".$lock->resource_id." for section ".$section, "", "", 0, 0, 0);
				}

			break;
		}
	}




}
?>
