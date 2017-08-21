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

class vmware_vsphere_dc_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_dc_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_dc_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_dc_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_dc_id';
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
		$this->statfile = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.dc_list';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-dc-edit.tpl.php');
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

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_dc_add'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['ds_add'] = $a->get_string();

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$tmp = $line;
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							if(is_array($line) && count($line) > 0) {

								// prepare action section
								$actions = '';
							
								$add_host_action = '';
								$add_host_action_link = '';
								$a = $this->response->html->a();
								$a->label = $this->lang['action_dc_add_host'];
								$a->title = $this->lang['action_dc_add_host'];
								$a->css   = 'edit';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'add_host')."&".$this->identifier_name."=".$line['name'];
								$actions .= $a->get_string();
						
								$actions .= '<br>';
							
								$add_cluster_action = '';
								$add_cluster_action_link = '';
								$a = $this->response->html->a();
								$a->label = $this->lang['action_dc_add_cluster'];
								$a->title = $this->lang['action_dc_add_cluster'];
								$a->css   = 'edit';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'add_cluster')."&".$this->identifier_name."=".$line['name'];
								$actions .= $a->get_string();
						
								$actions .= '<br>';

								$remove_action = '';
								$remove_action_link = '';
								$a = $this->response->html->a();
								$a->label = $this->lang['action_dc_remove'];
								$a->title = $this->lang['action_dc_remove'];
								$a->css   = 'remove';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'remove')."&".$this->identifier_name."=".$line['name'];
								$actions .= $a->get_string();

							
								// cluster and hosts in cluster
								$cluster = '';
								$hosts_in_cluster = '';
								if (isset($line['cluster'])) {
									$cluster_arr = explode(",", $line['cluster']);
									foreach($cluster_arr as $c) {
										$found_hosts_in_cluster = false;
										if (isset($line['hosts'])) {
											$host_arr = explode(",", $line['hosts']);
											foreach($host_arr as $ho) {
												$h_config_arr = explode(":", $ho);
												if ($h_config_arr[0] == $c) {
													$found_hosts_in_cluster = true;
													$hosts_in_cluster .= $h_config_arr[1]."<br>";
												}
											}
										}

										$a = $this->response->html->a();
										$a->label = $this->lang['action_dc_cluster_remove'];
										$a->title = $this->lang['action_dc_cluster_remove'];
										$a->css   = 'remove';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, 'remove_cluster')."&".$this->identifier_name."=".$line['name']."&cluster=".$c;
										$remove_cluster_action = $a->get_string();

										$a = $this->response->html->a();
										$a->label = $this->lang['action_dc_cluster_add_host'];
										$a->title = $this->lang['action_dc_cluster_add_host'];
										$a->css   = 'add';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, 'add_hosttocluster')."&".$this->identifier_name."=".$line['name']."&cluster=".$c;
										$add_host_to_cluster_action = $a->get_string();
									
										if ($found_hosts_in_cluster) {
											$cluster .= $c."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$add_host_to_cluster_action." ".$remove_cluster_action."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; |_ ESX Host ".$hosts_in_cluster."<br>";
										} else {
											$cluster .= $c."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$add_host_to_cluster_action." ".$remove_cluster_action."<br>";
										}
									
									}
								}
							
								// host in dc
								if (isset($line['hosts'])) {
									$host_arr = explode(",", $line['hosts']);
									foreach($host_arr as $ho) {
										$h_config_arr = explode(":", $ho);
										if (!isset($h_config_arr[1])) {
											$cluster .= "ESX Host ".$h_config_arr[0]."<br>";
										}
										$a = $this->response->html->a();
										$a->title   = $this->lang['action_edit'];
										$a->label   = 'Datastore';
										$a->handler = 'onclick="wait();"';
										$a->css     = 'edit';
										$a->href    = '/htvcenter/base/index.php?plugin=vmware-vsphere&controller=vmware-vsphere-ds&vmware_vsphere_ds_action=edit&appliance_id='.$this->appliance->id.'&esxhost='.$h_config_arr[0];
										$links = $a->get_string();
										$cluster .= $a->get_string();
										$cluster .= "<br>";
										
										$a = $this->response->html->a();
										$a->title   = $this->lang['action_edit'];
										$a->label   = 'Network';
										$a->handler = 'onclick="wait();"';
										$a->css     = 'edit';
										$a->href    = '/htvcenter/base/index.php?plugin=vmware-vsphere&controller=vmware-vsphere-vs&vmware_vsphere_vs_action=edit&appliance_id='.$this->appliance->id.'&esxhost='.$h_config_arr[0];
										$links = $a->get_string();
										$cluster .= $a->get_string();
										$cluster .= "<br><br>";
										
										
										
									}
								}
							
								// fill body
								$body[] = array(
									'name'   => $line['name'],
									'cluster' => $cluster,
									'action' => $actions
								);
							} else {
								$_REQUEST[$this->message_param] = $tmp;
							}
						}
					}
				}
			}

			$h['name']['title'] = $this->lang['table_name'];
			$h['cluster']['title'] = $this->lang['table_cluster'];
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;

			$table = $this->response->html->tablebuilder('vmware_dc_list', $this->response->get_array($this->actions_name, 'edit'));
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
