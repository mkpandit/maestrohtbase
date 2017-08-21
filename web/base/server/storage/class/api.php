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

class storage_api {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'storage_api';
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
			case 'ajaxcall':
				$content[]  = $this->select(false);
				$content[]  = $this->ajaxcall(true);
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
	
	function diskadd( $hidden = true ){
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
	 * Do an Ajax call
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function ajaxcall( $hidden = true ){
		$data = '';
		
		/*if( $hidden === true ) {
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['ajaxcall'];
			$data = $controller->action();
		}*/
		
		/*$hostList = shell_exec('sudo python '.$this->rootdir.'/server/storage/script/scand.py');
		$hostListData = json_decode($hostList, true);
		$count = 1; $html_information = "";
		foreach($hostListData as $k => $v){
			$temp = explode(":", $v);
			//echo $temp[0] . " -> " . $temp[1];
			$html_information .= "<div class='disk disk-".$count."'>
				<span class='icon-wrap icon-wrap-lg icon-circle bg-danger'><i class='fa fa-server fa-lg'></i></span>
				<h4>".$temp[0]."</h4>
				<p>Status: ".$temp[1]." </p>
				<i class='fa fa-plus-square-o fa-lg'></i> <i class='fa fa-minus-square-o fa-lg'></i>
				</div>";
			$count++;
		}*/
		
		$data = "Test"; //$html_information;
		echo $data;
		/*$content['label']   = $this->lang['ajaxcall']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ajaxcall' );
		$content['onclick'] = false;
		if($this->action === 'ajaxcall'){
			$content['active']  = true;
		}*/
		//return $content;
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
