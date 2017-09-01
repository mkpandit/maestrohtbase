<?php
/**
 * ESX Hosts DataStore Manager
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_esx_ds_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_ds_id';
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
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		if($appliance_id === '') {
			return false;
		}
		// set ENV
		$virtualization = new virtualization();
		$appliance    = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource   = $resource;
		$this->appliance    = $appliance;
		$this->virtualization = $virtualization;
		$this->statfile = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
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
		$data = $this->ds();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/vmware-esx-ds-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_esx'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * DataStore Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds() {

		if($this->virtualization->type === 'vmware-esx') {

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_nas'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_nas");
			$d['ds_add_nas'] = $a->get_string();

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_iscsi'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_iscsi");
			$d['ds_add_iscsi'] = $a->get_string();
			
			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							// prepare remove link
							$actions = '';
							$remove_action = '';
							$remove_action_link = '';
							$a = $this->response->html->a();
							if (!strcmp($line[2], "NFS"))  {
								$a->label = 'Remove';
								$a->title = $this->lang['action_ds_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove_nas')."&volgroup=".$line[0];
								$actions .= $a->get_string();
							}
							if (!strcmp($line[2], "VMFS"))  {
								$a->label = 'Remove';
								$a->title = $this->lang['action_ds_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove_iscsi')."&volgroup=".$line[0];
								$actions .= $a->get_string();
							}

							$l = $this->response->html->a();
							if ($this->lang['action_edit'] == 'list VMDKs') {
								$label = 'List VMDKs';
							}
							$l->label = $label;
							$l->title = $this->lang['action_edit'];
							$l->css   = 'edit';
							$l->handler = 'onclick="wait();"';
							$l->href  = $this->response->get_url($this->actions_name, 'volgroup')."&volgroup=".$line[0];

							$actions .= $l->get_string();

							// format capacity + available
							$capacity = number_format($line[3], 2, '.', '');
							$available = number_format($line[4], 2, '.', '');

							// fill body
							$body[] = array(
								'name'   => '<div class="panel-heading">
									<h3 class="panel-title">'.$line[0].'</h3>
								</div>
								<div class="panel-body">
								<b>Name:</b> '.$line[0].'<br/>
								<b>Location:</b> '.$line[1].'<br/>
								<b>Filesystem:</b> '.$line[2].'<br/>
								<b>Capacity:</b> '.$capacity." GB".'<br/>
								<b>Available:</b> '.$available." GB".'<br/>
								</div>',
								'action' => $actions
							);
						}
					}
				}
			}

			$h['name']['title'] = $this->lang['table_name'];
			$h['location']['title'] = $this->lang['table_location'];
			$h['filesystem']['title'] = $this->lang['table_filesystem'];
			$h['capacity']['title'] = $this->lang['table_capacity'];
			$h['available']['title'] = $this->lang['table_available'];
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;

			$table = $this->response->html->tablebuilder('vmware_ds_list', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'name';
			$table->limit           = 20;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabellerr';
			$table->css             = 'htmlobject_table storageboxx';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action	    = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
