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

class vmware_vsphere_ds_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_ds_id';
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
		'tab' => 'Datastore Manager',
		'label' => 'Datastores on ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_ds_add_nas' => 'Add new NAS Datastore',
		'action_ds_add_iscsi' => 'Add new iSCSI Datastore',
		'action_ds_remove' => 'remove',
		'action_edit' => 'list VMDKs',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_location' => 'Location',
		'table_filesystem' => 'Filesystem',
		'table_capacity' => 'Capacity',
		'table_available' => 'Available',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add_nas' => array (
		'tab' => 'Add NAS DataStore',
		'label' => 'Add NAS DataStore to ESX Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'msg_added' => 'Added DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding NAS DataStore. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_nas' => array (
		'tab' => 'Remove NAS DataStore',
		'label' => 'Remove NAS DataStore from ESX Host %s',
		'msg_removed' => 'Removed DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_not_exists' => 'DataStore %s does not exists',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Removing NAS DataStore. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add_iscsi' => array (
		'tab' => 'Add iSCSI DataStore',
		'label' => 'Add iSCSI DataStore to ESX Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_subnet' => 'Subnetmask',
		'form_target' => 'Target',
		'form_portgroup' => 'PortGroup',
		'form_vswitch' => 'vSwitch',
		'form_vmk' => 'NIC-Name',
		'msg_added' => 'Added DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'error_no_target' => 'Target name empty',
		'error_no_targetip' => 'Target IP address empty',
		'error_no_portgroup' => 'Portgroup parameter empty',
		'error_no_vswitch' => 'vSwitch parameter empty',
		'error_no_vmk' => 'VNIC parameter empty',
		'error_no_vmk_ip' => 'VNIC IP address empty',
		'error_no_vmk_subnet' => 'VNIC Subnetmask empty',
		'please_wait' => 'Adding iSCSI DataStore. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_iscsi' => array (
		'tab' => 'Remove iSCSI DataStore',
		'label' => 'Remove iSCSI DataStore from ESX Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'msg_removed' => 'Removed DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_not_exists' => 'DataStore %s does not exists',
		'error_no_vsphere' => 'Appliance is not an ESX Server!',
		'please_wait' => 'Removing iSCSI DataStore. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'volgroup' => array (
		'tab' => 'List VMDKs',
		'label' => 'List VMDKs from Datastore %s on ESX Host %s',
		'action_clone' => 'clone',
		'action_remove' => 'remove',
		'table_name' => 'Name',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove VMDK(s)',
		'label' => 'Remove VMDK(s) from Datastore %s on ESX Host %s',
		'msg_removed' => 'Removed VMDK %s',
		'please_wait' => 'Removing VMDK(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'tab' => 'Clone VMDK',
		'label' => 'Clone VMDK %s from Datastore %s on ESX Host %s',
		'form_name' => 'Name',
		'msg_cloned' => 'Cloned VMDK %s to %s',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning VMDK. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-vsphere/lang", 'vmware-vsphere-ds.ini');
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
			if($this->action === 'remove' || $this->action === 'clone') {
				$this->action = "volgroup";
			} else {
				$this->action = "edit";
			}
		}
		if($this->action == '') {
			$this->action = "select";
		}
		if($this->action !== 'select') {
			$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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
			case 'add_nas':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add_nas(true);
			break;
			case 'remove_nas':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove_nas(true);
			break;
			case 'add_iscsi':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add_iscsi(true);
			break;
			case 'remove_iscsi':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove_iscsi(true);
			break;
			case 'volgroup':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(true);
			break;
			case 'remove':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->remove(true);
			break;
			case 'clone':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->duplicate(true);
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
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['select'];
			$data                      = $controller->action();
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
	 * Datastore management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.edit.class.php');
				$controller                  = new vmware_vsphere_ds_edit($this->htvcenter, $this->response);
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
	 * Add NAS DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_nas( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.add-nas.class.php');
				$controller                  = new vmware_vsphere_ds_add_nas($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['add_nas'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['add_nas']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_nas' );
		$content['onclick'] = false;
		if($this->action === 'add_nas'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add iSCSI DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_iscsi( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.add-iscsi.class.php');
				$controller                  = new vmware_vsphere_ds_add_iscsi($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['add_iscsi'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['add_iscsi']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_iscsi' );
		$content['onclick'] = false;
		if($this->action === 'add_iscsi'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove NAS DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove_nas( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.remove-nas.class.php');
				$controller                  = new vmware_vsphere_ds_remove_nas($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['remove_nas'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['remove_nas']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove_nas' );
		$content['onclick'] = false;
		if($this->action === 'remove_nas'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove iSCSI DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove_iscsi( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->__reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.remove-iscsi.class.php');
				$controller                  = new vmware_vsphere_ds_remove_iscsi($this->htvcenter, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['remove_iscsi'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['remove_iscsi']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove_iscsi' );
		$content['onclick'] = false;
		if($this->action === 'remove_iscsi'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * List VMDKs
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function volgroup( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.volgroup.class.php');
			$controller                  = new vmware_vsphere_ds_volgroup($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['volgroup'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['volgroup']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'volgroup' );
		$content['onclick'] = false;
		if($this->action === 'volgroup'){
			$content['active'] = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove VMDKs
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.remove.class.php');
			$controller                  = new vmware_vsphere_ds_remove($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['remove'];
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
	 * Clone VMDK
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function duplicate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-ds.clone.class.php');
				$controller                  = new vmware_vsphere_ds_clone($this->htvcenter, $this->response);
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
	 * Reload DataStore states
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __reload_ds() {
		$host = $this->response->html->request()->get('esxhost');
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datastore post_ds_list -i ".$resource->ip." -e ".$host;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.ds_list';
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
