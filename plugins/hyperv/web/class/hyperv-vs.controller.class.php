<?php
/**
 * Hyper-V Host Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_vs_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_vs_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_vs_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_vs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_vs_id';
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
		'tab' => 'Hyper-V Hosts',
		'label' => 'Select Hyper-V Host',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_comment' => 'Comment',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Network Manager',
		'label' => 'vSwitches on Hyper-V Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'Add new vSwitch',
		'action_remove_up' => 'Remove Uplink',
		'action_add_up' => 'Add Uplink',
		'action_remove' => 'remove',
		'action_update' => 'Update',
		'table_state' => 'State',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_network' => 'Network',
		'table_num_ports' => 'Ports',
		'table_used_ports' => 'Used',
		'table_conf_ports' => 'Conf.',
		'table_mtu' => 'MTU',
		'table_uplink' => 'Uplink',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add vSwitch',
		'label' => 'Add vSwitch to Hyper-V Host %s',
		'form_name' => 'Name',
		'form_ports' => 'Ports',
		'msg_added' => 'Added vSwitch %s',
		'error_exists' => 'vSwitch %s allready exists',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove vSwitch',
		'label' => 'Remove vSwitch(s) from Hyper-V Host %s',
		'msg_removed' => 'Removed vSwitch %s',
		'msg_not_removing' => 'Not removing vSwitch0',
		'please_wait' => 'Removing vSwitch(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'update' => array (
		'tab' => 'Portgroups',
		'label' => 'Configure Portgroups and Uplinks for %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_pg_name' => 'Name',
		'table_pg_vlan' => 'VLAN',
		'table_pg_ports' => 'Ports',
		'table_pg_uplink' => 'Uplink',
		'action_remove' => 'remove',
		'action_add_pg' => 'Add Portgroup to vSwitch',
		'action_add_up' => 'Add Uplink to vSwitch',
		'action_add_pg_up' => 'Add Uplink to Portgroup',
		'action_remove_pg_up' => 'Remove Uplink',
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
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
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
		'tab' => 'Add Uplink',
		'label' => 'Add Uplink to vSwitch %s',
		'label_portgroup' => 'Add Uplink to Portgroup %s on vSwitch %s',
		'lang_name' => 'Name',
		'form_uplink' => 'Uplink',
		'msg_added' => 'Added Uplink  %s to vSwitch',
		'error_exists' => 'Uplink %s allready exists',
		'error_not_exists' => 'vSwitch %s does not exist',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Uplink. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_up' => array (
		'tab' => 'Remove Uplink',
		'label' => 'Remove Uplink from vSwitch %s',
		'label_portgroup' => 'Remove Uplink from Portgroup %s on vSwitch %s',
		'msg_removed' => 'Removed Uplink %s',
		'msg_not_removing' => 'Not removing Uplink %s vom vSwitch0!<br>This is the Management Network-connection.',
		'error_exists' => 'Uplink %s does not exist',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'please_wait' => 'Removing Uplink. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
/*
	'reboot' => array (
		'tab' => 'Reboot Hyper-V Host',
		'label' => 'Reboot Hyper-V Host %s',
		'msg_rebooted' => 'Rebooted Hyper-V Host %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'shutdown' => array (
		'tab' => 'Shutdown Hyper-V Host',
		'label' => 'Shutdown Hyper-V Host %s',
		'msg_shutdown' => 'Powered off Hyper-V Host %s',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/hyperv/lang", 'hyperv-vs.ini');
		$this->tpldir   = $this->rootdir.'/plugins/hyperv/tpl';
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
		if($this->action == '') {
			$this->action = "select";
		}
		if($this->action !== 'select') {
			$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
			if($this->action !== 'edit') {
				$this->response->add('vs_name', $this->response->html->request()->get('vs_name'));
			}
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
		require_once($this->rootdir.'/plugins/hyperv/class/hyperv.vm-api.class.php');
		$controller = new hyperv_vm_api($this);
		$controller->action();
	}



	//--------------------------------------------
	/**
	 * Select Hyper-V Host for management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function __select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			// use hyperv-vm.select.class
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-vm.select.class.php');
			$controller = new hyperv_vm_select($this->htvcenter, $this->response);
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
				require_once($this->rootdir.'/plugins/hyperv/class/hyperv-vs.edit.class.php');
				$controller                  = new hyperv_vs_edit($this->htvcenter, $this->response);
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
			if($this->__reload_ne() && $this->__reload_ne_adapaters()) {
				require_once($this->rootdir.'/plugins/hyperv/class/hyperv-vs.add.class.php');
				$controller                  = new hyperv_vs_add($this->htvcenter, $this->response);
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
				require_once($this->rootdir.'/plugins/hyperv/class/hyperv-vs.remove.class.php');
				$controller                  = new hyperv_vs_remove($this->htvcenter, $this->response);
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
				require_once($this->rootdir.'/plugins/hyperv/class/hyperv-vs.update.class.php');
				$controller                  = new hyperv_vs_update($this->htvcenter, $this->response);
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
	 * Reload Network states
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_ne() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/hyperv/bin/htvcenter-hyperv-network post_net_config -i ".$resource->ip;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode fork';
		$file = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.net_config';
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


	
	//--------------------------------------------
	/**
	 * Reload physical Network adapters
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_ne_adapaters() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/hyperv/bin/htvcenter-hyperv-network post_net_adapters -i ".$resource->ip;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode fork';
		$file = $this->rootdir.'/plugins/hyperv/hyperv-stat/'.$resource->ip.'.net_adapters';
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



// #############################################################################
// ################## Host Management ##########################################
// #############################################################################


	//--------------------------------------------
	/**
	 * Reboot an Hyper-V Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------

/*
	function reboot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv.reboot.class.php');
			$controller                  = new hyperv_reboot($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['reboot'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['reboot']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'reboot' );
		$content['onclick'] = false;
		if($this->action === 'reboot'){
			$content['active']  = true;
		}
		return $content;
	}
*/


	//--------------------------------------------
	/**
	 * Shutdown an Hyper-V Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
/*
	function shutdown( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv.shutdown.class.php');
			$controller                  = new hyperv_shutdown($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['shutdown'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['shutdown']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'shutdown' );
		$content['onclick'] = false;
		if($this->action === 'shutdown'){
			$content['active']  = true;
		}
		return $content;
	}
*/



}
?>
