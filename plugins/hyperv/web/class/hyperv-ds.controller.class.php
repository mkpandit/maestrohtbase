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

class hyperv_ds_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_ds_id';
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
		'table_id' => 'Id',
		'table_comment' => 'Comment',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Pool Manager',
		'label' => 'Pools on Hyper-V Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_ds_add_pool' => 'Add new Pool',
		'action_ds_remove' => 'remove',
		'action_edit' => 'list',
		'table_state' => 'Status',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_location' => 'Location',
		'table_filesystem' => 'Filesystem',
		'table_capacity' => 'Capacity',
		'table_available' => 'Available',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'add_pool' => array (
		'tab' => 'Add Pool',
		'label' => 'Add Pool to Hyper-V Host %s',
		'lang_browse' => 'browse',
		'lang_browser' => 'Directorypicker',
		'form_name' => 'Name',
		'form_comment' => 'Comment',
		'form_path' => 'Path',
		'msg_added' => 'Added Pool %s',
		'error_exists' => 'Pool %s allready exists',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'error_name' => 'Name must be %s',
		'error_comment' => 'Comment must be %s only',
		'please_wait' => 'Adding Pool. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove_pool' => array (
		'tab' => 'Remove Pool',
		'label' => 'Remove Pool from Hyper-V Host %s',
		'msg_removed' => 'Removed Pool %s',
		'error_exists' => 'Pool %s allready exists',
		'error_not_exists' => 'Pool %s does not exists',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'error_not_empty' => 'Pool %s not empty! Not removing Pool!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Removing Pool. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'volgroup' => array (
		'tab' => 'List Volumes',
		'label' => 'List Volumes from Pool %s on Hyper-V Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'add',
		'action_clone' => 'clone',
		'action_remove' => 'remove',
		'table_name' => 'Name',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove Volume(s)',
		'label' => 'Remove Volume(s) from Pool %s on Hyper-V Host %s',
		'msg_removed' => 'Removed Volume %s',
		'please_wait' => 'Removing Volume(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Volume',
		'label' => 'Add Volume on Hyper-V Host %s',
		'lang_basic' => 'Basic',
		'lang_browse' => 'browse',
		'lang_browser' => 'Filepicker',
		'form_name' => 'Name',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'form_comment' => 'Description',
		'msg_added' => 'Added Volume %s',
		'error_exists' => 'Volume %s allready exists',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'error_name' => 'Name must be %s',
		'error_path' => 'Path must not be empty',
		'please_wait' => 'Adding Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'create' => array (
		'tab' => 'Create Volume',
		'label' => 'Create Volume on Hyper-V Host %s',
		'lang_basic' => 'Basic',
		'form_name' => 'Name',
		'form_path' => 'Path',
		'form_size' => 'Size (MB)',
		'form_datastore' => 'Storage Pool',
		'form_comment' => 'Description',
		'msg_added' => 'Added Volume %s',
		'error_exists' => 'Volume %s allready exists',
		'error_no_hyperv' => 'Appliance is not an Hyper-V Server!',
		'error_name' => 'Name must be %s',
		'error_path' => 'Path must not be empty',
		'please_wait' => 'Adding Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'tab' => 'Clone Volume',
		'label' => 'Clone Volume %s from Pool %s on Hyper-V Host %s',
		'form_name' => 'Name',
		'msg_cloned' => 'Cloned Volume %s to %s',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning Volume. Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/hyperv/lang", 'hyperv-ds.ini');
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
			case 'add_pool':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add_pool(true);
			break;
			case 'remove_pool':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove_pool(true);
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
			case 'add':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->add(true);
			break;
			case 'create':
				$content[] = $this->__select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->create(true);
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
	 * Pool management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.edit.class.php');
			$controller                  = new hyperv_ds_edit($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['edit'];
			$data = $controller->action();
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
	 * Add Pool
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add_pool( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.add-pool.class.php');
			$controller                  = new hyperv_ds_add_pool($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['add_pool'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add_pool']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add_pool' );
		$content['onclick'] = false;
		if($this->action === 'add_pool'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Remove Pool
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove_pool( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.remove-pool.class.php');
			$controller                  = new hyperv_ds_remove_pool($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['remove_pool'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['remove_pool']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove_pool' );
		$content['onclick'] = false;
		if($this->action === 'remove_pool'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * List Volumes
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function volgroup( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.volgroup.class.php');
			$controller                  = new hyperv_ds_volgroup($this->htvcenter, $this->response);
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
	 * Remove Volumes
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.remove.class.php');
			$controller                  = new hyperv_ds_remove($this->htvcenter, $this->response);
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
	 * Add volume
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.add.class.php');
			$controller                  = new hyperv_ds_add($this->htvcenter, $this->response, $this);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['add'];
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
	 * Create volume
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function create( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.create.class.php');
			$controller                  = new hyperv_ds_create($this->htvcenter, $this->response, $this);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['create'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['create']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'create' );
		$content['onclick'] = false;
		if($this->action === 'create'){
			$content['active']  = true;
		}
		return $content;
	}
	
	
	//--------------------------------------------
	/**
	 * Clone Volume
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function duplicate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hyperv/class/hyperv-ds.clone.class.php');
			$controller                  = new hyperv_ds_clone($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['clone'];
			$data = $controller->action();
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



}
?>
