<?php
/**
 * xen-vm ISO
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_vm_iso
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'xen_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'xen_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xen_vm_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
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
		$this->file       = $htvcenter->file();
		$this->htvcenter    = $htvcenter;
		$this->user	    = $htvcenter->user();
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$appliance = new appliance();
		$resource  = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.pick_iso_config';

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
//		$response = $this->iso();

		#$this->response->redirect(
		#	$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response)
		#);
//		echo $response;

		$this->iso();
	}

	//--------------------------------------------
	/**
	 * ISO
	 *
	 * @access public
	 * @return htmlobject_response
	 *
	 *
	 * for testing
	 * http://cloud/htvcenter/base/plugins/xen/index-vm.php?appliance_id=&xen_vm_action=iso&appliance_id=2&path=/tmp
	 *
	 */
	//--------------------------------------------
	function iso() {
		$response = $this->response;
		$iso_path = $response->html->request()->get('path');
		// TODO better validation
		if ($iso_path !== '') {

		    $command  = $this->htvcenter->get('basedir')."/plugins/xen/bin/htvcenter-xen-vm iso";
		    $command .= ' -q '.$iso_path;
		    $command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode background';

		    $file = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$this->resource->id.'.pick_iso_config';
		    if($this->file->exists($file)) {
			    $this->file->remove($file);
		    }
		    $this->resource->send_command($this->resource->ip, $command);
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
						$base = $tmp[1];
					} else {
						if($tmp[1] !== '.') {
							$a  = $response->html->a();
							$a->label = $tmp[1];
							if($tmp['1'] === '..') {
								if($base !== '/') {
									$path = substr($base, 0, strrpos($base,'/'));
									if($path === '') {
										$path = '/';
									}
									$a->href = $response->get_url($this->actions_name, 'iso' ).'&path='.$path;
									$a->css  = 'folder';
									$body[$i]['file'] = $c;
									$body[$i]['name'] = $a->get_string();
									$i++;
								}
							} else {
								if($tmp[0] === 'F') {
									$a->handler = 'onclick="alert(\''.$base.'/'.$tmp[1].'\');"';
									$a->css  = 'file';
								}
								if($tmp[0] === 'D') {
									$a->href = $response->get_url($this->actions_name, 'iso' ).'&path='.$base.'/'.$tmp['1'];
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


			$table = $response->html->tablebuilder('xen_vm_iso', $response);

			$head['file']['hidden']   = true;
			$head['file']['sortable'] = false;
			$head['name']['title'] = 'Name';
			$head['name']['map'] = 'file';

			$table->max                       = count($body);
			$table->limit                       = count($body);
			$table->offset                     = 0;
			$table->order                     = 'ASC';
			$table->form_action           = $html->thisfile;
			$table->form_method         = 'GET';
			$table->css                        = 'htmlobject_table';
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
