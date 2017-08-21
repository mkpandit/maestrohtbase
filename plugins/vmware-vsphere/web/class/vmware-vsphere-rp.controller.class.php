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

class vmware_vsphere_rp_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_rp_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_rp_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_rp_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_rp_id';
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
	'edit' => array (
		'tab' => 'ResourcePool Manager',
		'label' => 'ResourcePool on vSphere %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'Add new ResourcePool',
		'action_remove' => 'Remove ResourcePool',
		'action_remove_up' => 'Remove NIC',
		'action_add_up' => 'Add NIC',
		'action_remove' => 'remove',
		'action_update' => 'Edit ResourcePool',
		'table_state' => 'State',
		'table_name' => 'Name',
		'table_rp' => 'ResourcePool',
		'table_vm' => 'Virtual Machines',
		'table_relocate' => 'Relocate VM to another ResourcePool',
		'table_parent' => 'Parent',
		'table_cpu' => 'CPU Configuration',
		'table_memory' => 'Memory Configuration',
		'table_cpuexpandablereservation' => 'Expandable',
		'table_cpureservation' => 'Reservation',
		'table_cpulimit' => 'Limit',
		'table_cpushares' => 'Shares',
		'table_cpulevel' => 'Level',
		'table_cpuoverallusage' => 'Usage',
		'table_cpumaxusage' => 'Max.',
		'table_memoryexpandablereservation' => 'Expandable',
		'table_memoryreservation' => 'Reservation',
		'table_memorylimit' => 'Limit',
		'table_memoryshares' => 'Shares',
		'table_memorylevel' => 'Level',
		'table_memoryoverallusage' => 'Usage',
		'table_memorymaxusage' => 'Max.',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add ResourcePool',
		'label' => 'Add ResourcePool to vSphere %s',
		'form_name' => 'Name',
		'form_parent' => 'Parent ResourcePool',
		'form_cpuexpandableReservation' => 'Expandable',
		'form_cpulimit' => 'Limit',
		'form_cpureservation' => 'Reservation',
		'form_cpushares' => 'Shares',
		'form_cpulevel' => 'Level',
		'form_memoryexpandableReservation' => 'Expandable',
		'form_memorylimit' => 'Limit',
		'form_memoryreservation' => 'Reservation',
		'form_memoryshares' => 'Shares',
		'form_memorylevel' => 'Level',
		'lang_cpu' => 'CPU Configuration',
		'lang_memory' => 'Memory Configuration',
		'msg_added' => 'Added ResourcePool %s',
		'error_exists' => 'ResourcePool %s allready exists',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding ResourcePool. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove ResourcePool',
		'label' => 'Remove ResourcePool(s) from vSphere %s',
		'msg_removed' => 'Removed ResourcePool %s',
		'msg_not_removing' => 'Not removing ResourcePool0',
		'please_wait' => 'Removing ResourcePool(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'update' => array (
		'tab' => 'Update ResourcePool',
		'label' => 'Update ResourcePool on vSphere %s',
		'form_name' => 'Name',
		'form_parent' => 'Parent ResourcePool',
		'form_cpuexpandableReservation' => 'Expandable',
		'form_cpulimit' => 'Limit',
		'form_cpureservation' => 'Reservation',
		'form_cpushares' => 'Shares',
		'form_cpulevel' => 'Level',
		'form_memoryexpandableReservation' => 'Expandable',
		'form_memorylimit' => 'Limit',
		'form_memoryreservation' => 'Reservation',
		'form_memoryshares' => 'Shares',
		'form_memorylevel' => 'Level',
		'lang_cpu' => 'CPU Configuration',
		'lang_memory' => 'Memory Configuration',
		'msg_added' => 'Added ResourcePool %s',
		'error_exists' => 'ResourcePool %s does not exist',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Updating ResourcePool. Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-vsphere/lang", 'vmware-vsphere-rp.ini');
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
			$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
			if($this->action !== 'edit') {
				$this->response->add('resourcepool', $this->response->html->request()->get('resourcepool'));
			}
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'edit':
				$content[] = $this->edit(true);
			break;
			case 'add':
				$content[] = $this->edit(false);
				$content[] = $this->add(true);
			break;
			case 'update':
				$content[] = $this->edit(false);
				$content[] = $this->update(true);
			break;
			case 'remove':
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
		require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere.vm-api.class.php');
		$controller = new vmware_vsphere_vm_api($this);
		$controller->action();
	}




	//--------------------------------------------
	/**
	 * ResourcPool management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_rp()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-rp.edit.class.php');
				$controller                  = new vmware_vsphere_rp_edit($this->htvcenter, $this->response);
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
	 * Add ResourcePool
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_rp()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-rp.add.class.php');
				$controller                  = new vmware_vsphere_rp_add($this->htvcenter, $this->response);
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
	 * Remove ResourcePool
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_rp()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-rp.remove.class.php');
				$controller                  = new vmware_vsphere_rp_remove($this->htvcenter, $this->response);
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
	 * Configure ResourcePool
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_rp()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-rp.update.class.php');
				$controller                  = new vmware_vsphere_rp_update($this->htvcenter, $this->response);
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
	 * Reload ResourcePool states
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_rp() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-rp post_rp_list -i ".$resource->ip;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.rp_list';
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
