<?php
/**
 * vSphere Hosts Add ResourcePool
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_rp_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_rp_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_vsphere_rp_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_rp_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_vsphere_rp_id';
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
		$this->user = $htvcenter->user();
		$this->file = $this->htvcenter->file();
		$this->rootdir = $this->htvcenter->get('webdir');

		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
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
		$resourcepool = $this->response->html->request()->get('resourcepool');
		$this->resourcepool = $resourcepool;
		// set ENV
		$virtualization = new virtualization();
		$appliance	= new appliance();
		$resource	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_rp = $this->rootdir.'/plugins/vmware-vsphere/vmware-vsphere-stat/'.$resource->ip.'.rp_list';
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg).'&resourcepool='.$response->form->get_request('name')
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-vsphere-rp-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($this->lang['lang_cpu'], 'lang_cpu');
		$t->add($this->lang['lang_memory'], 'lang_memory');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Network add ResourcePool
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function add() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name     = $form->get_request('name');
			$parent   = $form->get_request('parent');
			
			
			$cpuexpandableReservation   = $form->get_request('cpuexpandableReservation');
			$cpulimit   = $form->get_request('cpulimit');
			$cpureservation   = $form->get_request('cpureservation');
			$cpushares   = $form->get_request('cpushares');
			$cpulevel   = $form->get_request('cpulevel');
			$memoryexpandableReservation   = $form->get_request('memoryexpandableReservation');
			$memorylimit   = $form->get_request('memorylimit');
			$memoryreservation   = $form->get_request('memoryreservation');
			$memoryshares   = $form->get_request('memoryshares');
			$memorylevel   = $form->get_request('memorylevel');
			
			$command  = $this->htvcenter->get('basedir')."/plugins/vmware-vsphere/bin/htvcenter-vmware-vsphere-rp create";
			$command .= " -i ".$this->resource->ip;
			$command .= " -n ".$name;
			$command .= " -q ".$parent;
			$command .= " --cpuexpandableReservation ".$cpuexpandableReservation;
			$command .= " --cpulimit ".$cpulimit;
			$command .= " --cpureservation ".$cpureservation;
			$command .= " --cpushares ".$cpushares;
			$command .= " --cpulevel ".$cpulevel;
			$command .= " --memoryexpandableReservation ".$memoryexpandableReservation;
			$command .= " --memorylimit ".$memorylimit;
			$command .= " --memoryreservation ".$memoryreservation;
			$command .= " --memoryshares ".$memoryshares;
			$command .= " --memorylevel ".$memorylevel;
			$command .= ' --htvcenter-ui-user '.$this->user->name;
			$command .= ' --htvcenter-cmd-mode background';

			if (file_exists($this->statfile_rp)) {
				$lines = explode("\n", file_get_contents($this->statfile_rp));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = $this->htvcenter->string_to_array($line, '|', '=');
							if($name === $line['name']) {
								$error = sprintf($this->lang['error_exists'], $name);
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_rp)) {
					unlink($this->statfile_rp);
				}

				// send command to add the nas
				$htvcenter_server = new htvcenter_server();
				$htvcenter_server->send_command($command, NULL, true);
				while (!file_exists($this->statfile_rp)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$response->msg = sprintf($this->lang['msg_added'], $name);
			}
		} 
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}



	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$parent_select_arr = array();
		if (file_exists($this->statfile_rp)) {
			$lines = explode("\n", file_get_contents($this->statfile_rp));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = $this->htvcenter->string_to_array($line, '|', '=');
						$parent_select_arr[] = array($line['name'], $line['name']);
					}
				}
			}
		}
		
		$expandable_select_arr[] = array('False', 'False');
		$expandable_select_arr[] = array('True', 'True');
		
		$level_select_arr[] = array('normal', 'normal');
		$level_select_arr[] = array('high', 'high');
		$level_select_arr[] = array('low', 'low');
		$level_select_arr[] = array('custom', 'custom');
		

		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 50;

		
		$d['parent']['label']							= $this->lang['form_parent'];
		$d['parent']['object']['type']					= 'htmlobject_select';
		$d['parent']['object']['attrib']['index']		= array(0,0);
		$d['parent']['object']['attrib']['id']			= 'parent';
		$d['parent']['object']['attrib']['name']		= 'parent';
		$d['parent']['object']['attrib']['options']		= $parent_select_arr;
		$d['parent']['object']['attrib']['selected']	= array($this->resourcepool);

		$d['cpuexpandableReservation']['label']							= $this->lang['form_cpuexpandableReservation'];
		$d['cpuexpandableReservation']['object']['type']				= 'htmlobject_select';
		$d['cpuexpandableReservation']['object']['attrib']['index']		= array(0,0);
		$d['cpuexpandableReservation']['object']['attrib']['id']		= 'cpuexpandableReservation';
		$d['cpuexpandableReservation']['object']['attrib']['name']		= 'cpuexpandableReservation';
		$d['cpuexpandableReservation']['object']['attrib']['options']	= $expandable_select_arr;
		$d['cpuexpandableReservation']['object']['attrib']['selected']	= array('False');
		
		$d['cpulimit']['label']							= $this->lang['form_cpulimit'];
		$d['cpulimit']['required']						= true;
		$d['cpulimit']['validate']['regex']				= '/^[0-9-]+$/i';
		$d['cpulimit']['validate']['errormsg']			= sprintf($this->lang['error_cpulimit'], '0-9-');
		$d['cpulimit']['object']['type']				= 'htmlobject_input';
		$d['cpulimit']['object']['attrib']['name']		= 'cpulimit';
		$d['cpulimit']['object']['attrib']['type']		= 'text';
		$d['cpulimit']['object']['attrib']['value']		= '-1';
		$d['cpulimit']['object']['attrib']['maxlength']	= 10;
		
		$d['cpureservation']['label']							= $this->lang['form_cpureservation'];
		$d['cpureservation']['required']						= true;
		$d['cpureservation']['validate']['regex']				= '/^[0-9-]+$/i';
		$d['cpureservation']['validate']['errormsg']			= sprintf($this->lang['error_cpureservation'], '0-9');
		$d['cpureservation']['object']['type']					= 'htmlobject_input';
		$d['cpureservation']['object']['attrib']['name']		= 'cpureservation';
		$d['cpureservation']['object']['attrib']['type']		= 'text';
		$d['cpureservation']['object']['attrib']['value']		= '0';
		$d['cpureservation']['object']['attrib']['maxlength']	= 10;
		
		$d['cpushares']['label']							= $this->lang['form_cpushares'];
		$d['cpushares']['required']							= true;
		$d['cpushares']['validate']['regex']				= '/^[0-9]+$/i';
		$d['cpushares']['validate']['errormsg']				= sprintf($this->lang['error_cpushares'], '0-9');
		$d['cpushares']['object']['type']					= 'htmlobject_input';
		$d['cpushares']['object']['attrib']['name']			= 'cpushares';
		$d['cpushares']['object']['attrib']['type']			= 'text';
		$d['cpushares']['object']['attrib']['value']		= '0';
		$d['cpushares']['object']['attrib']['maxlength']	= 10;
		
		$d['cpulevel']['label']							= $this->lang['form_cpulevel'];
		$d['cpulevel']['required']						= true;
		$d['cpulevel']['object']['type']				= 'htmlobject_select';
		$d['cpulevel']['object']['attrib']['index']		= array(0,0);
		$d['cpulevel']['object']['attrib']['id']		= 'cpulevel';
		$d['cpulevel']['object']['attrib']['name']		= 'cpulevel';
		$d['cpulevel']['object']['attrib']['options']	= $level_select_arr;
		$d['cpulevel']['object']['attrib']['selected']	= array('normal');
		
		
		$d['memoryexpandableReservation']['label']							= $this->lang['form_memoryexpandableReservation'];
		$d['memoryexpandableReservation']['object']['type']					= 'htmlobject_select';
		$d['memoryexpandableReservation']['object']['attrib']['index']		= array(0,0);
		$d['memoryexpandableReservation']['object']['attrib']['id']			= 'memoryexpandableReservation';
		$d['memoryexpandableReservation']['object']['attrib']['name']		= 'memoryexpandableReservation';
		$d['memoryexpandableReservation']['object']['attrib']['options']	= $expandable_select_arr;
		$d['memoryexpandableReservation']['object']['attrib']['selected']	= array('False');
		
		$d['memorylimit']['label']							= $this->lang['form_memorylimit'];
		$d['memorylimit']['required']						= true;
		$d['memorylimit']['validate']['regex']				= '/^[0-9-]+$/i';
		$d['memorylimit']['validate']['errormsg']			= sprintf($this->lang['error_memorylimit'], '0-9-');
		$d['memorylimit']['object']['type']					= 'htmlobject_input';
		$d['memorylimit']['object']['attrib']['name']		= 'memorylimit';
		$d['memorylimit']['object']['attrib']['type']		= 'text';
		$d['memorylimit']['object']['attrib']['value']		= '-1';
		$d['memorylimit']['object']['attrib']['maxlength']	= 10;
		
		$d['memoryreservation']['label']							= $this->lang['form_memoryreservation'];
		$d['memoryreservation']['required']							= true;
		$d['memoryreservation']['validate']['regex']				= '/^[0-9-]+$/i';
		$d['memoryreservation']['validate']['errormsg']				= sprintf($this->lang['error_memoryreservation'], '0-9');
		$d['memoryreservation']['object']['type']					= 'htmlobject_input';
		$d['memoryreservation']['object']['attrib']['name']			= 'memoryreservation';
		$d['memoryreservation']['object']['attrib']['type']			= 'text';
		$d['memoryreservation']['object']['attrib']['value']		= '0';
		$d['memoryreservation']['object']['attrib']['maxlength']	= 10;
		
		$d['memoryshares']['label']								= $this->lang['form_memoryshares'];
		$d['memoryshares']['required']							= true;
		$d['memoryshares']['validate']['regex']					= '/^[0-9]+$/i';
		$d['memoryshares']['validate']['errormsg']				= sprintf($this->lang['error_memoryshares'], '0-9');
		$d['memoryshares']['object']['type']					= 'htmlobject_input';
		$d['memoryshares']['object']['attrib']['name']			= 'memoryshares';
		$d['memoryshares']['object']['attrib']['type']			= 'text';
		$d['memoryshares']['object']['attrib']['value']			= '0';
		$d['memoryshares']['object']['attrib']['maxlength']		= 10;
		
		$d['memorylevel']['label']							= $this->lang['form_memorylevel'];
		$d['memorylevel']['required']						= true;
		$d['memorylevel']['object']['type']					= 'htmlobject_select';
		$d['memorylevel']['object']['attrib']['index']		= array(0,0);
		$d['memorylevel']['object']['attrib']['id']			= 'memorylevel';
		$d['memorylevel']['object']['attrib']['name']		= 'memorylevel';
		$d['memorylevel']['object']['attrib']['options']	= $level_select_arr;
		$d['memorylevel']['object']['attrib']['selected']	= array('normal');
		
		
		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}
	
}
?>
