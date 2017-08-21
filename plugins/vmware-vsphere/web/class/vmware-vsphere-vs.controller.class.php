<?php
/**
 * VMware ESX Host Controller
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vs_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_vs_id';
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
		'tab' => 'ESX Hosts',
		'label' => 'Select ESX Host',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_comment' => 'Comment',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Network Manager',
		'label' => 'vSwitches on ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'Add new vSwitch',
		'action_remove' => 'Remove vSwitch',
		'action_remove_up' => 'Remove NIC',
		'action_add_up' => 'Add NIC',
		'action_remove' => 'remove',
		'action_update' => 'Edit Portgroups',
		'table_state' => 'State',
		'table_name' => 'Name',
		'table_vs' => 'vSwitch',
		'table_pg' => 'Portgroup',
		'table_num_ports' => 'Ports',
		'table_available' => 'Available',
		'table_conf_ports' => 'Conf.',
		'table_mtu' => 'MTU',
		'table_uplink' => 'NIC',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add vSwitch',
		'label' => 'Add vSwitch to ESX Host %s',
		'form_name' => 'Name',
		'form_ports' => 'Ports',
		'form_pnic' => 'Physical NIC',
		'msg_added' => 'Added vSwitch %s',
		'error_exists' => 'vSwitch %s allready exists',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove vSwitch',
		'label' => 'Remove vSwitch(s) from ESX Host %s',
		'msg_removed' => 'Removed vSwitch %s',
		'msg_not_removing' => 'Not removing vSwitch0',
		'please_wait' => 'Removing vSwitch(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'update' => array (
		'tab' => 'Portgroups',
		'label' => 'Configure Portgroups and NICs for %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_pg_name' => 'Name',
		'table_pg_vlan' => 'VLAN',
		'table_pg_ports' => 'Ports',
		'table_pg_uplink' => 'NIC',
		'action_remove' => 'remove',
		'action_add_pg' => 'Add Portgroup to vSwitch',
		'action_add_up' => 'Add NIC to vSwitch',
		'action_add_pg_up' => 'Add NIC to Portgroup',
		'action_remove_pg_up' => 'Remove NIC',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add_pg' => array (
		'tab' => 'Add Portgroup',
		'label' => 'Add Portgroup to vSwitch %s',
		'form_name' => 'Name',
		'form_vlan' => 'VLAN ID',
		'msg_added' => 'Added Portgroup  %s to vSwitch',
		'error_exists' => 'Portgroup %s allready exists',
		'error_not_exists' => 'vSwitch %s does not exist',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Portgroup. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_pg' => array (
		'tab' => 'Remove Portgroup(s)',
		'label' => 'Remove Portgroup(s) from vSwitch %s',
		'msg_removed' => 'Removed Portgroup %s',
		'msg_not_removing' => 'Not removing Portgroup',
		'error_exists' => 'Portgroup %s does not exist',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Removing Portgroup(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add_up' => array (
		'tab' => 'Add NIC',
		'label' => 'Add NIC to vSwitch %s',
		'label_portgroup' => 'Add NIC to Portgroup %s on vSwitch %s',
		'lang_name' => 'Name',
		'form_uplink' => 'NIC',
		'msg_added' => 'Added NIC  %s to vSwitch',
		'error_exists' => 'NIC %s allready exists',
		'error_not_exists' => 'vSwitch %s does not exist',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding NIC. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_up' => array (
		'tab' => 'Remove NIC',
		'label' => 'Remove NIC from vSwitch %s',
		'label_portgroup' => 'Remove NIC from Portgroup %s on vSwitch %s',
		'msg_removed' => 'Removed NIC %s',
		'msg_not_removing' => 'Not removing NIC %s vom vSwitch0!<br>This is the Management Network-connection.',
		'error_exists' => 'NIC %s does not exist',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'please_wait' => 'Removing NIC. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
/*
	'reboot' => array (
		'tab' => 'Reboot ESX Host',
		'label' => 'Reboot ESX Host %s',
		'msg_rebooted' => 'Rebooted ESX Host %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'shutdown' => array (
		'tab' => 'Shutdown ESX Host',
		'label' => 'Shutdown ESX Host %s',
		'msg_shutdown' => 'Powered off ESX Host %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
*/


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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-vsphere/lang", 'vmware-vsphere-vs.ini');
		$this->tpldir   = $this->rootdir.'/plugins/vmware-vsphere/tpl';
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
			if( $this->action == 'add_up' || $this->action == 'remove_up' ) {
				$this->action = "edit";
			}
			else if( $this->action == 'add_pg' || $this->action == 'remove_pg' ) {
				$this->action = "update";
			}
			else {
				if(
					($this->response->html->request()->get('pg_name') !== '' && $this->action !== 'add_up') || 
					($this->response->html->request()->get('pg_name') !== '' && $this->action !== 'add_up') 
				) {
					$this->action = "update";
				} else {
					$this->action = "edit";
				}
			}
		}
		if($this->action == '') {
			$this->action = "select";
		}
		if($this->action !== 'select') {
			$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
			if($this->action !== 'edit') {
				$this->response->add('vs_name', $this->response->html->request()->get('vs_name'));
			}
		}
		if($this->action !== 'select') {
			$this->response->params['esxhost'] = $this->response->html->request()->get('esxhost');
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->__select(true);
			break;
			case 'edit':
				$content[] = $this->__select(false);
				$content[] = $this->edit(true);
			break;
			case 'add':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add(true);
			break;
			case 'update':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->update(true);
			break;
			case 'remove':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove(true);
			break;
			case 'add_pg':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->update(false);
				$content[] = $this->add_pg(true);
			break;
			case 'remove_pg':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->update(false);
				$content[] = $this->remove_pg(true);
			break;

			case 'add_up':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				if($this->response->html->request()->get('pg_name') !== '') {
					$content[] = $this->update(false);
				}
				$content[] = $this->add_up(true);
			break;
			case 'remove_up':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				if($this->response->html->request()->get('pg_name') !== '') {
					$content[] = $this->update(false);
				}
				$content[] = $this->remove_up(true);
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
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere.vm-api.class.php');
		$controller = new vmware_vsphere_vm_api($this);
		$controller->action();
	}



	//--------------------------------------------
	/**
	 * Select ESX Host for management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function __select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			// use vmware-vsphere-vm.select.class
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.select.class.php');
			$controller = new vmware_vsphere_vm_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang           = $this->lang['select'];
			$data                       = $controller->action();
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
	 * Network management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.edit.class.php');
				$controller                  = new vmware_vsphere_vs_edit($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['edit'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['edit']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.add.class.php');
				$controller                  = new vmware_vsphere_vs_add($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['add'];
				$data = $controller->action();
			}
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
	 * Remove vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.remove.class.php');
				$controller                  = new vmware_vsphere_vs_remove($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['remove'];
				$data = $controller->action();
			}
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
	 * Configure vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.update.class.php');
				$controller                  = new vmware_vsphere_vs_update($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['update'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['update']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add PortGroup to vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_pg( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.add-pg.class.php');
				$controller                  = new vmware_vsphere_vs_add_pg($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['add_pg'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['add_pg']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_pg' );
		$content['onclick'] = false;
		if($this->action === 'add_pg'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove PortGroup from vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove_pg( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.remove-pg.class.php');
				$controller                  = new vmware_vsphere_vs_remove_pg($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['remove_pg'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['remove_pg']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove_pg' );
		$content['onclick'] = false;
		if($this->action === 'remove_pg'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add NIC to vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.add-up.class.php');
				$controller                  = new vmware_vsphere_vs_add_up($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['add_up'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['add_up']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_up' );
		$content['onclick'] = false;
		if($this->action === 'add_up'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove NIC from vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove_up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vs.remove-up.class.php');
				$controller                  = new vmware_vsphere_vs_remove_up($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['remove_up'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['remove_up']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove_up' );
		$content['onclick'] = false;
		if($this->action === 'remove_up'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Reload Network states
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_ne() {
		$host = $this->response->html->request()->get('esxhost');
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-network post_net_config -i ".$resource->ip." -e ".$host;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.net_config';
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
		return true;
	}



}
?>
