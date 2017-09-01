<?php
/**
 * VMware vSphere Datacenter Controller
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_dc_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_dc_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_dc_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_dc_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_dc_id';
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
		'tab' => 'Datacenter Manager',
		'label' => 'Datacenters on vSphere Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_dc_add' => 'Add new Datacenter',
		'action_dc_remove' => 'Remove Datacenter',
		'action_edit' => 'list Datacenter',
		'action_dc_add_host' => 'Add Host to Datacenter',
		'action_dc_add_cluster' => 'Add Cluster to Datacenter',
		'action_dc_cluster_add_host' => 'Add Host to Cluster',
		'action_dc_cluster_remove' => 'Remove Cluster',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_cluster' => 'Cluster/Hosts',
		'table_filesystem' => 'Filesystem',
		'table_capacity' => 'Capacity',
		'table_available' => 'Available',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Datacenter',
		'label' => 'Add Datacenter to vSphere Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'msg_added' => 'Added Datacenter %s',
		'error_exists' => 'Datacenter %s allready exists',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Datacenter. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add_host' => array (
		'tab' => 'Add Host to Datacenter',
		'label' => 'Add Host to Datacenter on vSphere Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_user' => 'Username',
		'form_password' => 'Password',
		'msg_added' => 'Added Host %s',
		'error_exists' => 'Host %s allready exists',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Host. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add_hosttocluster' => array (
		'tab' => 'Add Host to Cluster',
		'label' => 'Add Host to Cluster on vSphere Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_user' => 'Username',
		'form_password' => 'Password',
		'msg_added' => 'Added Host %s',
		'error_exists' => 'Host %s allready exists',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Host. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add_cluster' => array (
		'tab' => 'Add Cluster to Datacenter',
		'label' => 'Add Cluster to Datacenter to vSphere Host %s',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_user' => 'Username',
		'form_password' => 'Password',
		'msg_added' => 'Added Cluster to Datacenter %s',
		'error_exists' => 'Cluster %s allready exists',
		'error_no_vsphere' => 'Appliance is not an vSphere Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Cluster to Datacenter. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_cluster' => array (
		'tab' => 'Remove Cluster',
		'label' => 'Remove Cluster from Datacenter %s on vSphere Host %s',
		'msg_removed' => 'Removed Cluster %s',
		'please_wait' => 'Removing Cluster. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove Datacenter',
		'label' => 'Remove Datacenter %s on vSphere Host %s',
		'msg_removed' => 'Removed Datacenter %s',
		'please_wait' => 'Removing Datacenter. Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-vsphere/lang", 'vmware-vsphere-dc.ini');
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
			$this->action = "edit";
		}
		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');

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
			case 'remove':
				$content[] = $this->edit(false);
				$content[] = $this->remove(true);
			break;
			case 'add_host':
				$content[] = $this->edit(false);
				$content[] = $this->add_host(true);
			break;
			case 'add_cluster':
				$content[] = $this->edit(false);
				$content[] = $this->add_cluster(true);
			break;
			case 'remove_cluster':
				$content[] = $this->edit(false);
				$content[] = $this->remove_cluster(true);
			break;
			case 'add_hosttocluster':
				$content[] = $this->edit(false);
				$content[] = $this->add_hosttocluster(true);
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
	 * Datacenter management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_dc()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.edit.class.php');
				$controller                  = new vmware_vsphere_dc_edit($this->htvcenter, $this->response);
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
	 * Add Datacenter
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->__reload_dc()) {
				require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.add.class.php');
				$controller                  = new vmware_vsphere_dc_add($this->htvcenter, $this->response);
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
	 * Remove Datacenter
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.remove.class.php');
			$controller                  = new vmware_vsphere_dc_remove($this->htvcenter, $this->response);
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
	 * Add Host to Datacenter
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_host( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.add_host.class.php');
			$controller                  = new vmware_vsphere_dc_add_host($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['add_host'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add_host']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_host' );
		$content['onclick'] = false;
		if($this->action === 'add_host'){
			$content['active']  = true;
		}
		return $content;
	}

	
	//--------------------------------------------
	/**
	 * Add Cluster to Datacenter
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_cluster( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.add_cluster.class.php');
			$controller                  = new vmware_vsphere_dc_add_cluster($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['add_cluster'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add_cluster']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_cluster' );
		$content['onclick'] = false;
		if($this->action === 'add_cluster'){
			$content['active']  = true;
		}
		return $content;
	}
	

	//--------------------------------------------
	/**
	 * Remove Cluster from Datacenter
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove_cluster( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.remove_cluster.class.php');
			$controller                  = new vmware_vsphere_dc_remove_cluster($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['remove_cluster'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['remove_cluster']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove_cluster' );
		$content['onclick'] = false;
		if($this->action === 'remove_cluster'){
			$content['active']  = true;
		}
		return $content;
	}


	

	//--------------------------------------------
	/**
	 * Add Host to Cluster
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_hosttocluster( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-dc.add_hosttocluster.class.php');
			$controller                  = new vmware_vsphere_dc_add_hosttocluster($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['add_hosttocluster'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addtocluster']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_hosttocluster' );
		$content['onclick'] = false;
		if($this->action === 'add_hosttocluster'){
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
	function __reload_dc() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datacenter post_dc_list -i ".$resource->ip;
		$command .= ' --htvcenter-ui-user '.$this->user->name;
		$command .= ' --htvcenter-cmd-mode background';
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.dc_list';
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
