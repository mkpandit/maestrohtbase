<?php
/**
 * htvcenter Menu
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_menu
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;

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
	function __construct($response, $file, $user) {
		global $htvcenter_SERVER_BASE_DIR;
		$this->response = $response;
		$this->file     = $file;
		$this->user     = $user;
		
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$this->RootDir = $htvcenter_SERVER_BASE_DIR.'/htvcenter';
		$this->WebDir = '/htvcenter/base/';
		$this->ImgDir = $RootDir.'/img/';
		$this->PluginsDir = $RootDir.'plugins/';
		$this->ClassDir = $RootDir.'class/';
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
		require_once($_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/class/layersmenu.class.php');
		require_once($_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/class/PHPLIB.php');
		
		$mid = new TreeMenu();
		$mid->dirroot = $this->RootDir;
		$mid->imgdir = $this->ImgDir.'menu/';
		$mid->imgwww = $this->WebDir.'img/menu/';
		$mid->icondir = $this->ImgDir.'menu/';
		$mid->iconwww = $this->WebDir.'img/menu/';
		
		$strMenuStructure = '';
		
		// define the base menu item
		$strMenuStructure .= implode('', file($this->RootDir.'/web/base/server/aa_server/menu.txt'));
		
		if($strMenuStructure != '') {
			$mid->setMenuStructureString($strMenuStructure);
		}
		$mid->setIconsize(18, 18);
		$mid->parseStructureForMenu('menu1_');
//		$mid->replaceStringInUrls('menu1_', '&', '&amp;');


//		var_dump($_SERVER['QUERY_STRING']);
		$mid->setSelectedItemByUrl('menu1_', $_SERVER['QUERY_STRING']);
		$mid->newTreeMenu('menu1_');

		$mid2 = new TreeMenu();
		$mid2->dirroot = $this->RootDir;
		$mid2->imgdir = $this->ImgDir.'menu/';
		$mid2->imgwww = $this->WebDir.'img/menu/';
		$mid2->icondir = $this->ImgDir.'menu/';
		$mid2->iconwww = $this->WebDir.'img/menu/';
		
		$strMenuStructure = '';		
		
		// define the plugin manager menu item
		$strMenuStructure .= implode('', file($this->PluginsDir.'/aa_plugins/menu.txt'));
		
		// define the base plugin sections
		$strMenuStructure .= $this->parse_subsection("Cloud", "cloud");
		$strMenuStructure .= $this->parse_subsection("Deployment", "deployment");
		$strMenuStructure .= $this->parse_subsection("Highavailability", "HA");
		$strMenuStructure .= $this->parse_subsection("Management", "management");
		$strMenuStructure .= $this->parse_subsection("Monitoring", "monitoring");
		$strMenuStructure .= $this->parse_subsection("Network", "network");
		$strMenuStructure .= $this->parse_subsection("Storage", "storage");
		$strMenuStructure .= $this->parse_subsection("Virtualization", "virtualization");
		$strMenuStructure .= $this->parse_subsection("Misc", "misc");
		
		if($strMenuStructure != '') {
			$mid2->setMenuStructureString($strMenuStructure);
		}	
		$mid2->setIconsize(18, 18);
		$mid2->parseStructureForMenu('menu2_');
		$mid2->setSelectedItemByUrl('menu2_', $_SERVER['QUERY_STRING']);
		$mid2->newTreeMenu('menu2_');

		$t = $this->response->html->template($this->tpldir.'/index_menu.tpl.php');
		$t->add("time()", 'timestamp');
		$t->add($mid->getTreeMenu('menu1_'), 'menu_1');
		$t->add($mid2->getTreeMenu('menu2_'), 'menu_2');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Parse menu.txt files for phplayers
	 *
	 * @access private
	 * @return string
	 */
	//--------------------------------------------
	function parse_subsection($menuname, $name) {
		global $htvcenter_SERVER_BASE_DIR;
		$str = '';
		$folders = $this->file->get_folders($this->RootDir.'/plugins');
		$menu = ".|$menuname\n";
		foreach ($folders as $plug) {
			$filename = $this->PluginsDir.'/'.$plug['name'].'/menu.txt';
			$plugin_config = $plug['path'].'/etc/htvcenter-plugin-'.$plug['name'].'.conf';
			if($this->file->exists($plugin_config)) {
				$store = "";
				$store = htvcenter_parse_conf($plugin_config);
				extract($store);
				if (!strcmp($store['htvcenter_PLUGIN_TYPE'], $name)) {
					if($this->file->exists($filename)) {
						$str .= implode('', file($filename));
					}
				}
			}
		}
		// workaround for img path
		if($str !== '') {
			$str = str_replace('|../../', '|', $str);
			$str = $menu.$str;
		}
		return $str;
	}

}
