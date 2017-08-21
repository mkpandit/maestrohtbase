<?php
/**
 * hyperv storage vm api
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_vm_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->response->html;
		$this->response   = $this->html->response();
		$this->file       = $this->controller->file;
		$this->admin      = $this->controller->htvcenter->admin();		

		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$pool_id = $this->response->html->request()->get('volgroup');
		// set ENV
		$this->response->params['appliance_id'] = $id;
		$this->response->params['volgroup'] = $pool_id;
		$appliance = new appliance();
		$resource  = new resource();

		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);

		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = 'hyperv-stat/'.$resource->id.'.pick_iso_config';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'filepicker':
				$this->filepicker();
			break;
			case 'filebrowser':
				$this->filebrowser();
			break;
			case 'dirbrowser':
				$this->dirbrowser();
			break;
		}
	}


	//--------------------------------------------
	/**
	 * Directorybrowser
	 *
	 * @access public
	 */
	//--------------------------------------------
	function dirbrowser() {
		$response = $this->response;
		$iso_path = $response->html->request()->get('path');
		
		$appliance_id = $response->html->request()->get('appliance_id');
		$pool_id = $response->html->request()->get('volgroup');
		
		if ($iso_path !== '') {

			$command  = $this->controller->htvcenter->get('basedir')."/plugins/hyperv/bin/htvcenter-hyperv-vm post_file_list -i ".$this->resource->ip;
			$command .= ' --path '.$iso_path;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode fork';

			$file = $this->controller->htvcenter->get('webdir').'/plugins/hyperv/hyperv-stat/'.$this->resource->ip.'.pick_file_config';
			if($this->file->exists($file)) {
				$this->file->remove($file);
			}
			
			$htvcenter_server = new htvcenter_server();
		    $htvcenter_server->send_command($command, NULL, true);
			while (!$this->file->exists($file)) // check if the data file has been modified
			{
			  usleep(10000); // sleep 10ms to unload the CPU
			  clearstatcache();
			}
			$lines = $this->file->get_contents($file);
			$lines = explode("\n", $lines);

			if(is_array($lines) && count($lines) > 1) {
				$i = 0;
				foreach($lines as $c) {
					$tmp = explode('@', $c);
					if($tmp[0] === 'P') {
						$base = trim($tmp[1]);
					} else {
						if(isset($tmp[1]) && $tmp[1] !== '.') {
							$tmp[1] = trim($tmp[1]);
							$a  = $response->html->a();
							$a->label = $tmp[1];
							if($tmp[0] === 'L') {
								$path = $tmp[1].':/';
								$a->href = '#';
								$a->handler = 'onclick="filepicker.browse(\''.$path.'\'); return false;"';
								$a->css  = 'drive';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else if($tmp[0] === 'N') {
								$path = '/';
								$a->handler = 'onclick="filepicker.insert(\''.$base.'\'); return false;"';
								$a->href = '#';
								$a->css  = 'new';
								$a->label = 'Use as default base directory';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else if($tmp[0] === 'B') {
								$path = substr($base, 0, strpos($base,'/')).'/';
								$a->href = '#';
								$a->handler = 'onclick="filepicker.browse(\''.$path.'\'); return false;"';
								$a->css  = 'folder';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else if($tmp[0] === 'U') {
								$path = substr($base, 0, strrpos($base,'/'));
								if($path === '') {
									$path = '/';
								}
								$fullpath = str_replace(" ", "@", $path);
								$a->href = '#';
								$a->handler = 'onclick="filepicker.browse(\''.$fullpath.'\'); return false;"';
								$a->css  = 'folder';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else {
								if($base !== '/') {
									$tmp[1] = '/'.$tmp[1];
								}
								$fullpath = str_replace(" ", "@", $base.$tmp[1]);
								if($tmp[0] === 'F') {
									$a->handler = '';
									$a->href = '#';
									$a->css  = 'file';
								}
								if($tmp[0] === 'D') {
									$a->href = '#';
									$a->handler = 'onclick="filepicker.browse(\''.$fullpath.'\'); return false;"';
									$a->css  = 'folder';
								}
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							}
						}
					}
				}
			}
			$table = $response->html->tablebuilder('hyperv_vm_api', $response);

			$head['file']['hidden']   = true;
			$head['file']['sortable'] = false;
			$head['name']['title'] = 'Name';
			$head['name']['map'] = 'file';

			$table->max                       = count($body);
			$table->limit                       = count($body);
			$table->offset                     = 0;
			$table->order                     = 'ASC';
			$table->form_action           = $response->html->thisfile;
			$table->form_method         = 'GET';
			$table->css                        = 'filepicker_table';
			$table->border                   = 1;
			$table->id                          = 'Table';
			$table->head                     = $head;
			$table->body                     = $body;
//			$table->sort                       = 'file';
			$table->sort_form              = false;
			$table->sort_link                = false;
//			$table->autosort                = true;

			$table = $table->get_object();
			unset($table->__elements['pageturn_head']);
			unset($table->__elements[0]);

			echo $table->get_string();
		} else {
			echo 'no';
		}

	}
	
	
	
	
	//--------------------------------------------
	/**
	 * Filebrowser
	 *
	 * @access public
	 */
	//--------------------------------------------
	function filebrowser() {
		$response = $this->response;
		$iso_path = $response->html->request()->get('path');
		
		$appliance_id = $response->html->request()->get('appliance_id');
		$pool_id = $response->html->request()->get('volgroup');
		
		if ($iso_path !== '') {

			$command  = $this->controller->htvcenter->get('basedir')."/plugins/hyperv/bin/htvcenter-hyperv-vm post_file_list -i ".$this->resource->ip;
			$command .= ' --path '.$iso_path;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode fork';

			$file = $this->controller->htvcenter->get('webdir').'/plugins/hyperv/hyperv-stat/'.$this->resource->ip.'.pick_file_config';
			if($this->file->exists($file)) {
				$this->file->remove($file);
			}
			
			$htvcenter_server = new htvcenter_server();
		    $htvcenter_server->send_command($command, NULL, true);
			while (!$this->file->exists($file)) // check if the data file has been modified
			{
			  usleep(10000); // sleep 10ms to unload the CPU
			  clearstatcache();
			}
			$lines = $this->file->get_contents($file);
			$lines = explode("\n", $lines);

			if(is_array($lines) && count($lines) > 1) {
				$i = 0;
				foreach($lines as $c) {
					$tmp = explode('@', $c);
					if($tmp[0] === 'P') {
						$base = trim($tmp[1]);
					} else {
						if(isset($tmp[1]) && $tmp[1] !== '.') {
							$tmp[1] = trim($tmp[1]);
							$a  = $response->html->a();
							$a->label = $tmp[1];
							if($tmp[0] === 'L') {
								$path = $tmp[1].':/';
								$a->href = '#';
								$a->handler = 'onclick="filepicker.browse(\''.$path.'\'); return false;"';
								$a->css  = 'drive';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else if($tmp[0] === 'N') {
								$path = '/';
								$a->href = '/htvcenter/base/index.php?plugin=hyperv&controller=hyperv-ds&appliance_id='.$appliance_id.'&image_path='.$base.'&hyperv_ds_action=create';
								$a->label = 'Create new Virtual Disk here';
								$a->css  = 'new';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else if($tmp[0] === 'B') {
								$path = substr($base, 0, strpos($base,'/')).'/';
								$a->href = '#';
								$a->handler = 'onclick="filepicker.browse(\''.$path.'\'); return false;"';
								$a->css  = 'folder';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else if($tmp[0] === 'U') {
								$path = substr($base, 0, strrpos($base,'/'));
								if($path === '') {
									$path = '/';
								}
								$fullpath = str_replace(" ", "@", $path);
								$a->href = '#';
								$a->handler = 'onclick="filepicker.browse(\''.$fullpath.'\'); return false;"';
								$a->css  = 'folder';
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							} else {
								if($base !== '/') {
									$tmp[1] = '/'.$tmp[1];
								}
								$fullpath = str_replace(" ", "@", $base.$tmp[1]);
								if($tmp[0] === 'F') {
									$a->handler = 'onclick="filepicker.insert(\''.$fullpath.'\'); return false;"';
									$a->href = '#';
									$a->css  = 'file';
								}
								if($tmp[0] === 'D') {
									$a->href = '#';
									$a->handler = 'onclick="filepicker.browse(\''.$fullpath.'\'); return false;"';
									$a->css  = 'folder';
								}
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							}
						}
					}
				}
			}
			$table = $response->html->tablebuilder('hyperv_vm_api', $response);

			$head['file']['hidden']   = true;
			$head['file']['sortable'] = false;
			$head['name']['title'] = 'Name';
			$head['name']['map'] = 'file';

			$table->max                       = count($body);
			$table->limit                       = count($body);
			$table->offset                     = 0;
			$table->order                     = 'ASC';
			$table->form_action           = $response->html->thisfile;
			$table->form_method         = 'GET';
			$table->css                        = 'filepicker_table';
			$table->border                   = 1;
			$table->id                          = 'Table';
			$table->head                     = $head;
			$table->body                     = $body;
//			$table->sort                       = 'file';
			$table->sort_form              = false;
			$table->sort_link                = false;
//			$table->autosort                = true;

			$table = $table->get_object();
			unset($table->__elements['pageturn_head']);
			unset($table->__elements[0]);

			echo $table->get_string();
		} else {
			echo 'no';
		}

	}
	
	
	
	
	

	function filepicker() {
		$response = $this->response;
		$iso_path = $response->html->request()->get('path');
		if ($iso_path !== '') {

		    $command  = $this->controller->htvcenter->get('basedir')."/plugins/hyperv/bin/htvcenter-hyperv-vm post_iso_list -i ".$this->resource->ip;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode fork';
		    $file = $this->controller->htvcenter->get('webdir').'/plugins/hyperv/hyperv-stat/'.$this->resource->ip.'.pick_iso_config';
		    if($this->file->exists($file)) {
			    $this->file->remove($file);
		    }
			$htvcenter_server = new htvcenter_server();
		    $htvcenter_server->send_command($command, NULL, true);
		    while (!$this->file->exists($file)) // check if the data file has been modified
		    {
		      usleep(10000); // sleep 10ms to unload the CPU
		      clearstatcache();
		    }
			$lines = $this->file->get_contents($file);
			$lines = explode("\n", $lines);

			$body = array();
			if(is_array($lines) && count($lines) > 1) {
				$i = 0;
				foreach($lines as $c) {
					if($c !== '') {
						$a  = $response->html->a();
						$a->label = $c;
						$a->handler = 'onclick="filepicker.insert(\''.str_replace('\\', '/', trim($c)).'\'); return false;"';
						$a->href = '#';
						$a->css  = 'file';
						$body[$i]['file'] = $c;
						$body[$i]['name'] = $a->get_string();
						$i++;
					}
				}
			}
			$table = $response->html->tablebuilder('hyperv_api', $response);

			$head['file']['hidden']   = true;
			$head['file']['sortable'] = false;
			$head['name']['title'] = 'Name';
			$head['name']['map'] = 'file';

			$table->max                       = count($body);
			$table->limit                       = count($body);
			$table->offset                     = 0;
			$table->order                     = 'ASC';
			$table->form_action           = $response->html->thisfile;
			$table->form_method         = 'GET';
			$table->css                        = 'filepicker_table';
			$table->border                   = 1;
			$table->id                          = 'Table';
			$table->head                     = $head;
			$table->body                     = $body;
			$table->sort                       = 'file';
			$table->sort_form              = false;
			$table->sort_link                = false;
			$table->autosort                = true;

			$table = $table->get_object();
			unset($table->__elements['pageturn_head']);
			unset($table->__elements[0]);

			echo $table->get_string();
		} else {
		    echo 'no';
		}

	}


}
?>
