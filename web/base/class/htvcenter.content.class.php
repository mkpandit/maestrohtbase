<?php
/**
 * htvcenter Content
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_content
{

var $pluginkey;
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* absolute path to webroot
* @access public
* @var string
*/
var $rootdir;
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
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file, $user, $htvcenter) {
		$this->response = $response;
		$this->htvcenter  = $htvcenter;
		$this->file     = $file;
		$this->user     = $user;
		$this->request  = $this->response->html->request();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$t = $this->response->html->template($this->tpldir.'/index_content.tpl.php');
		$t->add($this->content(), 'content');
		$t->add($this->pluginkey, 'contentclass'); // needs to run after $this->content()
		return $t;
	}

	//--------------------------------------------
	/**
	 * Build content
	 *
	 * @access public
	 * @return htmlobject_tabmenu | string
	 */
	//--------------------------------------------
	function content() {
		$lc = $this->lcc();
		if (isset($lc)) {
			return $lc;
		}
		if($this->request->get('iframe') !== '') {
			$this->pluginkey = 'iframe';
			$iframe = parse_url($this->request->get('iframe'), PHP_URL_PATH);
			$str = '<iframe name="MainFrame" id="MainFrame" src="'.$iframe.'" scrolling="auto" height="1" frameborder="0"></iframe>';
			$name = $this->request->get('name');
			if($name === '') {
				$name = 'Iframe';
			}
			// assign params to response
			$this->response->add('iframe',$this->request->get('iframe'));
			$this->response->add('name',$this->request->get('name'));

			// tabs
			$content[] = array(
				'label' => $name,
				'value' => $str,
				'target' => $this->response->html->thisfile,
				'request' => $this->response->get_array(),
				'onclick' => false,
				'hidden' => false,
			);
			$tab = $this->response->html->tabmenu('iframe_tab');
			$tab->message_param = 'noop';
			$tab->auto_tab = false;
			$tab->css = 'htmlobject_tabs';
			$tab->add($content);
			return $tab;
		} 
		else if ($this->request->get('base') !== '') {
			$plugin = $this->request->get('base');
			$this->pluginkey = $plugin;
			$name   = $plugin;
			$class  = $plugin;
			if($this->request->get('controller') !== '') {
				$class = $this->request->get('controller');
				$name  = $class;
			}
			$class  = str_replace('-', '_', $class).'_controller';
			$path   = $this->rootdir.'/server/'.$plugin.'/class/'.$name.'.controller.class.php';
			$role = $this->htvcenter->role($this->response);
			$data = $role->get_plugin($class, $path);
			return $data;
		}
		else if($this->request->get('plugin') !== '') {
			$plugin = $this->request->get('plugin');
			$this->pluginkey = $plugin;
			$name   = $plugin;
			$class  = $plugin;
			if($this->request->get('controller') !== '') {
				$class = $this->request->get('controller');
				$name  = $class;
			}
			$class  = str_replace('-', '_', $class).'_controller';
			$path   = $this->rootdir.'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
			if($this->file->exists($path)) {
				$role = $this->htvcenter->role($this->response);
				$data = $role->get_plugin($class, $path);
				return $data;
			} else {
				// handle plugins not oop
				$path = $this->rootdir.'/plugins/'.$plugin.'/'.$name.'-manager.php';
				if($this->file->exists($path)) {
					$params = '';
					foreach($_REQUEST as $k => $v) {
						if(is_string($v)) {
							$params .= '&'.$k.'='.$v;		
						}
						if(is_array($v)) {
							foreach($v as $key => $value) {
								$params .= '&'.$k.'['.$key.']'.'='.$value;
							}
						}
					}
					$str = '<iframe name="MainFrame" id="MainFrame" src="plugins/'.$plugin.'/'.$name.'-manager.php?'.$params.'" scrolling="auto" height="1" frameborder="0"></iframe>';
					return $str;
				} else {
					$role = $this->htvcenter->role($this->response);
					$data = $role->get_plugin($class, $path);
					return $data;
				}
			}
		} else {
			// default page - datacenter overview
			$this->pluginkey = 'aa_server';
			$path   = $this->rootdir.'/server/'.$this->pluginkey.'/class/datacenter.controller.class.php';
			$this->htvcenter->init();
			require_once($path);
			$controller = new datacenter_controller($this->htvcenter, $this->response);
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * lcc
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function lcc() {
		$now = $_SERVER['REQUEST_TIME'];
		$number  = $this->htvcenter->l[0];
		$version = $this->htvcenter->l[1];
		$clients = $this->htvcenter->l[2] + $this->htvcenter->tc;
		$until   = $this->htvcenter->l[3];
		$this->htvcenter->init();
		if ($until != 0) {
			if ($now > $until) {
				$event = new event();
				$event_description = $this->lang['ltimeout'];
				$event->log("htvcenter", $now, 9, "License", $event_description, "", "", 0, 0, 0);
				if(($now + (14 * 24 * 60 * 60)) > $until) {
					$this->response->redirect(
						$this->response->get_url('upload', 'true', 'upload_msg', $this->lang['ltimeout'])
					);
				}
			}
		}
		$res = new resource();
		$lres = $res->get_count('phys');
		if ($lres > $clients) {
			$event = new event();
			$event_description = $this->lang['lclients'];
			$event->log("htvcenter", $now, 9, "License", $event_description, "", "", 0, 0, 0);
		}
		return;
	}

}
?>
