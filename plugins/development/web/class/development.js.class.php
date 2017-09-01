<?php
/**
 * Development Hooks
 *
	htvcenter Enterprise developed by htvcenter Enterprise GmbH.

	All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

	This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
	The latest version of this license can be found here: http://htvcenter-enterprise.com/license

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://htvcenter-enterprise.com

	Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


class development_js
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'development_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "development_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'development_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'development_identifier';
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
var $lang;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response, $controller) {

		$this->htvcenter  = $htvcenter;
		$this->controller = $controller;
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('basedir');
		$this->tpldir   = $this->rootdir.'/plugins/development/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();

		$plugin = $this->response->html->request()->get('plugin_name');
		$this->response->add('plugin_name', $plugin);
		$base = $this->response->html->request()->get('base_name');
		$this->response->add('base_name', $base);
		$this->methods = array('api', 'action');
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
		if($this->response->html->request()->get('plugin_name') !== '') {
			$name = $this->response->html->request()->get('plugin_name');
			$data['label'] = sprintf($this->lang['label_plugin'], $name);
		}
		else if ($this->response->html->request()->get('base_name') !== '') {
			$name = $this->response->html->request()->get('base_name');
			$data['label'] = sprintf($this->lang['label_base'], $name);
		}
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $this->response->html->thisfile,
		));
		$t = $this->response->html->template($this->tpldir.'/development-js.tpl.php');
		$t->add($vars);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');

		return $this->edit($t);

	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function edit($template) {
		if($this->response->html->request()->get('plugin_name') !== '') {
			$path = $this->rootdir.'/plugins/'.$this->response->html->request()->get('plugin_name').'/web/js/';
		}
		if($this->response->html->request()->get('base_name') !== '') {
			if($this->response->html->request()->get('base_name') !== 'plugins') {
				$path = $this->rootdir.'/web/base/server/'.$this->response->html->request()->get('base_name').'/js/';
			}
			elseif ($this->response->html->request()->get('base_name') === 'plugins') {
				$path = $this->rootdir.'/web/base/plugins/aa_plugins/js/';
			}
		}

		$files = $this->file->get_files($path, '', '*.js');
		$names = array();
		if(is_array($files)) {
			foreach($files as $file) {
				if(strripos($file['name'], '.js') !== false) {
					$names[] = $file['path'];
				}
			}
			$links = '';
			foreach($names as $path) {
				$links .= '<h3>'.basename($path).'</h3>';
				$links .= '<pre>'.$this->file->get_contents($path).'</pre>';
			}
			if($links === '') {
				$links = '<div>Plugin has no js</div>';
			}
			$template->add($links, 'links');
		} 
		$template = $this->controller->__get_navi($template, 'js');
		return $template;
	}


}
?>
