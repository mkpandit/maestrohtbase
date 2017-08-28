<?php
/**
 * Plugins Select
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class aa_plugins_select
{
/**
* plugin key
* @access public
* @var string
*/
var $plugin_key = 'aa_plugins';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aa_plugins_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aa_plugins_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aa_plugins_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aa_plugins_tab';
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
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param htvcenter $htvcenter
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter  = $htvcenter;
		$this->file     = $this->htvcenter->file();
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
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/aa_plugins-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider
	 */
	//--------------------------------------------
	function select() {

		$icon_started = $this->lang['action_stop'];
		$icon_stopped = $this->lang['action_start'];
		$icon_enabled = $this->lang['action_disable'];
		$icon_disabled = $this->lang['action_enable'];

		$plugin = new plugin();
		$plugins_available = $plugin->available();
		$plugins_enabled = $plugin->enabled();
		$plugins_started = $plugin->started();

		$h = array();
 		$h['name']['title'] = $this->lang['table_name'];
 
 		$h['type']['title'] = '&#160;';
 		$h['type']['sortable'] = '&#160;';
 
 		$h['type_n']['title'] = $this->lang['table_type'];
 		$h['type_n']['hidden'] = true;
 
 		$h['description']['title'] = $this->lang['table_description'];
 		$h['description']['sortable'] = false;

		$h['configure']['title'] = "&#160;";
 		$h['configure']['sortable'] = false;
		
		$h['enabled']['title'] = "&#160;";
 		$h['enabled']['sortable'] = false;
 
 		$h['enabled_n']['title'] = $this->lang['table_enabled'];
 		$h['enabled_n']['hidden'] = true;
 
 		$h['started']['title'] = "&#160;";
 		$h['started']['sortable'] = false;
 
 		$h['started_n']['title'] = $this->lang['table_started'];
 		$h['started_n']['hidden'] = true;

		$table = $this->response->html->tablebuilder('plugins', $this->response->get_array($this->actions_name, 'select'));
		$table->max = count($plugins_available);
		$table->sort = 'name';
		$table->limit = 0;
		$table->order = 'ASC';
		$table->init();
		$tps = $table->get_params();
		$tp = '';
		foreach($tps['plugins'] as $k => $v) {
			$tp .= '&plugins['.$k.']='.$v;
		}

		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base/';
		$thisfile = $this->response->html->thisfile;
		$b = array();
		$plugtype = array();
		$i = 0;
		$htvcenter_version = $this->htvcenter->get('config', 'SERVER_VERSION');


		foreach ($plugins_available as $index => $plugin_name) {

			$tmp = $plugin->get_config($plugin_name);
		
			$plugin_description = $tmp['description'];
                        $plugin_description=  str_replace("htvcenter","htvcenter", $plugin_description);
			$plugin_type =  $tmp['type'];

			$plugtype[] = $plugin_type;
			$plugin_version = $tmp['version'];
			$plugin_major = substr($plugin_version, 0, strpos($plugin_version, '.'));
			$plugin_minor = substr($plugin_version, strpos($plugin_version, '.')+1);
			$plugin_minor = substr($plugin_minor, 0, strpos($plugin_minor, '.'));
			$plugin_base_version = $plugin_major.".".$plugin_minor;
			$filtro = $this->response->html->request()->get('plugin_filter');
			$strleno = strlen($this->response->html->request()->get('plugin_filter'));
		
			if ($filtro == '' || $filtro == 'NULL' || empty($filtro)) {
				$filtro = 'cloud';
				$strleno = 5;
			}
		
			
			if (!$strleno || strstr($filtro, $plugin_type)) {
				
				
				// 
				$b[$i] = array();

				// plugin version mismatch
$htvcenter_version = 5.2;				
if ($htvcenter_version != $plugin_base_version) {


					$b[$i]['name'] = $plugin_name;
					$b[$i]['type'] = '<span class="pill ' . strtolower($plugin_type) . '">'.$plugin_type.'</span>';
					$b[$i]['type_n'] = $plugin_type;
					$b[$i]['description'] = $plugin_description;
					$b[$i]['enabled'] = $this->lang['version_mismatch'];
					$b[$i]['enabled_n'] = 'b';
					$b[$i]['started'] = '&#160;';
					$b[$i]['started_n'] = 'c';
					$b[$i]['configure'] = '';
					
				} else {

					// plugin not enabled!
					if (!in_array($plugin_name, $plugins_enabled)) {

						$a = $this->response->html->a();
						$a->label    = $icon_disabled;
						$a->href     = $this->response->get_url($this->actions_name, "enable");
						$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
						$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
						$a->href    .= $tp;
						$a->href    .= '#'.$plugin_name;
						// anchor
						$a->name     = $plugin_name;
						$a->handler  = 'onclick="wait();"';
						$a->css      = 'enable';
						$a->title    = sprintf($this->lang['title_enable'], $plugin_name);
						$plugin_description = strip_tags($plugin_description);

						switch($plugin_name) {
							case 'novnc':
								$plugin_name_title = 'svaccess';
								break;
							case 'kvm':
								$plugin_name_title = 'OCH';
								$plugin_description = 'Integrates the OCH Virtualization Technology.';
								break;
							case 'cloud':
								$plugin_name_title = 'fortis';
								break;
							case 'tmpfs-storage':
								$plugin_name_title = 'HTFS In-Memory';
								break;
							default:
								$plugin_name_title = $plugin_name;
							break;
						}
						$b[$i]['name'] = '<div class="panel-heading"><div class="panel-control">
										<i class="fa fa-info-circle infofa add-tooltip" data-placement="bottom" data-toggle="tooltip" data-original-title="'.$plugin_description.'"></i><span class="label label-purple">'.$plugin_type.'</span>
									</div>
									<h3 class="panel-title">'.$plugin_name_title.'</h3> </div>';
						$b[$i]['type'] = ''; //'<span class="pill ' . strtolower($plugin_type) . '">'.$plugin_type.'</span></div>';
						$b[$i]['type_n'] = $plugin_type;
						$b[$i]['description'] = '';
						$b[$i]['enabled'] = $a->get_string();
						$b[$i]['enabled_n'] = 'b';
						$b[$i]['started'] = '&#160;';
						$b[$i]['started_n'] = 'c';
					} else {
						$plugin_icon_path="$RootDir/plugins/$plugin_name/img/plugin.png";
						$plugin_icon="/htvcenter/base/plugins/$plugin_name/img/plugin.png";
						$plugin_icon_default="/htvcenter/base/plugins/aa_plugins/img/plugin.png";
						if ($this->file->exists($plugin_icon_path)) {
							$plugin_icon_default=$plugin_icon;
						}

						$a = $this->response->html->a();
						$a->label    = $icon_enabled;
						$a->href     = $this->response->get_url($this->actions_name, "disable");
						$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
						$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
						$a->href    .= $tp;
						$a->href    .= '#'.$plugin_name;
						// anchor
						$a->name     = $plugin_name;
						$a->handler  = 'onclick="wait();"';
						$a->css      = (!in_array($plugin_name, $plugins_started)) 
											? 'disable'
											: 'disable disabled';			
						$a->title    = sprintf($this->lang['title_disable'], $plugin_name_title);

						$plugin_description = strip_tags($plugin_description);
						
						switch($plugin_name) {
							case 'novnc':
								$plugin_name_title = 'svaccess';
								break;
							case 'cloud':
								$plugin_name_title = 'fortis';
								break;
							case 'kvm':
								$plugin_name_title = 'OCH';
								$plugin_description = 'Integrates the OCH Virtualization Technology.';
								break;
							case 'tmpfs-storage':
								$plugin_name_title = 'HTFS In-Memory';
								break;
							default:
								$plugin_name_title = $plugin_name;
							break;
						}
						$b[$i]['name'] = '<div class="panel-heading"><div class="panel-control">
										<i class="fa fa-info-circle infofa add-tooltip" data-placement="bottom" data-toggle="tooltip" data-original-title="'.$plugin_description.'"></i><span class="label label-purple">'.$plugin_type.'</span>
									</div><h3 class="panel-title">'.$plugin_name_title.'</h3> </div>';
						$b[$i]['type'] = '';//<span class="pill ' . strtolower($plugin_type) . '">'.$plugin_type.'</span>';
						$b[$i]['type_n'] = $plugin_type;
						$b[$i]['description'] = '';
						$b[$i]['enabled'] = $a->get_string();
						$b[$i]['enabled_n'] = 'a';

						// started ?
						if (!in_array($plugin_name, $plugins_started)) {
							$a = $this->response->html->a();
							$a->label    = $icon_stopped;
							$a->href     = $this->response->get_url($this->actions_name, "start");
							$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
							$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
							$a->href    .= $tp;
							$a->href    .= '#'.$plugin_name;
							$a->handler  = 'onclick="wait();"';
							$a->css      = 'start';
							$a->title    = sprintf($this->lang['title_start'], $plugin_name_title);

							$b[$i]['started'] = $a->get_string();
							$b[$i]['started_n'] = 'b';
						} else {
							$a = $this->response->html->a();
							$a->label    = $icon_started;
							$a->href     = $this->response->get_url($this->actions_name, "stop");
							$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
							$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
							$a->href    .= $tp;
							$a->href    .= '#'.$plugin_name;
							$a->handler  = 'onclick="wait();"';
							$a->css      = 'stop';
							$a->title    = sprintf($this->lang['title_stop'], $plugin_name_title);

							$b[$i]['started'] = $a->get_string();
							$b[$i]['started_n'] = 'a';
						}
					}
					// configure button
					$b[$i]['configure'] = '';
					if(isset($tmp['configurable'])) {
						$a = $this->response->html->a();
						$a->label    = $this->lang['action_configure'];
						$a->href     = $this->response->get_url($this->actions_name, "configure");
						$a->href    .= '&'.$this->identifier_name.'='.$plugin_name;
						$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
						$a->href    .= $tp;
						$a->handler  = 'onclick="wait();"';
						$a->css      = 'manage configpop';
						$a->title    = sprintf($this->lang['title_configure'], $plugin_name_title);
						$b[$i]['configure'] = $a->get_string();
					}

				}
				$i++;
			}
		}

		$plugs = array();
		$plugs[] = array();
		$plugtype = array_unique($plugtype);
		natcasesort($plugtype);
		foreach($plugtype as $p) {
			$plugs[] = array($p,ucfirst($p));
		}
		
		$select = $this->response->html->select();
		$select->add($plugs, array(0,1));
		$select->name = 'plugin_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('plugin_filter'));
		
		$box = $this->response->html->box();
		$box->add($select);
		$box->id = 'plugins_filter';
		$box->css = 'htmlobject_box';
		$box->label = $this->lang['lang_filter'];

		$table->id = 'Tabellerplug';
		$table->tr_handler = array();
		$table->css = 'htmlobject_table table table-striped';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = true;
		$table->sort_link = false;
		$table->add_headrow($box->get_string());
		$table->head = $h;
		$table->body = $b;
		$table->max = count($b);
		$table->form_action = $this->response->html->thisfile;
	/*
		$table->actions_name = $this->actions_name;
		$table->actions = array(
							array('enable' => $this->lang['action_enable']),
							array('disable' => $this->lang['action_disable']),
							array('start' => $this->lang['action_start']),
							array('stop' => $this->lang['action_stop'])
						);
	
		$table->identifier = 'name';
		$table->identifier_name = $this->identifier_name;
	*/
		return $table;
	}

}
?>
