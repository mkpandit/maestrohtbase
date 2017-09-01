<?php
/**
 * Select vSphere Hosts to manage
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_host_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_host_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_id';
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
	function __construct($htvcenter, $response, $controller) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');
		$this->controller = $controller;

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
		$virtualization	= new virtualization();
		$appliance		= new appliance();
		$resource		= new resource();
		$htvcenter_server = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$htvcenter_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource			= $resource;
		$this->appliance		= $appliance;
		$this->virtualization	= $virtualization;
		$this->htvcenter_server	= $htvcenter_server;
		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.host_list';

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
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-host-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function select() {

		$head['appliance_icon']['title'] = " ";
		$head['appliance_icon']['sortable'] = false;
		$head['appliance_name']['title'] = $this->lang['table_name'];
		$head['appliance_cluster']['title'] = $this->lang['table_cluster'];
		$head['appliance_dc']['title'] = $this->lang['table_datacenter'];
		$head['appliance_action']['title'] = " ";
		$head['appliance_action']['sortable'] = false;

		$table = $this->response->html->tablebuilder('vmware_host_select', $this->response->get_array($this->actions_name, 'select'));
		$table->sort            = 'appliance_name';
		$table->limit           = 10;
		$table->offset          = 0;
		$table->order           = 'ASC';
		$table->autosort        = false;
		$table->sort_link       = false;
		$table->init();

		// handle tab in tab
		if($this->response->html->request()->get('iplugin') !== '') {
			$strControler = 'icontroller';
		}
		else if($this->response->html->request()->get('rplugin') !== '') {
			$strControler = 'rcontroller';
		}
		else if($this->response->html->request()->get('aplugin') !== '') {
			$strControler = 'acontroller';
		} else {
			$strControler = 'controller';
		}

		$table_max_hosts = 0;
		if(file_exists($this->statfile)) {
			$lines = explode("\n", file_get_contents($this->statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = $this->htvcenter->string_to_array($line, '|', '=');

						$a = $this->response->html->a();
						$a->title   = $this->lang['action_edit'];
						$a->label   = 'Datastore';
						$a->handler = 'onclick="wait();"';
						$a->css     = 'edit';
						$a->href    = $this->response->get_url($strControler, "vmware-vsphere-ds").'&vmware_vsphere_ds_action=edit&appliance_id='.$this->appliance->id.'&esxhost='.$line['name'];
						$links = $a->get_string();


						$a = $this->response->html->a();
						$a->title   = $this->lang['action_edit'];
						$a->label   = 'Network';
						$a->handler = 'onclick="wait();"';
						$a->css     = 'edit';
						$a->href    = $this->response->get_url($strControler, "vmware-vsphere-vs").'&vmware_vsphere_vs_action=edit&appliance_id='.$this->appliance->id.'&esxhost='.$line['name'];
						$links .= $a->get_string();

						$cluster = '-';
						if ($line['name'] !== $line['cluster']) {
							$cluster = $line['cluster'];
						}

						$ta[] = array(
							'appliance_icon' => '<span class="pill active">active</span>',
							'appliance_name' => $line['name'],
							'appliance_cluster' => $cluster,
							'appliance_dc' => $line['datacenter'],
							'appliance_action' => $links,
						);
						$table_max_hosts++;
					}
				}
			}
		}   
		$table->max				= $table_max_hosts;
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->form_action	    = $this->response->html->thisfile;
		$table->identifier      = 'appliance_name';
		$table->identifier_name = 'appliance_name';
		$table->body = $ta;
		return $table->get_string();
	}




}
?>
