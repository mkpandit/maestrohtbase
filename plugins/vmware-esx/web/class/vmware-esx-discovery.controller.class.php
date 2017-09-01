<?php
/**
 * VMware ESX Host auto-discovery Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_discovery_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_discovery_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_discovery_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_discovery_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_discovery_id';
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
		'tab' => 'ESX Hosts auto-discovery',
		'label' => 'Discovered ESX Hosts',
		'action_rescan' => 'Discover ESX Hosts',
		'action_remove' => 'remove',
		'action_remove_title' => 'Remove Host from htvcenter',
		'action_add_manual' => 'Manual add ESX Hosts',
		'action_add' => 'add',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_mac' => 'MAC',
		'table_ip' => 'IP',
		'table_hostname' => 'Hostname',
		'table_user' => 'User',
		'table_password' => 'Password',
		'table_comment' => 'Comment',
		'error_no_storage' => '<b>No ESX Host appliance configured yet!</b><br><br>Please create a VMware ESX Host first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Auto-Discovering ESX Server. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add ESX Host',
		'label' => 'Add ESX Host to htvcenter',
		'form_name' => 'Name',
		'mac_address' => 'MAC Address',
		'ip_address' => 'IP Address',
		'hostname' => 'Hostname',
		'domainname' => 'Domainname',
		'user' => 'User',
		'password' => 'Password',
		'comment' => 'Comment',
		'msg_added' => 'Added ESX Host %s',
		'no_id_given' => 'Please select an ESX Host',
		'error_exists' => 'ESX Host %s allready integrated',
		'error_storage_exists' => 'A storage named %s allready exists',
		'error_image_exists' => 'An image named %s allready exists',
		'error_server_exists' => 'A server named %s allready exists',
		'error_integrating' => 'Error connecting to ESX Host %s with the given username and password',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding ESX Host. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove ESX Host(s) from htvcenter',
		'label' => 'Remove ESX Host(s) from htvcenter',
		'msg_removed' => 'Removed ESX Host %s',
		'please_wait' => 'Removing ESX Host(s). Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-esx/lang", 'vmware-esx-discovery.ini');
		$this->tpldir   = $this->rootdir.'/plugins/vmware-esx/tpl';
		require_once $this->basedir."/plugins/vmware-esx/web/class/vmware-esx-discovery.class.php";
		$this->discovery = new vmware_esx_discovery();
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
		$vmware_esx_discovery = new vmware_esx_discovery();
		$discovered_esx_hosts = $this->discovery->get_count();
//		if ($discovered_esx_hosts < 1) {
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
	 * Select discovered ESX Host for integration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.select.class.php');
			$controller = new vmware_esx_discovery_select($this->htvcenter, $this->response);
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
	 * Integrates a new discovered ESX Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.add.class.php');
			$controller                = new vmware_esx_discovery_add($this->htvcenter, $this->response);
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
	 * Remove vmware-esx Host from discovery
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.remove.class.php');
			$controller                  = new vmware_esx_discovery_remove($this->htvcenter, $this->response);
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
	 * Rescan for ESX Host, triggers auto-discovery
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __rescan() {
		require_once $this->rootdir."/plugins/vmware-esx/class/vmware-esx-discovery.class.php";
		$vmware_esx_discovery = new vmware_esx_discovery();
		$command  = $this->basedir."/plugins/vmware-esx/bin/htvcenter-vmware-esx-autodiscovery";
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/autodiscovery_finished';
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
						$esx_ip = $line[0];
						$esx_mac = strtolower($line[1]);
						// check if discovered already
						if ((strlen($esx_mac)) && (strlen($esx_ip))) {
							if (($vmware_esx_discovery->mac_discoverd_already($esx_mac)) && ($vmware_esx_discovery->ip_discoverd_already($esx_ip))) {
								$esx_comment = "Added by auto-discovery";
								$vmware_esx_discovery_fields['vmw_esx_ad_mac'] = $esx_mac;
								$vmware_esx_discovery_fields['vmw_esx_ad_ip'] = $esx_ip;
								$vmware_esx_discovery_fields['vmw_esx_ad_hostname'] = $esx_ip;
								$vmware_esx_discovery_fields['vmw_esx_ad_user'] = "root";
								$vmware_esx_discovery_fields['vmw_esx_ad_password'] = "";
								$vmware_esx_discovery_fields['vmw_esx_ad_comment'] = $esx_comment;
								$vmware_esx_discovery_fields['vmw_esx_ad_is_integrated '] = 0;
								$vmware_esx_discovery->add($vmware_esx_discovery_fields);
							}
							unset($esx_mac);
							unset($esx_ip);
						}
					}
				}
			}
		}
		unlink($file);
		return true;
	}

}
?>
