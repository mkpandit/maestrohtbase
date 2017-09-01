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

class vmware_vsphere_ds_volgroup
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

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));
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
		$volgroup = $this->response->html->request()->get('volgroup');
		if($volgroup === '') {
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
		$this->volgroup = $volgroup;
		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.vmdk_list';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-ds-volgroup.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add(sprintf($this->lang['label'],$this->response->html->request()->get('volgroup'), $this->appliance->name), 'label');
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

			$this->__reload_vmdks($this->volgroup);

			$resource_icon_default="/htvcenter/base/img/resource.png";
			$host_icon="/htvcenter/base/plugins/vmware-vsphere/img/plugin.png";
			$state_icon="/htvcenter/base/img/".$this->resource->state.".png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/htvcenter/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$host_icon)) {
				$resource_icon_default=$host_icon;
			}
			$body = array();
			$file = $this->statfile;
			$identifier_disabled = array();
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));

				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$clone_action_enabled = false;
							$line = explode('@', $line);
							$name = str_replace('.vmdk', '', substr($line[0], strripos($line[0],'/')+1));

							$image_description = '';
							$image = new image();
							$image->get_instance_by_name($name);
							if ($image->id > 2) {
								if ($image->isactive == 1) {
									$state_icon = '<span class="pill active">active</span>';
									$identifier_disabled[] = $name;
								} else if ($image->isactive == 0) {
									$state_icon = '<span class="pill idle">idle</span>';
									$clone_action_enabled = true;
								}
								$image_description = $image->comment;
							} else {
								// not imported to HyperTask
								$state_icon = '<span class="pill inactive">unaligned</span>';
								$clone_action_enabled = true;
							}
							$state_icon_img = $state_icon;

							$clone_action = '';
							if ($clone_action_enabled) {
								$a_clone = $this->response->html->a();
								$a_clone->label   = $this->lang['action_clone'];
								$a_clone->title   = $this->lang['action_clone'];
								$a_clone->css     = 'clone';
								$a_clone->handler = 'onclick="wait();"';
								$a_clone->href    = $this->response->get_url($this->actions_name, "clone")."&vmdk=".$name;
								$clone_action = $a_clone->get_string();
							}
							
							$body[] = array(
								'state' => $state_icon_img,
								'name'   => $name,
								'comment' => $image_description,
								'action'  => $clone_action,
								#'location' => "<nobr>".$line[1]."</nobr>",
								#'filesystem' => $line[2],
								#'capacity' => $capacity." GB",
								#'available' => $available." GB",
								#'action' => $a->get_string(),
								#'list' => $l->get_string(),
							);
						}
					}
				}
			}

			$h['state'] = array();
			$h['state']['title'] = '&#160;';
			$h['state']['sortable'] = false;
			$h['name']['title'] = $this->lang['table_name'];
			$h['comment']['title'] = '&#160;';
			$h['comment']['sortable'] = false;
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;

			$table = $this->response->html->tablebuilder('ds_list', $this->response->get_array($this->actions_name, 'volgroup'));
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
			$table->identifier      = 'name';
			$table->identifier_name = $this->identifier_name;
			$table->actions_name    = $this->actions_name;
			$table->identifier_disabled = $identifier_disabled;
			$table->actions         = array(array('remove' => $this->lang['action_remove']));

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Reload DataStore vmdks
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function __reload_vmdks($volgroup) {
		$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-datastore post_vmdk_list -i ".$this->resource->ip." -n ".$this->volgroup;
		$file = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$this->resource->ip.'.vmdk_list';
		if(file_exists($file)) {
			unlink($file);
		}
		$htvcenter_server = new htvcenter_server();
		$htvcenter_server->send_command($command, NULL, true);
		while (!file_exists($file)) // check if the data file has been modified
		{
			usleep(10000); // sleep 10ms to unload the CPU
			clearstatcache();
		}
		return true;
	}


}
?>
