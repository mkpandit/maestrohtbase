<?php
/**
 * Sanboot-Storage Edit Storage
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class sanboot_storage_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'sanboot_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "sanboot_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'sanboot_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'sanboot_identifier';
/**
* identifier name
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
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->response->add('storage_id', $this->response->html->request()->get('storage_id'));
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$storage_id = $this->response->html->request()->get('storage_id');
		if($storage_id === '') {
			return false;
		}
		// set ENV
		$deployment = new deployment();
		$storage    = new storage();
		$resource   = new resource();

		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$this->resource   = $resource;
		$this->storage    = $storage;
		$this->deployment = $deployment;

		#if(!file_exists('storage/'.$resource->id.'.sanboot.stat.manual')) {
		$this->statfile = $this->htvcenter->get('basedir').'/plugins/sanboot-storage/web/storage/'.$resource->id.'.vg.stat';
		#} else {
		#	$this->response->redirect(
		#		$this->response->get_url($this->actions_name, 'manual', $this->message_param, $this->lang['manual_configured'])
		#	);
		#}
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
		$this->init();
		$data = $this->edit();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/sanboot-storage-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_sanboot'], $this->response->html->request()->get('storage_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {
		if((strpos($this->deployment->type, 'iscsi-san-deployment') !== false) || (strpos($this->deployment->type, 'aoe-san-deployment') !== false)) {

			// check device-manager
			$devicemgm = false;
			if($this->file->exists($this->htvcenter->get('webdir').'/plugins/device-manager/class/device-manager.addvg.class.php')) {
				$devicemgm = true;
			}

			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/sanboot-storage/img/plugin.png";
			$state_icon = $this->htvcenter->get('baseurl')."/img/".$this->resource->state.".png";
			if ($this->file->exists($this->htvcenter->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->htvcenter->get('baseurl').$resource_icon_default;

			$d['state'] = '<img width="24" height="24" src="'.$state_icon.'">';
			$d['icon'] = '<img width="24" height="24" src="'.$resource_icon_default.'">';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['deployment'] = $this->deployment->type;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					$i = 0;
					foreach($lines as $line) {
						if($line !== '') {
							$line  = explode('@', $line);
							$name  = substr($line[0], strripos($line[0], '/'));
							$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '').' MB';
							$vfree = str_replace('m', '', $line[6]);
							if($vfree !== '0') {
								$vfree = number_format(substr($line[6], 0, strpos($line[6], '.')), 0, '', '');
							}
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_edit'];
							$a->label   = $this->lang['action_edit'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "volgroup").'&volgroup='.$name;
							$body[$i] = array(
								'icon' => $d['icon'],
								'name'   => $name,
								'pv' => $line[1],
								'lv' => $line[2],
								'sn' => $line[3],
								'attr' => $line[4],
								'vsize' => $vsize,
								'vfree' => $vfree.' MB',
								'edit' => $a->get_string(),
							);
							if($devicemgm === true) {
								if($line[2] === '0' && $line[3] === '0') {
									$a = $this->response->html->a();
									$a->title   = $this->lang['action_remove'];
									$a->label   = $this->lang['action_remove'];
									$a->handler = 'onclick="wait();"';
									$a->css     = 'remove';
									$a->href    = $this->response->get_url($this->actions_name, "removevg").'&volgroup='.$name;
									$body[$i]['remove'] = $a->get_string();
								} else {
									$body[$i]['remove'] = '&#160;';
								}
							}
							$i++;
						}
					}
				}
			}

			$h['icon']['title'] = '&#160;';
			$h['icon']['sortable'] = false;
			$h['name']['title'] = $this->lang['table_name'];
			$h['pv']['title'] = $this->lang['table_pv'];
			$h['lv']['title'] = $this->lang['table_lv'];
			$h['sn']['title'] = $this->lang['table_sn'];
			$h['attr']['title'] = $this->lang['table_attr'];
			$h['vsize']['title'] = $this->lang['table_vsize'];
			$h['vfree']['title'] = $this->lang['table_vfree'];
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;
			if($devicemgm === true) {
				$h['remove']['title'] = '&#160;';
				$h['remove']['sortable'] = false;
			}

			$table = $this->response->html->tablebuilder('sanboot_edit', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'name';
			$table->limit           = 10;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action     = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			#$table->identifier      = 'name';
			#$table->identifier_name = $this->identifier_name;
			#$table->actions_name    = $this->actions_name;
			#$table->actions         = array($this->lang['action_remove'], $this->lang['action_snap']);

			$d['add'] = '';
			if($devicemgm === true) {
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_add'];
				$a->label   = $this->lang['action_add'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'add';
				$a->href    = $this->response->get_url($this->actions_name, "addvg");
				$d['add'] = $a->get_string();
			}

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}
?>
