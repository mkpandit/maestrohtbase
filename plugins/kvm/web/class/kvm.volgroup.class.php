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

class kvm_volgroup
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
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));

		$this->volgroup = $this->response->params['volgroup'];
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

		$this->statfile = $this->htvcenter->get('basedir').'/plugins/kvm/web/storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
		
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
			$t = $this->response->html->template($this->tpldir.'/kvm-volgroup.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_attr'], 'lang_attr');
			$t->add($this->lang['lang_pv'], 'lang_pv');
			$t->add($this->lang['lang_size'], 'lang_size');
			$t->add($this->htvcenter->get('baseurl'), 'baseurl');
			$t->add(sprintf($this->lang['label'], $this->volgroup, $data['name']), 'label');
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

		if (isset($_GET['treeaction']) && $_GET['treeaction'] == 'remove') {
			if (isset($_POST['data'])) {
				$data = $_POST['data'];
				$message = '';
				$error = 0;
				foreach ($data as $obj) {

					if (isset($obj['imgid']) && !empty($obj['path'])) {
						$mes = $this->imageremove($obj['imgid']);
						if ($mes != '') {
							$message .= $mes;
							break;
						}
					}

					if (isset($obj['path']) && !empty($obj['path'])) {
						$mes = $this->fsremove($obj['path']);
						if ($mes != '') {
							$message .= $mes;
							break;
						}
					}
				}

				if ($message == '') {
					$message = '1';
				}
				echo $message;
			}

			die();
		}

		if(strpos($this->deployment->type, 'kvm') !== false) {
			$resource_icon_default = "/img/resource.png";
			$storage_icon = "/plugins/kvm/img/plugin.png";
			if ($this->file->exists($this->htvcenter->get('webdir').$storage_icon)) {
				$resource_icon_default = $storage_icon;
			}
			$resource_icon_default = $this->htvcenter->get('baseurl').$resource_icon_default;

			// Storage info
			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = '<span id="resido">'.$this->resource->id.'</span> / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['deployment'] = $this->deployment->type;
			$d['id'] = $this->storage->id;

			// Volgroup info
			$lines = explode("\n", $this->file->get_contents($this->htvcenter->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.vg.stat'));


			foreach($lines as $line) {
				$line = explode("@", $line);
				if(isset($line[0]) && $line[0] === $this->volgroup) {

					//handle format sent by df
					$line[5] = str_replace('MB', '.00', $line[5]);
					$line[6] = str_replace('MB', '.00', $line[6]);

					$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '').' MB';
					$vfree = str_replace('m', '', $line[6]);
					if($vfree !== '0') {
						$vfree = substr($line[6], 0, strpos($line[6], '.'));
					}
					$d['volgroup_name'] = $line[0];
					if($d['deployment'] === 'kvm-lvm-deployment') {
						$d['volgroup_pv'] = $line[1];
						$d['volgroup_lv'] = $line[2];
						$d['volgroup_sn'] = $line[3];
						$d['volgroup_attr'] = $line[4];
					}
					if($d['deployment'] === 'kvm-bf-deployment') {
						$d['volgroup_pv'] = '-';
						$d['volgroup_lv'] = '-';
						$d['volgroup_sn'] = '-';
						$d['volgroup_attr'] = 'file';
					}
					if($d['deployment'] === 'kvm-gluster-deployment') {
						$d['volgroup_pv'] = '-';
						$d['volgroup_lv'] = '-';
						$d['volgroup_sn'] = '-';
						$d['volgroup_attr'] = 'gluster';
					}
					$d['volgroup_vsize'] = $vsize;
					$d['volgroup_vfree'] = number_format($vfree, 0, '', '').' MB';
				}
			}

			$a = '&#160';
			if($d['volgroup_vfree'] !== '0 MB') {
				$a = $this->response->html->a();
				$a->label   = $this->lang['action_add'];
				$a->css     = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href    = $this->response->get_url($this->actions_name, "add");
			}
			$d['add'] = $a;

			$body = array();

			$file = $this->statfile;
			if($this->file->exists($file)) {

				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					$disabled = array();
					$t = $this->response->html->template($this->htvcenter->get('webdir').'/js/htvcenter-progressbar.js');
					foreach($lines as $line) {
						if($line !== '') { 
							$image_add_remove = '';
							$deployment_type = ''; 
							$line = explode('@', $line);
							$name = $line[1];
							$mode = substr($line[3], 0, 1);
							$s = '';
							$c = '';
							$r = '';
							$src = '';
							$progress = '';
							if ($line[4] == "clone_in_progress") {
								// add to disabled identifier
								$disabled[] = $name;
								// progressbar
								$t->add(uniqid('b'), 'id');
								$t->add($this->htvcenter->get('baseurl').'/api.php?action=plugin&plugin=kvm&kvm_action=progress&name='.$this->resource->id.'.lvm.'.$name.'.sync_progress', 'url');
								$t->add($this->lang['action_clone_in_progress'], 'lang_in_progress');
								$t->add($this->lang['action_clone_finished'], 'lang_finished');
								$progress = $t->get_string();
							} else if ($line[4] == "sync_in_progress") {
								$progress = $this->lang['action_sync_in_progress'];
								$disabled[] = $name;
							} else {
								if($d['deployment'] === 'kvm-lvm-deployment') {
									$volume_size = number_format(substr($line[4], 0, strpos($line[4], '.')), 0, '', '').' MB';
								}
								if($d['deployment'] === 'kvm-bf-deployment') {
									$volume_size = number_format(($line[4]/(1000*1000)), 0, '', '').' MB';
								}
								if($d['deployment'] === 'kvm-gluster-deployment') {
									$volume_size = number_format(($line[4]/(1000*1000)), 0, '', '').' MB';
								}
								$image_add_remove = '';
								$deployment_type = '';
								$image = $this->htvcenter->image();


# Image
								$image->get_instance_by_name($name);

								if (strlen($image->id)) {



									if( $image->type == $this->deployment->type ) {
										if( $line[0] === $this->deployment->type && $line[4] !== "sync_in_progress" && $line[4] !== "clone_in_progress" ) {
											if($d['volgroup_vfree'] !== '0 MB' ) {
												if($mode !== 's') {
													$s = $this->response->html->a();
													$s->title   = $this->lang['action_snap']; 
													$s->label   = $this->lang['action_snap'];
													$s->handler = 'onclick="wait();"';
													$s->css     = 'snap';
													$s->href    = $this->response->get_url($this->actions_name, "snap").'&lvol='.$line[1];
													$snaphref = $s->href;
													$s = $s->get_string();
												} else {
													$disabled[] = $line[5];
													$src = $line[5];
												}
												if($vfree >= (int)substr($line[4], 0, strpos($line[4], '.'))) {
													$c = $this->response->html->a();
													$c->title   = $this->lang['action_clone'];
													$c->label   = $this->lang['action_clone'];
													$c->handler = 'onclick="wait();"';
													$c->css     = 'clone';
													$c->href    = $this->response->get_url($this->actions_name, "clone").'&lvol='.$line[1];

													$clonehref = $c->href;
													$c = $c->get_string();

												}
											}
											$r = $this->response->html->a();
											$r->title   = $this->lang['action_resize'];
											$r->label   = $this->lang['action_resize'];
											$r->handler = 'onclick="wait();"';
											$r->css     = 'resize';
											$r->href    = $this->response->get_url($this->actions_name, "resize").'&lvol='.$line[1];
											$r = $r->get_string();
											$deployment_type = $this->deployment->type;
										} else {
											$disabled[] = $name;
										}
									}
								}
								// create/remove image object, check if image exists
								if($d['deployment'] === 'kvm-gluster-deployment') {
									$path_glusters = "gluster://".$this->resource->ip."/".$this->volgroup."/".$name;
									$path  = '<b>'.$this->lang['table_path_physical'].'</b>: '.$line[2].'<br>';
									$path .= '<b>'.$this->lang['table_path_glusters'].'</b>: '.$path_glusters.'<br>';
								}
								if (!strlen($image->id)) {
									$i = $this->response->html->a();
									$i->title   = $this->lang['action_add_image'];
									$i->label   = $this->lang['action_add_image'];
									$i->handler = 'onclick="wait();"';
									$i->css     = 'add';
									if($d['deployment'] === 'kvm-lvm-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device=/dev/'.$this->volgroup.'/'.$name.'&image_command=add';
										$rdev = '/dev/'.$this->volgroup.'/'.$name;
									} else if($d['deployment'] === 'kvm-bf-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$line[2].'&image_command=add';

										$rdev = $line[2];
										$pazz = $line[2];

									} else if($d['deployment'] === 'kvm-gluster-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$path_glusters.'&image_name='.$name.'&image_command=add';
										$rdev = $path_glusters;
										$imgname = $name;
									}
									$disabled[] = $name;
									$image_add_remove = $i->get_string();


									// automatic add:

										$root_device = $rdev;
										if ($this->deployment->type == 'kvm-gluster-deployment') {
											$image_name = $imgname;
										} else {
											$image_name = basename($root_device);
										}

										// check if image name is not in use yet
										$image = new image();
										$image->get_instance_by_name($image_name);
										if (strlen($image->id)) {
											//$errors[] = sprintf($this->lang['error_exists'], $image_name);
										} else {
											$tables = $this->htvcenter->get('table');
											$image_fields = array();
											$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
											$image_fields['image_name'] = $image_name;
											$image_fields['image_type'] = $this->deployment->type;
											$image_fields['image_rootfstype'] = 'local';
											$image_fields['image_storageid'] = $this->storage->id;
											$image_fields['image_comment'] = "Image Object for volume $image_name";
											$image_fields['image_rootdevice'] = $root_device;
											$image = new image();
											$image->add($image_fields);
											//$message[] = sprintf($this->lang['msg_added_image'], $image_name);
										}

									// ----
								
									
									if( $image->type != $this->deployment->type ) {
										$deployment_type = $image->type;
										$disabled[] = $name;
									} else {
										$i = $this->response->html->a();
										$i->title   = $this->lang['action_remove_image'];
										$i->label   = $this->lang['action_remove_image'];
										$i->handler = 'onclick="wait();"';
										$i->css     = 'remove';
										$i->href    = $this->response->get_url($this->actions_name, "image").'&image_id='.$image->id.'&image_command=remove';
										$image_add_remove = $i->get_string();
									}
								} else {
									if( $image->type != $this->deployment->type ) {
										$deployment_type = $image->type;
										$disabled[] = $name;
									} else {
										$i = $this->response->html->a();
										$i->title   = $this->lang['action_remove_image'];
										$i->label   = $this->lang['action_remove_image'];
										$i->handler = 'onclick="wait();"';
										$i->css     = 'remove';
										$i->href    = $this->response->get_url($this->actions_name, "image").'&image_id='.$image->id.'&image_command=remove';

										$image_add_remove = $i->get_string();
									}
								}
							}


								
							$state = '<span class="pill inactive">unaligned</span>';
							if($d['deployment'] === 'kvm-lvm-deployment') {
								switch($image->isactive) {
									case '0':
										$state = '<span class="pill idle">idle</span>';
									break;
									case '1':
										$state = '<span class="pill active">active</span>';
									break;
								}

								$rootimg = str_replace('/'.$name, '', $image->rootdevice);
								$data  = '<b>'.$this->lang['table_name'].'</b>:'.$name.'<br>';
								$data .= '<b>'.$this->lang['table_deployment'].'</b>:'.$deployment_type.'<br>';
								$data .= '<b>'.$this->lang['table_attr'].'</b>:'.$line[3].'<br>';
								$data .= '<b>'.$this->lang['table_size'].'</b>:'.$volume_size.'<br>';
								$data .= '<b>'.'Root'.'</b>:'.$rootimg.'<br>';
								$data .= '<b>'.$this->lang['table_source'].'</b>:'.$src.'<br>';
								$data .= '<br><br>';
								
								$arr = array(
									'state'    => $state,
									'deploy'   => $deployment_type,
									'name'     => $name,
									'attr'     => $line[3],
									'source'   => $src,
									'size'     => $volume_size,
									'data'     => $data,
									'path' => $pazz,
									'progress' => $progress,
									'action'   => $image_add_remove.$c.$s.$r,
								);
								$body[] = $arr;
								$arr['clonehref'] = $clonehref;
								$arr['snaphref'] = $snaphref;
								$arr['rootimg'] = $image->id;
								$bodytree[$name] = $arr;

								$voloptions .= '<option value="'.$name.'">'.$name.'</option>';

							}
							if($d['deployment'] === 'kvm-bf-deployment') {
								switch($image->isactive) {
									case '0':
										$state = '<span class="pill idle">idle</span>';
									break;
									case '1':
										$state = '<span class="pill active">active</span>';
									break;
								}
								$root = str_replace('/'.$name, '', $image->rootdevice);


								$data  = '<b>'.$this->lang['table_name'].'</b>:'.$name.'<br>';
								$data .= '<b>'.$this->lang['table_deployment'].'</b>:'.$deployment_type.'<br>';
								$data .= '<b>'.$this->lang['table_attr'].'</b>:'.$line[3].'<br>';
								$data .= '<b>'.$this->lang['table_size'].'</b>:'.$volume_size.'<br>';
								$data .= '<b>'.'Root'.'</b>:'.$root.'<br>';
								$data .= '<b>'.$this->lang['table_source'].'</b>:'.str_replace($root.'/', '',$src).'<br>';
								$data .= '<br>';
								$rootimg = $image->id;
								$arr = array(
									'state'    => $state,
									'deploy'   => $deployment_type,
									'name'     => $name,
									'attr'     => $line[3],
									'source'   => $src,
									'size'     => $volume_size,
									'data'     => $data,
									'progress' => $progress,
									'path' => $pazz,
									'action'   => $image_add_remove.$c.$s,
								);

								$body[] = $arr;
								$arr['clonehref'] = $clonehref;
								$arr['snaphref'] = $snaphref;
								$arr['rootimg'] = $image->id;
								$bodytree[$name] = $arr;

								/*if ($name == 'somefile2') {
									
									var_dump($body); 
									echo '<br/>======== <br/>';
									var_dump($line);
									echo '<br/>======== <br/>';
									var_dump($image);
									//die();
								}*/

								$voloptions .= '<option value="'.$name.'">'.$name.'</option>';
							}
							if($d['deployment'] === 'kvm-gluster-deployment') {
								$arr = array(
									'deploy' => $deployment_type,
									'name'   => $name,
									'path'   => $path,
									'source' => $src,
									'size'   => $volume_size,
									'image'   => $image_add_remove,
									'path' => $pazz,
									'snap'   => $s,
									'clone'  => $c,
								);
								$body[] = $arr;
								$arr['clonehref'] = $clonehref;
								$arr['snaphref'] = $snaphref;
								$arr['rootimg'] = $rootimg;
								$bodytree[$name] = $arr;
								$voloptions .= '<option value="'.$name.'">'.$name.'</option>';
							}


						}
					}
				}
			}

			if($d['deployment'] === 'kvm-lvm-deployment') {
				$h['state']['title']       = $this->lang['table_state'];
				$h['name']['title']        = $this->lang['table_name'];
				$h['name']['hidden']       = true;
				$h['deploy']['title']      = $this->lang['table_deployment'];
				$h['deploy']['hidden']     = true;
				$h['attr']['title']        = $this->lang['table_attr'];
				$h['attr']['hidden']       = true;
				$h['size']['title']        = $this->lang['table_size'];
				$h['size']['hidden']       = true;
				$h['source']['title']      = $this->lang['table_source'];
				$h['source']['hidden']     = true;
				$h['data']['title']        = '&#160;';
				$h['data']['sortable']     = false;
				$h['progress']['title']    = '&#160;';
				$h['progress']['sortable'] = false;
				$h['action']['title']    = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-bf-deployment') {
				$h['state']['title']       = $this->lang['table_state'];
				$h['name']['title']        = $this->lang['table_name'];
				$h['name']['hidden']       = true;
				$h['deploy']['title']      = $this->lang['table_deployment'];
				$h['deploy']['hidden']     = true;
				$h['attr']['title']        = $this->lang['table_attr'];
				$h['attr']['hidden']       = true;
				$h['size']['title']        = $this->lang['table_size'];
				$h['size']['hidden']       = true;
				$h['source']['title']      = $this->lang['table_source'];
				$h['source']['hidden']     = true;
				$h['data']['title']        = '&#160;';
				$h['data']['sortable']     = false;
				$h['progress']['title']    = '&#160;';
				$h['progress']['sortable'] = false;
				$h['action']['title']    = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-gluster-deployment') {
				#$h['icon']['title']      = '&#160;';
				#$h['icon']['sortable']   = false;
				$h['name']['title']      = $this->lang['table_name'];
				$h['deploy']['title']    = $this->lang['table_deployment'];
				$h['path']['title']      = $this->lang['table_path'];
				$h['source']['title']    = $this->lang['table_source'];
				$h['size']['title']      = $this->lang['table_size'];
				$h['image']['title']      = '&#160;';
				$h['image']['sortable']   = false;
				$h['snap']['title']      = '&#160;';
				$h['snap']['sortable']   = false;
				$h['clone']['title']     = '&#160;';
				$h['clone']['sortable']  = false;
			}

			$table = $this->response->html->tablebuilder('kvm_lvols', $this->response->get_array($this->actions_name, 'volgroup'));
			$table->sort                = 'name';
			$table->limit               = 50;
			$table->offset              = 0;
			$table->order               = 'ASC';
			$table->max                 = count($body);
			$table->autosort            = true;
			$table->sort_link           = false;
			$table->id                  = 'Tabelle';
			$table->css                 = 'htmlobject_table';
			$table->border              = 1;
			$table->cellspacing         = 0;
			$table->cellpadding         = 3;
			$table->form_action         = $this->response->html->thisfile;
			$table->head                = $h;
			
			$table->identifier          = 'name';
			$table->identifier_name     = $this->identifier_name;
			$table->identifier_disabled = $disabled;
			$table->actions_name        = $this->actions_name;
			$table->actions             = array(array('remove' => $this->lang['action_remove']));

			$table->body                = $body;
			
			$d['table'] = $table->get_string();
			
			$dir = '/usr/share/htvcenter/storage';
			//var_dump($bodytree); die();
			$tree = $this->taketree($dir, $bodytree);

			//var_dump($tree); die();
			//var_dump($body); die();



			$d['tree'] = $tree;
			$d['voloptions'] = $voloptions;
			return $d;
		} else {
			return false;
		}
	}



	function taketree($dir, $body, $deep=0) {
			$result .= '<ul>';
			
			$cdir = scandir($dir);
			   foreach ($cdir as $key => $value)
			   {
			      if (!in_array($value,array(".","..")) && !preg_match('@^\.@', $value))
			      {
			         if (is_dir($dir .'/'. $value))
			         {  
			         	$result .= '<li data-jstree=\'{"type":"demo"}\'><span class="fspath">'.$dir.'/'.$value.'</span><span class="unimgpath">'.$body[$value]['rootimg'].'</span>'.$value;
			            $result .= $this->taketree($dir .'/'. $value, $body, 1);
			            $result .= '</li>';
			         }
			         else
			         {  
			         	if (strlen($value) > 21 ) {
			         		$valshow = substr($value, 0, 21);
			         		$valshow = $valshow.'...';
			         	} else {
			         		$valshow = $value;
			         	}

			         	$sizee = $this->sizeFilter(filesize($dir.'/'.$value));
			            $result .= '<li><span class="fspath">'.$dir.'/'.$value.'</span><span class="unimgpath">'.$body[$value]['rootimg'].'</span><span class="ende divtxt">'.$valshow.'</span>';
			           
			           if ($deep == 0) {
			           	  $result .= '<span class="divtxt"><span class="pill">'.$body[$value]['state'].'</span></span>';
			           } else {
			           	  $result .= '<span class="divtxt"></span>';
			           }
			           
			            $result .= '<span class="divtxt"><b>Size</b>: '.$sizee.'</span>
			            <span class="divtxt">
			            <div href="'.$body[$value]['clonehref'].'" onclick="wait();" title="clone" class=" divbtn spacerleft clone btn-labeled fa fa-clone"><span class="halflings-icon white export"><i></i></span>clone</div>
			           </span>
			          	<span class="divtxt">
			            <div href="'.$body[$value]['snaphref'].'" onclick="wait();" title="snap" class="divbtn spacerleft snap btn-labeled fa fa-mouse-pointer"><span class="halflings-icon white camera"><i></i></span>snap</div></span></li>';
			         }
			      }
			   }

			   $result .= '</ul>';
			   return $result;
			}



		function imageremove($imgid) {
							$image_id = $imgid;
							// check if image is not in use any more before removing
							$remove_error = 0;
							$appliance = new appliance();
							$appliance_id_list = $appliance->get_all_ids();
							foreach($appliance_id_list as $appliance_list) {
								$appliance_id = $appliance_list['appliance_id'];
								$app_image_remove_check = new appliance();
								$app_image_remove_check->get_instance_by_id($appliance_id);
								if ($app_image_remove_check->imageid == $image_id) {
									$image_is_used_by_appliance .= $appliance_id." ";
									$remove_error = 1;
								}
							}
							$mes = '';
							if ($remove_error == 1) {
								$mes = sprintf($this->lang['error_image_still_in_use'], $image_id, $image_is_used_by_appliance);
							} else {
								$image_remove = new image();
								$image_remove->remove($image_id);
							}		

							return $mes;				
		}


		function fsremove($path) {

			$command = 'rm -rf '.$path;
			$resource  = new resource();
            $resource->send_command('127.0.0.1', $command);

			/*if (!is_dir($path)) {
				unlink($path);
			} else {
				//remove inner files:
				$this->rmdir_files($path);
			}
			*/
			return '';
		}


		/*function rmdir_files($path) {
			
			$dh = opendir($path);
				if ($dh) {
					while($file = readdir($dh)) {
					   if (!in_array($file, array('.', '..'))) {
						    if (is_file($path.'/'.$file)) {
						     	unlink($path.'/'.$file);
						    }
						    else if (is_dir($path.'/'.$file)) {
						     	rmdir_files($path.'/'.$file);
						    }
					   }
					}
					  rmdir($path);
				}
		}*/


		public function sizeFilter( $bytes ) {
		    $label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
		    for( $i = 0; $bytes >= 1024 && $i < ( count( $label ) -1 ); $bytes /= 1024, $i++ );
		    return( round( $bytes, 2 ) . " " . $label[$i] );
		}


}



?>
