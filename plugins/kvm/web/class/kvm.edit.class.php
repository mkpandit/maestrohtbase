<?php
/**
 * KVM Edit Storage
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class kvm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_identifier';
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
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;

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
		$this->statfile   = $this->htvcenter->get('basedir').'/plugins/kvm/web/storage/'.$resource->id.'.vg.stat';
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
			$t = $this->response->html->template($this->tpldir.'/kvm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_kvm'], $this->response->html->request()->get('storage_id'));
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

		if(strpos($this->deployment->type, 'kvm') !== false) {

			// check device-manager
			$devicemgm = false;
			if($this->file->exists($this->htvcenter->get('webdir').'/plugins/device-manager/class/device-manager.addvg.class.php')) {
				$devicemgm = true;
			}

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['deployment'] = $this->deployment->type;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$body = array();
			$file = $this->statfile;
			if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					$i = 0;
					foreach($lines as $line) {
						if($line !== '') {
							$line  = explode('@', $line);
							$name  = substr($line[0], strripos($line[0], '/'));

							//handle format send by df
							$line[5] = str_replace('MB', '.00', $line[5]);
							$line[6] = str_replace('MB', '.00', $line[6]);

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

							if($d['deployment'] === 'kvm-lvm-deployment') {
								/* $body[$i] = array(
									'name' => $name,
									'pv' => $line[1],
									'lv' => $line[2],
									'sn' => $line[3],
									'attr' => $line[4],
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'action' => $a->get_string(),
								);
								if($devicemgm === true) {
									if($line[2] === '0' && $line[3] === '0') {
										$a = $this->response->html->a();
										$a->title   = $this->lang['action_remove'];
										$a->label   = $this->lang['action_remove'];
										$a->handler = 'onclick="wait();"';
										$a->css     = 'remove';
										$a->href    = $this->response->get_url($this->actions_name, "removevg").'&volgroup='.$name;
										$body[$i]['action'] = $body[$i]['action'].$a->get_string();
									}
								}
								*/
								

								$vused = $vsize - $vfree;
								//$vused = 555;
								if ( (!empty($_POST['idstor'])) && ($_POST['idstor'] == $name) ) {
									echo $vused; die();
								}
								$lvmrow = '<div class="panel gaugestorage" idstor="'.$name.'">
																	<div class="panel-heading">
																		<div class="panel-control panel-control-component">
																		'.$a->get_string().'
																					</div>
																		<h3 class="panel-title">'.$name.'</h3>
																	</div>
													
																	<!-- Gauge JS -->
																	<!--===================================================-->
																	<div class="panel-body">
																		<div class="text-center">
													
																			<!--Gauge placeholder-->
																			<canvas class="canvas-responsive" height="70" id="gauge-'.$i.'"></canvas>
													
																			<p class="h4">
																				<span class="label label-primary" id="gauge-txt-'.$i.'"></span>
																				<span class="sizeused" style="display:none;">'.$vused.'</span>
																				<span class="sizeof">of</span>
																				<span class="sizestor">'.$vsize.'</span><hr><div class="storageinfomax">';
																			
																			if (!empty($line[1])) {
																				$lvmrow .= '<span class="datastorage">PV: '.$line[1].'</span>';
																			}
																			if (!empty($line[2])) {
																				$lvmrow .= '<span class="datastorage">LV: '.$line[2].'</span>';
																			}
																			if (!empty($line[3])) {
																				$lvmrow .= '<span class="datastorage">SN: '.$line[3].'</span>';
																			}
																			if (!empty($line[4])) {
																				$lvmrow .= '<br/><span class="datastorage">Attributes: '.$line[4].'</span>';
																			}
																			$lvmrow .= '</div></p>
																		</div>
																	</div>
																	<!--===================================================-->
																	<!-- End Gauge JS -->
													
																</div>
								';
								$body[$i] = array('name' => $lvmrow);
							}
							if($d['deployment'] === 'kvm-bf-deployment') {
								/*$body[$i] = array(
									'name'   => $name,
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'attr' => '&#160;',
									'action' => $a->get_string(),
								);*/

$vused = $vsize - $vfree;
//$vused = 555;
	if ( (!empty($_POST['idstor'])) && ($_POST['idstor'] == $name) ) {
									echo $vused; die();
								}
$body[$i] = array('name' => '

								<div class="panel gaugestorage" idstor="'.$name.'">
									<div class="panel-heading">
										<div class="panel-control panel-control-component">
										'.$a->get_string().'
													</div>
										<h3 class="panel-title">'.$name.'</h3>
									</div>
					
									<!-- Gauge JS -->
									<!--===================================================-->
									<div class="panel-body">
										<div class="text-center">
					
											<!--Gauge placeholder-->
											<canvas class="canvas-responsive" height="70" id="gauge-'.$i.'"></canvas>
					
											<p class="h4">
												<span class="label label-primary" id="gauge-txt-'.$i.'"></span>
												<span class="sizeused" style="display:none;">'.$vused.'</span>
												<span class="sizeof">of</span>
												<span class="sizestor">'.$vsize.'</span>
											</p>
										</div>
									</div>
									<!--===================================================-->
									<!-- End Gauge JS -->
					
								</div>
							

');
							}
							if($d['deployment'] === 'kvm-gluster-deployment') {
								$vg  = '<b>'.$this->lang['table_type'].'</b>: '.$line[1].'<br>';
								$vg .= '<b>'.$this->lang['table_status'].'</b>: '.$line[2].'<br>';
								$vg .= '<b>'.$this->lang['table_topology'].'</b>: '.$line[3].'<br>';
								$vg .= '<b>'.$this->lang['table_transport'].'</b>: '.$line[4];
								$brick_arr  = explode(',', $line[7]);
								$bricks  = '';
								foreach($brick_arr as $brick) {
									$bricks  .= '<b>'.$brick.'</b><br>';
								}
								$body[$i] = array(
									'name'   => $name,
									'vg' => $vg,
									'bricks' => $bricks,
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'action' => $a->get_string(),
								);
							

							}
						}
						$i++;
					}
				}
			}

			if($d['deployment'] === 'kvm-lvm-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['pv']['title'] = $this->lang['table_pv'];
				$h['lv']['title'] = $this->lang['table_lv'];
				$h['sn']['title'] = $this->lang['table_sn'];
				$h['attr']['title'] = $this->lang['table_attr'];
				$h['action']['title'] = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-bf-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['attr']['title'] =  '&#160;';
				$h['attr']['sortable'] = false;
				$h['action']['title'] = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-gluster-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['vg']['title'] = $this->lang['table_vg'];
				$h['bricks']['title'] = $this->lang['table_bricks'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['edit']['title'] = '&#160;';
				$h['edit']['sortable'] = false;
			}

			$table = $this->response->html->tablebuilder('kvm_edit', $this->response->get_array($this->actions_name, 'edit'));
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

			$d['add'] = '';
			if($devicemgm === true && $d['deployment'] === 'kvm-lvm-deployment') {
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
