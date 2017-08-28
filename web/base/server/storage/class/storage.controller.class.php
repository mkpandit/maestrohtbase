<?php
/**
 * Storage Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class storage_controller {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'storage_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'storage_identifier';
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
		'tab' => 'Storage',
		'label' => 'Storage',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Storage type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'diskadd' => array (
		'tab' => 'Add a new disk',
		'label' => 'Add a new disk',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'memoryadd' => array (
		'tab' => 'Add memory as disk storage',
		'label' => 'Add memory as disk storage',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'addawsfile' => array (
		'tab' => 'Add AWS Bucket (S3)',
		'label' => 'Add AWS Bucket (S3)',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
		'aws_file_name' => 'Select file to upload'
	),
	'add' => array (
		'label' => 'Add Storage',
		'msg' => 'Added Storage %s',
		'form_name' => 'Name',
		'error_name' => 'Storage name must be %s',
		'form_capabilities' => 'Capabilities',
		'lang_name_generate' => 'generate name',
		'error_capabilities' => 'Capabilities name must be %s',
		'form_deployment' => 'Deployment Type',
		'form_resource' => 'Resource',
		'form_comment' => 'Comment',
		'error_comment' => 'Comment must be %s',
		'error_exists' => 'Storage name must be unique!',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove Storage',
		'msg' => 'Removed storage %s',
		'msg_not_removing_active' => 'Not removing Storage %s!<br>Image %s are still located on it!',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Edit Storage',
		'label' => 'Edit Storage %s',
		'msg' => 'Edited storage %s',
		'comment' => 'Comment',
		'form_comment' => 'Comment',
		'error_comment' => 'Comment must be %s',
		'please_wait' => 'Loading. Please wait ..',
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
		$this->tpldir   = $this->rootdir.'/server/storage/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/storage/lang", 'storage.ini');
//		$response->html->debug();

		if(isset($_GET) && $_GET['scandevice'] == 'yes'){
			$hostList = shell_exec('python '.$this->rootdir.'/server/storage/script/scand.py');
			$hostListData = json_decode($hostList, true);
			$count = 1; $html_information = "";
			foreach($hostListData as $k => $v){
				$temp = explode(":", $v);
				if($_GET['key'] != '') {
					if (strpos($temp[0], $_GET['key']) !== false) {
						$html_information .= "<div id='hd-".$count."' class='disk disk-".$count."'>
						<p><i class='fa fa-server fa-sm'></i></span>
						<span id='ip-header'>" . str_replace($_GET['key'], "<b>" . $_GET['key'] . "</b>", $temp[0]) . "</span> / Status: " . $temp[1] . " </p>
						<div id='hdd-".$count."' class='disk-hd'><p>Disks available on this host:</p></div>
						</div>";
						$count++;
					}
				} else {
					$html_information .= "<div id='hd-".$count."' class='disk disk-".$count."'>
					<p><i class='fa fa-server fa-sm'></i></span>
					<span id='ip-header'>" . $temp[0] . "</span> / Status: " . $temp[1] . " </p>
					<div id='hdd-".$count."' class='disk-hd'><p>Disks available on this host:</p></div>
					</div>";
					$count++;
				}
			}
			if($count == 1) {
				$html_information = "<div class='disk'>No host found with the keyword <i>" . $_GET['key'] . "</i></disk>";
			}
			echo $html_information; exit();
		}
		
		if(isset($_GET) && $_GET['scandisk'] == 'yes') {
			$server     = new htvcenter_server();
			$IP_ADDRESS = $_GET['ipaddress'];
			$disk_info_dump = shell_exec('python '.$this->rootdir.'/server/storage/script/scandrive.py '.$IP_ADDRESS);
			$disk_info = json_decode($disk_info_dump, true);	
			$data = ""; $count = 1;
			foreach($disk_info as $k => $v){
				$temp = explode("/", $v);
				if($temp[1] != "") {
					$data .= "<p class='disk-bullet'><i class='fa fa-minus-circle fa-sm' aria-hidden='true'></i>" . $v . "</p>";
				} else {
					$t = explode(" ", $temp[0]);
					$data .= "<p class='disk-bullet' data='".$IP_ADDRESS."' id='disk-bullet-".$count."'><i class='fa fa-plus-circle fa-sm' aria-hidden='true'></i>" . $v . "</p>";
				}
				$count++;
			}
			if(empty($data)) {
				$data = "<p>No disk information available on this host.</p>";
			}
			echo "<div class='storage-info'>" . $data . "</div>"; exit();
		}
		
		if(isset($_GET) && $_GET['mountdisk'] == 'yes') {
			$server     = new htvcenter_server();
			$htvcenter_SERVER_IP_ADDRESS = $server->get_ip_address();
			$IP_ADDRESS = $_GET['ipaddress'];
			$disk_name = str_replace("!", "/", $_GET['disk']);
			//$disk_mount = $output = exec("./".$this->rootdir."/server/storage/script/chunk_disk_integration ".$IP_ADDRESS." htbase htbase ".$disk_name);
			$disk_info_dump = shell_exec('python '.$this->rootdir.'/server/storage/script/mountdisk.py '.$IP_ADDRESS.' htbase htbase '.$disk_name.' '.$htvcenter_SERVER_IP_ADDRESS);
			$disk_info = json_decode($disk_info_dump, true);
			foreach($disk_info as $k => $v){
				$data .= $v;
			}
			if(empty($data)) {
				$data = "<p>Could not mount the disk properly. Contact the administrator for help.</p>";
			} else {
				$data = "<p><i><b>" . $disk_name . "</b></i> has been mounted on MFS. </p>";
			}
			echo $data; exit();
		}
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
		$this->response->params['storage_filter'] = $this->response->html->request()->get('storage_filter');

		// handle table params
		$vars = $this->response->html->request()->get('storage');
		if($vars !== '') {
			if(!isset($vars['action'])) {
				foreach($vars as $k => $v) {
					$this->response->add('storage['.$k.']', $v);
				}
			} else {
				foreach($vars as $k => $v) {
					unset($this->response->params['storage['.$k.']']);
				}
			}
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'diskadd':
				$content[]  = $this->select(false);
				$content[]  = $this->diskadd(true);
			break;
			case 'memoryadd':
				$content[]  = $this->select(false);
				$content[]  = $this->memoryadd(true);
			break;
			case 'addawsfile':
				$content[]  = $this->select(false);
				$content[]  = $this->addawsfile(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->add(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case 'load':
				$tmp           = $this->select(false);
				$tmp['value']  = $this->__loader();
				$tmp['active'] = true;
				$content[]     = $tmp;
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
		require_once($this->rootdir.'/server/storage/class/storage.api.class.php');
		$controller = new storage_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.select.class.php');
			$controller = new storage_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
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
	 * Add a new disk
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function diskadd( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.diskadd.class.php');
			$controller = new storage_diskadd($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['diskadd'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['diskadd']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'diskadd' );
		$content['onclick'] = false;
		if($this->action === 'diskadd'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add memory as disk storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function memoryadd( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.memoryadd.class.php');
			$controller = new storage_memoryadd($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['memoryadd'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['memoryadd']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'memoryadd' );
		$content['onclick'] = false;
		if($this->action === 'memoryadd'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add an AWS Storage (S3)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function addawsfile( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.addawsfile.class.php');
			$controller = new addawsfile($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addawsfile'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addawsfile']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addawsfile' );
		$content['onclick'] = false;
		if($this->action === 'addawsfile' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.add.class.php');
			$controller                  = new storage_add($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['add'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}	

	//--------------------------------------------
	/**
	 * Edit storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.edit.class.php');
			$controller                  = new storage_edit($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit' || $this->action === $this->lang['select']['action_edit']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Remove storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/storage/class/storage.remove.class.php');
			$controller                  = new storage_remove($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['remove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Remove';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove' || $this->action === $this->lang['select']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Load Plugin as new tab
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function __loader() {

		$plugin = $this->response->html->request()->get('splugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('scontroller') !== '') {
			$class = $this->response->html->request()->get('scontroller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';

		// handle new response object
		$response = $this->response->response();
		$response->id = 'sload';
		#unset($response->params['storage[sort]']);
		#unset($response->params['storage[order]']);
		#unset($response->params['storage[limit]']);
		#unset($response->params['storage[offset]']);
		#unset($response->params['storage_filter']);
		$response->add('splugin', $plugin);
		$response->add('scontroller', $name);
		$response->add($this->actions_name, 'load');

		$path   = $this->htvcenter->get('webdir').'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
		$role = $this->htvcenter->role($response);
		$data = $role->get_plugin($class, $path);
		$data->pluginroot = '/plugins/'.$plugin;
		return $data;
	}

}
?>
