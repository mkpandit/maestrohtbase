<?php
/**
 * IP Management Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class ip_mgmt_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ip_mgmt';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ip_mgmt_msg';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'ip_mgmt_id';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ip_mgmt_tab';
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
var $lang = array(
	'ip_mgmt_manager' => 'IP Address Manager',
	'ip_mgmt_networks' => 'Networks',
	'ip_mgmt_add' => 'Add a new network',
	'ip_mgmt_appliances' => 'Appliances',
	'ip_mgmt_name' => 'Name',
	'ip_mgmt_details' => 'Details',
	'ip_mgmt_update' => 'Update',
	'ip_mgmt_actions' => 'Actions',
	'ip_mgmt_configure' => 'Configure',
	'ip_mgmt_delete' => 'Delete',
	'ip_mgmt_deleted' => 'Deleted',
	'ip_mgmt_network_1' => 'First IP',
	'ip_mgmt_network_2' => 'Last IP',
	'ip_mgmt_subnet' => 'Subnet',
	'ip_mgmt_broadcast' => 'Broadcast',
	'ip_mgmt_gateway' => 'Gateway',
	'ip_mgmt_dns1' => 'DNS 1',
	'ip_mgmt_dns2' => 'DNS 2',
	'ip_mgmt_domain' => 'Domain',
	'ip_mgmt_vlan_id' => 'Vlan ID',
	'ip_mgmt_comment' => 'Comment',
	'ip_mgmt_user_id' => 'User ID',
	'ip_mgmt_appliance_id' => 'Appliance ID',
	'ip_mgmt_nic_id' => 'Nic ID',
	'ip_mgmt_state' => 'State',
	'ip_mgmt_address' => 'Adress',
	'ip_mgmt_id' => 'ID',
	'ip_mgmt_token' => 'Token',
	'ip_mgmt_network' => 'Network',
	'ip_mgmt_name_in_use' => 'Name already in use!',
	'ip_mgmt_second_ip_must_be_bigger_than_first' => "Second ip-address must be bigger than the first one!",
	'ip_mgmt_insert_successful' => 'Successful inserted ip-addresses.',
	'ip_mgmt_update_successful' => 'Successful updated ip-addresses.',
	'ip_mgmt_name_invalid' => 'The name is invalid!',
	'ip_mgmt_kernel' => 'Kernel',
	'ip_mgmt_image' => 'Image',
	'ip_mgmt_resource' => 'Resource',
	'ip_mgmt_type' => 'Type',
	'ip_mgmt_appliances_configuration' => 'Appliance IP Configuration for %s',
	'ip_mgmt_appliance_configuration_successful' => 'Successful configured the appliance.',
	'label_details' => 'Details for network %s',
	'label_update' => 'Update network',
	'label_insert' => 'Add a new network',
	'label_delete' => 'Delete network(s)',
	'error_ip_invalid' => 'No valip ip',
	'error_subnet_invalid' => 'No subnet ip',
	'error_network_in_use' => 'Network %s is in use',
	'error_ips_in_use' => 'Ips in use by network %s',
	'error_last_ip_to_small' => 'Last ip must be larger than first ip',
	'error_last_ip_to_large' => "Last ip must be smaller than broadcast (%s)",
	'error_octet_invalid' => "%s octet (%s) is not valid",
	'error_inside_range' => "IP must not be within %s -%s",
	'error_octet_0' => "%s octet can not be 0",
	'error_octet_255' => "%s octet can not be 255",
	'error_octet_255_only' => "%s octet allows 255 only",
);

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
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->basedir  = $this->htvcenter->get('basedir');
		$this->tpldir   = $this->rootdir.'/plugins/ip-mgmt/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/ip-mgmt/lang", 'ip-mgmt.ini');
		require_once($this->htvcenter->get('basedir').'/plugins/ip-mgmt/web/class/ip-mgmt.class.php');
		$this->ip_mgmt = new ip_mgmt();
		$this->htvcenter->lc();
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
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
				$content[] = $this->applianceselect(false);
			break;
			case 'insert':
				$content[] = $this->insert(true);
				$content[] = $this->select(false);
				$content[] = $this->applianceselect(false);
			break;
			case 'update':
				$content[] = $this->update(true);
				$content[] = $this->select(false);
				$content[] = $this->applianceselect(false);
			break;
			case 'delete':
				$content[] = $this->delete(true);
				$content[] = $this->select(false);
				$content[] = $this->applianceselect(false);
			break;
			case 'details':
				$content[] = $this->details(true);
				$content[] = $this->select(false);
				$content[] = $this->applianceselect(false);
			break;
			case 'applianceselect':
				$content[] = $this->select(false);
				$content[] = $this->applianceselect(true);
			break;
			case 'configure':
				$content[] = $this->select(false);
				$content[] = $this->applianceselect(false);
				$content[] = $this->configure(true);
			break;

		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Select a network
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.select.class.php');
			$controller = new ip_mgmt_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->ip_mgmt         = $this->ip_mgmt;
			$controller->lang            = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['ip_mgmt_networks'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add a new network
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function insert( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.insert.class.php');
			$controller = new ip_mgmt_insert($this->htvcenter, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir        = $this->rootdir.'/plugins/ip-mgmt/tpl';
			$controller->ip_mgmt       = $this->ip_mgmt;
			$controller->lang          = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['ip_mgmt_add'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'insert' );
		$content['onclick'] = false;
		if($this->action === 'insert'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Update a network
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.update.class.php');
			$response = $this->response;
			$response->params = $this->response->params;
			$response->params[$this->identifier_name] = $this->response->html->request()->get($this->identifier_name);

			$controller = new ip_mgmt_update($this->htvcenter, $this->response, $this);
			$controller->message_param   = $this->message_param;
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->rootdir.'/plugins/ip-mgmt/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['ip_mgmt_update'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Delete a network
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function delete( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.delete.class.php');
			$controller = new ip_mgmt_delete($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->rootdir.'/plugins/ip-mgmt/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param   = $this->message_param;
			$controller->ip_mgmt         = $this->ip_mgmt;
			$controller->lang            = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['ip_mgmt_delete'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'delete' );
		$content['onclick'] = false;
		if($this->action === 'delete'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Details
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function details( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.details.class.php');
			$response = $this->response;
			$response->params = $this->response->params;
			$response->params[$this->identifier_name] = $this->response->html->request()->get($this->identifier_name);
			$controller = new ip_mgmt_details($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->ip_mgmt         = $this->ip_mgmt;
			$controller->lang            = $this->lang;
			$data = $controller->action();
		}

		$request_params = $response->get_array($this->actions_name, 'details' );

		$content['label']   = $this->lang['ip_mgmt_details'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $request_params;
		$content['hidden']  = true;
		$content['onclick'] = false;
		if($this->action === 'details'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Select an appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function applianceselect( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.applianceselect.class.php');
			$controller = new ip_mgmt_applianceselect($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->ip_mgmt         = $this->ip_mgmt;
			$controller->lang            = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['ip_mgmt_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'applianceselect' );
		$content['onclick'] = false;
		if($this->action === 'applianceselect'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Configure an appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function configure( $hidden = true ) {
		$data = '';
		$response = $this->response;
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ip-mgmt/class/ip-mgmt.configure.class.php');
			$response->params = $this->response->params;
			$response->params[$this->identifier_name] = $this->response->html->request()->get($this->identifier_name);
			$controller = new ip_mgmt_configure($this->htvcenter, $this->response, $this);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->rootdir.'/plugins/ip-mgmt/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['ip_mgmt_configure'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $response->get_string($this->actions_name, 'configure' );
		$content['onclick'] = false;
		if($this->action === 'configure'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Calculate IPs
	 *
	 * @access protected
	 * @return array
	 */
	//--------------------------------------------
	function __get_ips($first, $subnet, $name) {

		$last    = $this->response->html->request()->get('ip_mgmt_network_2');
		$gateway = $this->response->html->request()->get('ip_mgmt_gateway');
		$mask    = $subnet;

		if($this->response->html->request()->get('ip_mgmt_broadcast') === '') {
			$broadcast = $this->ip_mgmt->broadcast($first, $mask);
		} else {
			$broadcast = $this->response->html->request()->get('ip_mgmt_broadcast');
		}

		// check first ip
		$ip = explode('.', $first);
		if(count($ip) === 4) {
			foreach($ip as $k => $v) {
				$v = intval($v);
				if($v > 255) {
					$data['error']['ip_mgmt_network_1'] = $this->lang['error_ip_invalid'];
					break;
				}
				if($k === 3) {
					if($v === 0) {
						$data['error']['ip_mgmt_network_1'] = sprintf($this->lang['error_octet_0'], '4.');
						break;
					}
				}
			}
		} else {
			$data['error']['ip_mgmt_network_1'] = $this->lang['error_ip_invalid'];
		}
	
		// check subnet
		// possible values for subnetmask
		// 0, 128, 192, 224, 240, 248, 252, 254, 255
		// first octet not 0
		$subnet_values = array(0,128,192,224,240,248,252,254,255);
		$subnet = explode('.', $mask);
		if(count($subnet) === 4) {
			foreach($subnet as $k => $v) {
				$v = intval($v);
				if($k === 0) {
					if($v !== 255) {
						$data['error']['ip_mgmt_subnet'] = sprintf($this->lang['error_octet_255_only'], '1.');
					}
				}
				if($k === 1) {
					if(!in_array($v, $subnet_values) || (intval($subnet[0]) !== 255 && $v !== 0)) {
						$data['error']['ip_mgmt_subnet'] = sprintf($this->lang['error_octet_invalid'], '2.', $v);
						break;
					}
				}
				if($k === 2) {
					if(!in_array($v, $subnet_values) || (intval($subnet[1]) !== 255 && $v !== 0)) {
						$data['error']['ip_mgmt_subnet'] = sprintf($this->lang['error_octet_invalid'], '3.', $v);
						break;
					}
				}
				if($k === 3) {
					if(!in_array($v, $subnet_values) || (intval($subnet[2]) !== 255 && $v !== 0)) {
						$data['error']['ip_mgmt_subnet'] = sprintf($this->lang['error_octet_invalid'], '4.', $v);
					}
				}
			}
		} else {
			$data['error']['ip_mgmt_subnet'] = $this->lang['error_subnet_invalid'];
		}
		

		// check last ip
		if(isset($last) && $last !== '') {
			$ip = explode('.', $last);
			if(count($ip) === 4) {
				// check first ip is greater than last
				if(bindec($this->ip_mgmt->ip2bin($last)) > bindec($this->ip_mgmt->ip2bin($first))) {
					if(bindec($this->ip_mgmt->ip2bin($last)) < bindec($this->ip_mgmt->ip2bin($broadcast))) {
						foreach($ip as $k => $v) {
							$v = intval($v);
							if($v > 255) {
								$data['error']['ip_mgmt_network_2'] = $this->lang['error_ip_invalid'];
								break;
							}
							if($k === 3) {
								if($v === 255) {
									$data['error']['ip_mgmt_network_2'] = sprintf($this->lang['error_octet_255'], '4.');
									break;
								}
							}
						}
					} else {
						$data['error']['ip_mgmt_network_2'] = sprintf($this->lang['error_last_ip_to_large'], $broadcast);
					}
				} else {
					$data['error']['ip_mgmt_network_2'] = $this->lang['error_last_ip_to_small'];
				}
			} else {
				$data['error']['ip_mgmt_network_2'] = $this->lang['error_ip_invalid'];
			}
		} else {
			$last = $this->ip_mgmt->bin2ip(decbin(bindec($this->ip_mgmt->ip2bin($broadcast))-1));
		}

		// check gateway
		if($gateway !== '') {
			$ip = explode('.', $gateway);
			if(count($ip) === 4) {
				foreach($ip as $k => $v) {
					$v = intval($v);
					if($v > 255) {
						$data['error']['ip_mgmt_gateway'] = $this->lang['error_ip_invalid'];
						break;
					}
					if($k === 3) {
						if($v === 0) {
							$data['error']['ip_mgmt_gateway'] = sprintf($this->lang['error_octet_0'], '4.');
							break;
						}
					}
				}
			} else {
				$data['error']['ip_mgmt_gateway'] = $this->lang['error_ip_invalid'];
			}
			// check gateway fits
			if(!isset($data['error']['ip_mgmt_gateway'])) {
				$f = bindec($this->ip_mgmt->ip2bin($first));
				$l = bindec($this->ip_mgmt->ip2bin($last));
				$b = bindec($this->ip_mgmt->ip2bin($broadcast));
				$g = bindec($this->ip_mgmt->ip2bin($gateway));
				if($g > $b) {
					$data['error']['ip_mgmt_gateway'] = sprintf($this->lang['error_last_ip_to_large'], $broadcast);
				}
				elseif($g >= $f && $g <= $l ) {
					$data['error']['ip_mgmt_gateway'] = sprintf($this->lang['error_inside_range'], $first, $last);
				}
			}
		}

		if(!isset($data['error'])) {
			// check ip range is free
			$check = $this->ip_mgmt->ip_exists($first);
			if($check) {
				$n = array_shift($check);
				if($n['ip_mgmt_name'] !== $name) {
					$data['error']['ip_mgmt_network_1'] = sprintf($this->lang['error_ips_in_use'], $n['ip_mgmt_name']);
				} else {
					if(isset($n['ip_mgmt_user_id']) && $n['ip_mgmt_user_id'] !== '-1') {
						$data['error']['ip_mgmt_name'] = sprintf($this->lang['error_network_in_use'], $name);
					}
				}
			}
			$check = $this->ip_mgmt->ip_exists($last);
			if($check) {
				$n = array_shift($check);
				if($n['ip_mgmt_name'] !== $name) {
					$data['error']['ip_mgmt_network_2'] = sprintf($this->lang['error_ips_in_use'], $n['ip_mgmt_name']);
				} else {
					if(isset($n['ip_mgmt_user_id']) && $n['ip_mgmt_user_id'] !== '-1') {
						$data['error']['ip_mgmt_name'] = sprintf($this->lang['error_network_in_use'], $name);
					}
				}
			}
		}

		if(!isset($data['error'])) {
			$networks = $this->ip_mgmt->get_list();
			foreach($networks as $key => $network) {
				if($key === $name) {
					if(isset($network['first']['ip_mgmt_user_id']) && $network['first']['ip_mgmt_user_id'] !== '-1') {
						$data['error']['ip_mgmt_name'] = sprintf($this->lang['error_network_in_use'], $key);
						break;
					}
				} elseif(
					bindec($this->ip_mgmt->ip2bin($network['first']['ip_mgmt_address'])) > bindec($this->ip_mgmt->ip2bin($first)) &&
					bindec($this->ip_mgmt->ip2bin( $network['last']['ip_mgmt_address'])) < bindec($this->ip_mgmt->ip2bin($last))
				){
					$data['error']['ip_mgmt_name'] = sprintf($this->lang['error_ips_in_use'], $key);
					break;
				}
			}
		}


		#echo 'First: '.$first.'<br>';
		#echo 'Netmask: '. $mask.'<br><br>';
		#echo 'Network: '.$this->ip_mgmt->network($first, $mask).'<br>';
		#echo 'Broadcast: '. $broadcast.'<br><br>';
		#echo 'Last: '.$last.'<br><br>';

		// handle errors
		if(isset($data['error'])) {
			// return errors
			return $data;
		} else {
			$f = explode('.', $first);
			$l = explode('.', $last);
			$ips = array();
			// Class A
			if($l[1] > $f[1]) {
				for($i = $f[1]; $i <= $l[1]; $i++) {
					for($j = 1; $j <= 255; $j++) {
						for($k = 1; $k <= 255; $k++) {
							if($i == $f[1] && $j == $f[2]) {
								if($k >= $f[3]) {
									$ips[] = $f[0].'.'.$i.'.'.$j.'.'.$k;
								}
							}
							elseif($i == $l[1] && $j == $l[2]) {
								if($k <= $l[3]) {
									$ips[] = $f[0].'.'.$i.'.'.$j.'.'.$k;
								}
							} 
							else {
								if($i == $f[1] && $j > $f[2]) {
									$ips[] = $f[0].'.'.$i.'.'.$j.'.'.$k;
								}
								elseif($i == $l[1] && $j < $l[2]) {
									$ips[] = $f[0].'.'.$i.'.'.$j.'.'.$k;
								}
								elseif($i != $f[1] && $i < $l[1]) {
									$ips[] = $f[0].'.'.$i.'.'.$j.'.'.$k;
								}
							}
						}
					}
				}
			}
			// Class B
			elseif($l[2] > $f[2]) {
				for($j = $f[2]; $j <= $l[2]; $j++) {
					for($k = 1; $k <= 255; $k++) {
						if($j == $f[2] && $j != $l[2]) {
							if($k >= $f[3]) {
								$ips[] = $f[0].'.'.$f[1].'.'.$j.'.'.$k;
							}
						}
						elseif($j == $l[2]) {
							if($k <= $l[3]) {
								$ips[] = $f[0].'.'.$f[1].'.'.$j.'.'.$k;
							}
						} else {
							$ips[] = $f[0].'.'.$f[1].'.'.$j.'.'.$k;
						}
					}
				}
			}
			// Class C
			elseif($l[3] > $f[3]) {
				for($k = $f[3]; $k <= $l[3]; $k++) {
					$ips[] = $f[0].'.'.$f[1].'.'.$f[2].'.'.$k;
				}
			}
			return $ips;
		}
	}

}
?>
