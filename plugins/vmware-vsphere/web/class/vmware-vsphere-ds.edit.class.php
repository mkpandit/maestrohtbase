<?php
/**
 * vSphere Hosts DataStore Manager
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_ds_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_ds_id';
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
		$host = $this->response->html->request()->get('esxhost');
		if($host === '') {
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
		$this->host = $host;
		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.ds_list';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-ds-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_vsphere'], $this->response->html->request()->get('appliance_id'));
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

		if($this->virtualization->type === 'vmware-vsphere') {

			$d['state'] = '<span class="pill active">active</span>';
			$d['name'] = $this->host;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_nas'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_nas")."&esxhost=".$this->host;
			$d['ds_add_nas'] = $a->get_string();

/*			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_iscsi'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add_iscsi");
			$d['ds_add_iscsi'] = $a->get_string();
 */
			
			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							
							// prepare remove link
							$actions = '';
							$remove_action = '';
							$remove_action_link = '';
							$a = $this->response->html->a();
							if (!strcmp($line['type'], "NFS"))  {
								$a->label = $this->lang['action_ds_remove'];
								$a->title = $this->lang['action_ds_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove_nas')."&volgroup=".$line['name']."&esxhost=".$this->host;
								$actions .= $a->get_string();
							}
							if (!strcmp($line['type'], "VMFS"))  {
								$a->label = $this->lang['action_ds_remove'];
								$a->title = $this->lang['action_ds_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove_iscsi')."&volgroup=".$line['name']."&esxhost=".$this->host;
								$actions .= $a->get_string();
							}

/*							$l = $this->response->html->a();
							$l->label = $this->lang['action_edit'];
							$l->title = $this->lang['action_edit'];
							$l->css   = 'edit';
							$l->handler = 'onclick="wait();"';
							$l->href  = $this->response->get_url($this->actions_name, 'volgroup')."&volgroup=".$line['name'];
							$actions .= $l->get_string();
*/
							// format capacity + available
							$capacity = number_format($line['capacity'], 2, '.', '');
							// $available = number_format($line[4], 2, '.', '');

							// fill body
							$body[] = array(
								'name'   => $line['name'],
								'location' => "<nobr>".$line['remoteHost'].":".$line['remotePath']."</nobr>",
								'filesystem' => $line['type'],
								'capacity' => $capacity." GB",
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
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
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
