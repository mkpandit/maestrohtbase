<?php
/**
 * VMware vSphere Host Controller
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_vm_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_id';
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
		'tab' => 'vSphere Hosts',
		'label' => 'Select vSphere Host',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_comment' => 'Comment',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'edit' => array (
		'tab' => 'VM Manager',
		'label' => 'Virtual Machines on vSphere Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_update' => 'Update',
		'action_add_local_vm' => 'Add local VM',
		'action_add_network_vm' => 'Add network VM',
		'action_import_existing_vms' => 'Import',
		'action_console' => 'Console',
		'action_start' => 'start',
		'action_stop' => 'stop',
		'action_remove' => 'remove',
		'action_import' => 'import',
		'action_clone' => 'clone',
		'action_relocate' => 'relocate',
		'table_name' => 'Name',
		'table_state' => 'Status',
		'table_resource' => 'Resource ID',
		'table_hardware' => 'Hardware',
		'table_datacenter' => 'Datacenter',
		'table_location' => 'Location',
		'table_cluster' => 'Cluster',
		'table_host' => 'Hosts',
		'table_resourcepool' => 'ResourcePool',
		'table_iso' => 'ISO',
		'table_ip' => 'IP',
		'table_network' => 'Network',
		'table_vtype' => 'Type',
		'table_mac' => 'Mac',
		'table_cpu' => 'CPU',
		'table_nic' => 'NIC',
		'table_ram' => 'RAM',
		'table_disk' => 'Disk',
		'table_datastore' => 'DataStore',
		'table_vnc' => 'VNC',
		'table_boot' => 'Boot',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add VM',
		'label' => 'Create Virtual Machine on vSphere Host %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_virtual_disk' => 'Virtual disk image',
		'lang_browse' => 'browse',
		'lang_browser' => 'Filepicker',
		'lang_vnc' => 'VNC',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'form_name' => 'Name',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_disk' => 'New disk (MB)',
		'form_existing_disk' => 'Existing disk',
		'form_swap' => 'Swap',
		'form_datastore' => 'DataStore',
		'form_resourcepool' => 'ResourcePool',
		'form_datacenter' => 'Datacenter',
		'form_vmxversion' => 'VMX Version',
		'form_disktype' => 'Provisioning type',
		'form_enable' => 'enable',
		'form_mac' => 'MAC',
		'form_type' => 'Type',
		'form_vswitch' => 'vSwitch',
		'form_vnc' => 'VNC Password',
		'form_boot_order' => 'Boot Sequence',
		'form_boot_net' => 'Network-Boot',
		'form_boot_local' => 'Local-Boot',
		'form_boot_cd' => 'CD',
		'form_boot_iso' => 'Iso',
		'form_iso_path' => 'Path',
		'form_boot_net' => 'Network',
		'form_boot_local' => 'Local',
		'action_add_vm_image' => 'Add a new VM Image',
		'msg_added' => 'Added Virtual Machine %s',
		'error_exists' => 'Virtual Machine %s allready exists',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'error_vnc' => 'VNC Password must be %s',
		'error_disk' => 'Please fill in the size for a new disk or use an existing one',
		'error_disk_size' => 'Disk size must be a number',
		'error_iso_path' => 'Path must not be empty',
		'please_wait' => 'Adding VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'update' => array (
		'tab' => 'Update VM',
		'label' => 'Update Virtual Machine %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_virtual_disk' => 'Virtual disk image',
		'lang_browse' => 'browse',
		'lang_browser' => 'Filepicker',
		'lang_vnc' => 'VNC',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'form_name' => 'Name',
		'form_enable' => 'enable',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_swap' => 'Swap',
		'form_disk' => 'Disk (Swap)',
		'form_datastore' => 'DataStore',
		'form_mac' => 'MAC',
		'form_type' => 'Type',
		'form_vswitch' => 'vSwitch',
		'form_vnc' => 'VNC Password',
		'form_vncport' => 'VNC Port',
		'form_boot_order' => 'Boot Sequence',
		'form_boot_net' => 'Network-Boot',
		'form_boot_local' => 'Local-Boot',
		'form_boot_cd' => 'CD',
		'form_boot_iso' => 'Iso',
		'form_iso_path' => 'Path',
		'form_boot_net' => 'Network',
		'form_boot_local' => 'Local',
		'msg_updated' => 'Updated Virtual Machine %s',
		'error_not_exist' => 'Virtual Machine %s does not exist',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'error_vnc' => 'VNC Password must be %s',
		'error_iso_path' => 'Path must not be empty',
		'please_wait' => 'Updating VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'relocate' => array (
		'tab' => 'Relocate VM',
		'label' => 'Relocate Virtual Machine %s',
		'lang_basic' => 'Basic',
		'lang_location' => 'Location',
		'form_name' => 'Name',
		'form_datastore' => 'DataStore',
		'form_resourcepool' => 'ResourcePool',
		'msg_updated' => 'Relocated Virtual Machine %s',
		'error_not_exist' => 'Virtual Machine %s does not exist',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Relocating VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'tab' => 'Clone VM',
		'label' => 'Clone Virtual Machine from %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Backend',
		'lang_net' => 'Network',
		'lang_net_0' => 'First network card',
		'lang_vnc' => 'VNC',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'form_name' => 'Source',
		'form_clone_name' => 'Target',
		'form_enable' => 'enable',
		'form_datastore' => 'DataStore',
		'form_resourcepool' => 'Resource Pool',
		'form_mac' => 'MAC',
		'form_type' => 'Type',
		'form_vswitch' => 'vSwitch',
		'form_vnc' => 'VNC Password',
		'form_vncport' => 'VNC Port',
		'msg_cloned' => 'Cloning Virtual Machine %s',
		'error_not_exist' => 'Virtual Machine %s does not exist',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'error_vnc' => 'VNC Password must be %s',
		'error_iso_path' => 'Path must not be empty',
		'please_wait' => 'Cloning VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove VM',
		'label' => 'Remove Virtual Machine(s) from vSphere Host %s',
		'msg_removed' => 'Removed Virtual Machine %s',
		'msg_vm_resource_still_in_use' => 'VM %s resource id %s is still in use by server %s',
		'error_in_use' => 'Virtual Machine %s is still in use',
		'please_wait' => 'Removing VM(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'start' => array (
		'tab' => 'Start VM',
		'label' => 'Start Virtual Machine(s) on vSphere Host %s',
		'msg_started' => 'Started Virtual Machine %s',
		'error_in_use' => 'Virtual Machine %s is still in use',
		'please_wait' => 'Starting VM(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'stop' => array (
		'tab' => 'Stop VM',
		'label' => 'Stop Virtual Machine(s) on vSphere Host %s',
		'msg_stopped' => 'Stopped Virtual Machine %s',
		'error_in_use' => 'Virtual Machine %s is still in use',
		'please_wait' => 'Stopping VM(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'import' => array (
		'tab' => 'Import VM',
		'label' => 'Import VM',
		'please_notice' => '<b>Please notice:</b><br>Make sure that the imported VM obtains an IP address via dhcp. If static, a wrong IP Adress could be determined.',
		'msg_imported' => 'Imported VM %s',
	),
	'boot' => array (
		'tab' => 'VM Boot Sequence',
		'label' => 'Set VM Boot Sequence',
		'lang_id' => 'ID',
	)

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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-vsphere/lang", 'vmware-vsphere-vm.ini');
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
			$this->action = "edit";
		}
		if($this->action == '') {
			$this->action = "select";
		}
		if($this->action !== 'select') {
			$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add(true);
			break;
			case 'update':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->update(true);
			break;
			case 'relocate':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->relocate(true);
			break;
			case 'clone':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->clonevm(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove(true);
			break;
			case 'start':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->start(true);
			break;
			case 'stop':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->stop(true);
			break;
			case 'import':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->import(true);
			break;
			case 'boot':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->boot(true);
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
		require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.api.class.php');
		$controller = new vmware_vsphere_vm_api($this);
		$controller->action();
	}



	//--------------------------------------------
	/**
	 * Select vSphere Host for management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
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
	 * Select VMs on vSphere Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.edit.class.php');
				$controller                  = new vmware_vsphere_vm_edit($this->htvcenter, $this->response);
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
	 * Add VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm_components()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.add.class.php');
				$controller                  = new vmware_vsphere_vm_add($this->htvcenter, $this->response, $this);
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
	 * Update VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm_components()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.update.class.php');
				$controller                  = new vmware_vsphere_vm_update($this->htvcenter, $this->response);
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
	 * Relocate VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function relocate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm_components()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.relocate.class.php');
				$controller                  = new vmware_vsphere_vm_relocate($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['relocate'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['relocate']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'relocate' );
		$content['onclick'] = false;
		if($this->action === 'relocate'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Clone VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function clonevm( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm_components()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.clone.class.php');
				$controller                  = new vmware_vsphere_vm_clone($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['clone'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['clone']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'clone' );
		$content['onclick'] = false;
		if($this->action === 'clone'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Remove VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.remove.class.php');
				$controller                  = new vmware_vsphere_vm_remove($this->htvcenter, $this->response);
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
			$content['active'] = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Start VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.start.class.php');
				$controller                  = new vmware_vsphere_vm_start($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['start'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['start']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'start' );
		$content['onclick'] = false;
		if($this->action === 'start'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Stop VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.stop.class.php');
				$controller                  = new vmware_vsphere_vm_stop($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['stop'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['stop']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'stop' );
		$content['onclick'] = false;
		if($this->action === 'stop'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Imports existing VMs
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function import( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.import.class.php');
			$controller                  = new vmware_vsphere_vm_import($this->htvcenter, $this->response, $this);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['import'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['import']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'import' );
		$content['onclick'] = false;
		if($this->action === 'import'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Toggles VM boot sequence
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function boot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_vm()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-vm.boot.class.php');
				$controller                  = new vmware_vsphere_vm_boot($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['boot'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['boot']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'boot' );
		$content['onclick'] = false;
		if($this->action === 'boot'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Reload VM states
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_vm() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm post_vm_list -i ".$resource->ip;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_list';
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
	 * Reload VM Configuration components
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_vm_components() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-vm post_vm_components -i ".$resource->ip;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vm_components';
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
