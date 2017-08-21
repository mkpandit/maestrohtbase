<?php
/**
 * Hyper-V Host auto-discovery Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_discovery_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_discovery_id';
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
	'select' => array (
		'tab' => 'Hyper-V Hosts auto-discovery',
		'label' => 'Discovered Hyper-V Hosts',
		'action_rescan' => 'Discover Hyper-V Hosts',
		'action_remove' => 'remove',
		'action_remove_title' => 'Remove Host from htvcenter',
		'action_add_manual' => 'Manual add Hyper-V Hosts',
		'action_add' => 'add',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_mac' => 'MAC',
		'table_ip' => 'IP',
		'table_hostname' => 'Hostname',
		'table_user' => 'User',
		'table_password' => 'Password',
		'table_comment' => 'Comment',
		'error_no_storage' => '<b>No Hyper-V Host appliance configured yet!</b><br><br>Please create a Hyper-V Host first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Auto-Discovering Hyper-V Server. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Hyper-V Host',
		'label' => 'Add Hyper-V Host to htvcenter',
		'form_name' => 'Name',
		'mac_address' => 'MAC Address',
		'ip_address' => 'IP Address',
		'hostname' => 'Hostname',
		'domainname' => 'Domainname',
		'user' => 'User',
		'password' => 'Password',
		'comment' => 'Comment',
		'msg_added' => 'Added Hyper-V Host %s',
		'no_id_given' => 'Please select an Hyper-V Host',
		'error_exists' => 'Hyper-V Host %s allready integrated',
		'error_storage_exists' => 'A storage named %s allready exists',
		'error_image_exists' => 'An image named %s allready exists',
		'error_server_exists' => 'A server named %s allready exists',
		'error_integrating' => 'Error connecting to Hyper-V Host %s with the given username and password',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Hyper-V Host. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove Hyper-V Host(s) from htvcenter',
		'label' => 'Remove Hyper-V Host(s) from htvcenter',
		'msg_removed' => 'Removed Hyper-V Host %s',
		'please_wait' => 'Removing Hyper-V Host(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
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
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/hyperv/lang", 'hyperv-discovery.ini');
		$this->tpldir   = $this->rootdir.'/plugins/hyperv/tpl';
		require_once $this->basedir."/plugins/hyperv/web/class/hyperv-discovery.class.php";
		$this->discovery = new hyperv_discovery();
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
		$hyperv_discovery = new hyperv_discovery();
		$discovered_hyperv_hosts = $this->discovery->get_count();
//		if ($discovered_hyperv_hosts < 1) {
//			$this->action = "rescan";
//		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->add(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case 'rescan':
				$this->__rescan();
				$this->action = 'select';
				$content[] = $this->select(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}


	//--------------------------------------------
	/**
	 * Select discovered Hyper-V Host for integration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-discovery.select.class.php');
			$controller = new hyperv_discovery_select($this->htvcenter, $this->response);
			$controller->discovery      = $this->discovery;
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
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
	 * Integrates a new discovered Hyper-V Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-discovery.add.class.php');
			$controller                = new hyperv_discovery_add($this->htvcenter, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['add'];
			$controller->rootdir       = $this->rootdir;
			$controller->prefix_tab    = $this->prefix_tab;
			$controller->discovery      = $this->discovery;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove hyperv Host from discovery
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-discovery.remove.class.php');
			$controller                  = new hyperv_discovery_remove($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['remove'];
			$controller->discovery      = $this->discovery;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['remove']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Rescan for Hyper-V Host, triggers auto-discovery
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __rescan() {
		require_once $this->rootdir."/plugins/hyperv/class/hyperv-discovery.class.php";
		$hyperv_discovery = new hyperv_discovery();
		$command  = $this->basedir."/plugins/hyperv/bin/htvcenter-hyperv-autodiscovery discover";
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode fork';
		$file = $this->rootdir.'/plugins/hyperv/hyperv-stat/autodiscovery_finished';
		if(file_exists($file)) {
			unlink($file);
		}
		$htvcenter_server = new htvcenter_server();
		$htvcenter_server->send_command($command, NULL, true);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		// read discovery file
		if(file_exists($file)) {
			$lines = explode("\n", file_get_contents($file));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						$hyperv_ip = $line[0];
						$hyperv_mac = strtolower($line[1]);
						// check if discovered already
						if ((strlen($hyperv_mac)) && (strlen($hyperv_ip))) {
							if (($hyperv_discovery->mac_discoverd_already($hyperv_mac)) && ($hyperv_discovery->ip_discoverd_already($hyperv_ip))) {
								$hyperv_comment = "Added by auto-discovery";
								$hyperv_discovery_fields['hyperv_ad_mac'] = $hyperv_mac;
								$hyperv_discovery_fields['hyperv_ad_ip'] = $hyperv_ip;
								$hyperv_discovery_fields['hyperv_ad_hostname'] = $hyperv_ip;
								$hyperv_discovery_fields['hyperv_ad_user'] = "root";
								$hyperv_discovery_fields['hyperv_ad_password'] = "";
								$hyperv_discovery_fields['hyperv_ad_comment'] = $hyperv_comment;
								$hyperv_discovery_fields['hyperv_ad_is_integrated '] = 0;
								$hyperv_discovery->add($hyperv_discovery_fields);
							}
							unset($hyperv_mac);
							unset($hyperv_ip);
						}
					}
				}
			}
		}
		if(file_exists($file)) {
			unlink($file);
		}
		return true;
	}

}
?>
