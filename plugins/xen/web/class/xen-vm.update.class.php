<?php
/**
 * xen-VM Update VM
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class xen_vm_update
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
		$this->file                     = $htvcenter->file();
		$this->htvcenter                  = $htvcenter;
		$this->user	    = $htvcenter->user();
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$vm = $this->response->html->request()->get('vm');
		if($vm === '') {
			return false;
		}
		$this->vm = $vm;
		$this->response->params['vm'] = $this->vm;
		$appliance = new appliance();
		$resource  = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.vm_list';
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
		$response = $this->update();
		if(isset($response->msg)) {
			sleep(2);
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/xen-vm-update.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm), 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_net_0'], 'lang_net_0');
		$t->add($this->lang['lang_net_1'], 'lang_net_1');
		$t->add($this->lang['lang_net_2'], 'lang_net_2');
		$t->add($this->lang['lang_net_3'], 'lang_net_3');
		$t->add($this->lang['lang_net_4'], 'lang_net_4');
		$t->add($this->lang['lang_boot'], 'lang_boot');
		$t->add($this->lang['lang_browse'], 'lang_browse');
		$t->add($this->lang['lang_browser'], 'lang_browser');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->actions_name, 'actions_name');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$this->reload();
		$response = $this->get_response();
		$form     = $response->form;

		// check vnc password
		$vnc = $form->get_request('vnc');
		if($vnc !== '' && $vnc !== $form->get_request('vnc_1')) {
			$form->set_error('vnc_1', $this->lang['error_vnc_password']);
		}
		if(isset($vnc) && $vnc !== '' && strlen($vnc) < 6) {
			$form->set_error('vnc', $this->lang['error_vnc_password_count']);
		}

		$iso_path = '';
		if($form->get_request('boot') !== '' && $form->get_request('boot') === 'iso') {
			if($form->get_request('iso_path') === '') {
				$form->set_error('iso_path', $this->lang['error_iso_path']);
			} else {
				$iso_path = ' -iso '.$form->get_request('iso_path');
			}
		}

		if(!$form->get_errors() && $this->response->submit()) {
			$errors = array();
			if($form->get_request('boot') === '') {
				$errors[] = $this->lang['error_boot'];
			}

			$enabled = array();
			for($i = 1; $i < 5; $i++) {			
				$enabled[$i] = true;
				if($form->get_request('net'.$i) !== '') {
					if($form->get_request('mac'.$i) === '') {
						$form->set_error('mac'.$i, $this->lang['error_mac']);
						$enabled[$i] = false;
					}
					if($form->get_request('bridge'.$i) === '') {
						$form->set_error('bridge'.$i, $this->lang['error_bridge']);
						$enabled[$i] = false;
					}
				} else {
					$enabled[$i] = false;
				}
			}

			if(count($errors) > 0 || $form->get_errors()) {
				$response->error = join('<br>', $errors);
			} else {
				$command  = $this->htvcenter->get('basedir').'/plugins/xen/bin/htvcenter-xen-vm update';
				$command .= ' -n '.$this->vm;
				$command .= ' -m '.strtolower($this->response->html->request()->get('mac'));
				$command .= ' -r '.$form->get_request('memory');
				$command .= ' -c '.$form->get_request('cpus');
				$command .= ' -z '.$form->get_request('bridge');

				foreach($enabled as $key => $value) {
					if($value === true) {
						$command .= ' -m'.($key).' '.$form->get_request('mac'.$key);
						$command .= ' -z'.($key).' '.$form->get_request('bridge'.$key);
					}
				}

				$command .= ' -b '.$form->get_request('boot');
				$command .= $iso_path;
				$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';

				$this->resource->send_command($this->resource->ip, $command);
				$response->msg = sprintf($this->lang['msg_updated'], $this->vm);
			}
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

		$cpus[] = array("1", "1 CPU");
		$cpus[] = array("2", "2 CPUs");
		$cpus[] = array("4", "4 CPUs");
		$cpus[] = array("8", "8 CPUs");
		$cpus[] = array("16", "16 CPUs");

		$ram[] = array("256", "256 MB");
		$ram[] = array("512", "512 MB");
		$ram[] = array("1024", "1 GB");
		$ram[] = array("2048", "2 GB");
		$ram[] = array("4096", "4 GB");
		$ram[] = array("8192", "8 GB");
		$ram[] = array("16384", "16 GB");
		$ram[] = array("32768", "32 GB");
		$ram[] = array("65536", "64 GB");

		$disk_select_arr[] = array('1024','1 GB');
		$disk_select_arr[] = array('2048','2 GB');
		$disk_select_arr[] = array('10240','10 GB');
		$disk_select_arr[] = array('20480','20 GB');
		$disk_select_arr[] = array('51200','50 GB');
		$disk_select_arr[] = array('102400','100 GB');

		$swap_select_arr[] = array('1024', '1 GB');
		$swap_select_arr[] = array('2048','2 GB');

		$file = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$this->resource->id.'.'.$this->vm.'.vm_config';
		$ini  = htvcenter_parse_conf($file);

		$file = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$this->resource->id.'.bridge_config';
		$data = htvcenter_parse_conf($file);
		$bridges = array();
		$bridge_list = $data['htvcenter_XEN_BRIDGES'];
		$bridge_list = rtrim($bridge_list, ":");
		$bridge_array = explode(':', $bridge_list);
		foreach ($bridge_array as $b) {
			$bridges[] = array($b, $b);
		}

		$this->response->params['mac'] = $ini['htvcenter_XEN_VM_MAC_1'];
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'update');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['static']                        = true;
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = $this->vm;
		$d['name']['object']['attrib']['disabled']  = true;

		$d['cpus']['label']                         = $this->lang['form_cpus'];
		$d['cpus']['required']                      = true;
		$d['cpus']['object']['type']                = 'htmlobject_select';
		$d['cpus']['object']['attrib']['name']      = 'cpus';
		$d['cpus']['object']['attrib']['index']     = array(0,1);
		$d['cpus']['object']['attrib']['options']   = $cpus;
		$d['cpus']['object']['attrib']['selected']  = array($ini['htvcenter_XEN_VM_CPU']);

		$d['memory']['label']                       = $this->lang['form_memory'];
		$d['memory']['required']                    = true;
		$d['memory']['object']['type']              = 'htmlobject_select';
		$d['memory']['object']['attrib']['name']    = 'memory';
		$d['memory']['object']['attrib']['index']   = array(0,1);
		$d['memory']['object']['attrib']['options'] = $ram;
		if($ini['htvcenter_XEN_VM_RAM'] !== '') {
			$d['memory']['object']['attrib']['selected'] = array($ini['htvcenter_XEN_VM_RAM']);
		}

		$vmtype = $this->response->html->request()->get('vmtype');
		if($vmtype === 'xen-vm-net') {
			$d['disk']['label'] = $this->lang['form_swap'];
			$d['disk']['object']['type']				= 'htmlobject_select';
			$d['disk']['object']['attrib']['index']		= array(0,1);
			$d['disk']['object']['attrib']['id']		= 'disk';
			$d['disk']['object']['attrib']['name']		= 'disk';
			$d['disk']['object']['attrib']['options']	= $swap_select_arr;

			$d['add_image']   = '';

		} else {
			$d['disk']['label'] = $this->lang['form_disk'];
			$d['disk']['object']['type']				= 'htmlobject_select';
			$d['disk']['object']['attrib']['index']		= array(0,1);
			$d['disk']['object']['attrib']['id']		= 'disk';
			$d['disk']['object']['attrib']['name']		= 'disk';
			$d['disk']['object']['attrib']['options']	= $disk_select_arr;

			$a = $this->response->html->a();
			$a->label   = $this->lang['form_add_volume'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'add';
			$a->href    = 'index.php?plugin=xen';
			$d['add_image']   = $a->get_string();
		}

		$d['mac']['label']                         = $this->lang['form_mac'];
		$d['mac']['static']                        = true;
		$d['mac']['object']['type']                = 'htmlobject_input';
		$d['mac']['object']['attrib']['name']      = 'dummy';
		$d['mac']['object']['attrib']['type']      = 'text';
		$d['mac']['object']['attrib']['value']     = $ini['htvcenter_XEN_VM_MAC_1'];
		$d['mac']['object']['attrib']['maxlength'] = 50;
		$d['mac']['object']['attrib']['disabled']  = true;

		$d['bridge']['label']                        = $this->lang['form_bridge'];
		$d['bridge']['required']                     = true;
		$d['bridge']['object']['type']               = 'htmlobject_select';
		$d['bridge']['object']['attrib']['name']     = 'bridge';
		$d['bridge']['object']['attrib']['index']    = array(0,1);
		$d['bridge']['object']['attrib']['options']  = $bridges;
		$d['bridge']['object']['attrib']['selected'] = array($ini['htvcenter_XEN_VM_NET_1']);

		$a = $this->response->html->a();
		$a->label   = $this->lang['form_add_networks'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = 'index.php?plugin=network-manager&appliance_id='.$this->appliance->id;
		$d['add_networks']   = $a->get_string();

		// net 1
		$checked = false;
		if($ini['htvcenter_XEN_VM_MAC_2'] !== '' ) {
			$mac = $ini['htvcenter_XEN_VM_MAC_2'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net1']['label']                       = $this->lang['form_enable'];
		$d['net1']['object']['type']              = 'htmlobject_input';
		$d['net1']['object']['attrib']['type']    = 'checkbox';
		$d['net1']['object']['attrib']['name']    = 'net1';
		$d['net1']['object']['attrib']['value']   = 'enabled';
		$d['net1']['object']['attrib']['checked'] = $checked;

		$d['mac1']['label']                         = $this->lang['form_mac'];
		$d['mac1']['object']['type']                = 'htmlobject_input';
		$d['mac1']['object']['attrib']['name']      = 'mac1';
		$d['mac1']['object']['attrib']['type']      = 'text';
		$d['mac1']['object']['attrib']['value']     = $mac;
		$d['mac1']['object']['attrib']['maxlength'] = 50;

		$d['bridge1']['label']                       = $this->lang['form_bridge'];
		$d['bridge1']['object']['type']              = 'htmlobject_select';
		$d['bridge1']['object']['attrib']['name']    = 'bridge1';
		$d['bridge1']['object']['attrib']['index']   = array(0,1);
		$d['bridge1']['object']['attrib']['options'] = $bridges;
		if(isset($ini['htvcenter_XEN_VM_NET_2']) && $checked === true) {
			$d['bridge1']['object']['attrib']['selected'] = array($ini['htvcenter_XEN_VM_NET_2']);
		}

		// net 2
		$checked = false;
		if($ini['htvcenter_XEN_VM_MAC_3'] !== '' ) {
			$mac = $ini['htvcenter_XEN_VM_MAC_3'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net2']['label']                       = $this->lang['form_enable'];
		$d['net2']['object']['type']              = 'htmlobject_input';
		$d['net2']['object']['attrib']['type']    = 'checkbox';
		$d['net2']['object']['attrib']['name']    = 'net2';
		$d['net2']['object']['attrib']['value']   = 'enabled';
		$d['net2']['object']['attrib']['checked'] = $checked;

		$d['mac2']['label']                         = $this->lang['form_mac'];
		$d['mac2']['object']['type']                = 'htmlobject_input';
		$d['mac2']['object']['attrib']['name']      = 'mac2';
		$d['mac2']['object']['attrib']['type']      = 'text';
		$d['mac2']['object']['attrib']['maxlength'] = 50;
		$d['mac2']['object']['attrib']['value']     = $mac;

		$d['bridge2']['label']                       = $this->lang['form_bridge'];
		$d['bridge2']['object']['type']              = 'htmlobject_select';
		$d['bridge2']['object']['attrib']['name']    = 'bridge2';
		$d['bridge2']['object']['attrib']['index']   = array(0,1);
		$d['bridge2']['object']['attrib']['options'] = $bridges;
		if(isset($ini['htvcenter_XEN_VM_NET_3']) && $checked === true) {
			$d['bridge2']['object']['attrib']['selected'] = array($ini['htvcenter_XEN_VM_NET_3']);
		}

		// net 3
		$checked = false;
		if($ini['htvcenter_XEN_VM_MAC_4'] !== '' ) {
			$mac = $ini['htvcenter_XEN_VM_MAC_4'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net3']['label']                       = $this->lang['form_enable'];
		$d['net3']['object']['type']              = 'htmlobject_input';
		$d['net3']['object']['attrib']['type']    = 'checkbox';
		$d['net3']['object']['attrib']['name']    = 'net3';
		$d['net3']['object']['attrib']['value']   = 'enabled';
		$d['net3']['object']['attrib']['checked'] = $checked;

		$d['mac3']['label']                         = $this->lang['form_mac'];
		$d['mac3']['object']['type']                = 'htmlobject_input';
		$d['mac3']['object']['attrib']['name']      = 'mac3';
		$d['mac3']['object']['attrib']['type']      = 'text';
		$d['mac3']['object']['attrib']['value']     = $mac;
		$d['mac3']['object']['attrib']['maxlength'] = 50;

		$d['bridge3']['label']                       = $this->lang['form_bridge'];
		$d['bridge3']['object']['type']              = 'htmlobject_select';
		$d['bridge3']['object']['attrib']['name']    = 'bridge3';
		$d['bridge3']['object']['attrib']['index']   = array(0,1);
		$d['bridge3']['object']['attrib']['options'] = $bridges;
		if(isset($ini['htvcenter_XEN_VM_NET_4']) && $checked === true) {
			$d['bridge3']['object']['attrib']['selected'] = array($ini['htvcenter_XEN_VM_NET_4']);
		}

		// net 4
		$checked = false;
		if($ini['htvcenter_XEN_VM_MAC_5'] !== '' ) {
			$mac = $ini['htvcenter_XEN_VM_MAC_5'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net4']['label']                       = $this->lang['form_enable'];
		$d['net4']['object']['type']              = 'htmlobject_input';
		$d['net4']['object']['attrib']['type']    = 'checkbox';
		$d['net4']['object']['attrib']['name']    = 'net4';
		$d['net4']['object']['attrib']['value']   = 'enabled';
		$d['net4']['object']['attrib']['checked'] = $checked;

		$d['mac4']['label']                         = $this->lang['form_mac'];
		$d['mac4']['object']['type']                = 'htmlobject_input';
		$d['mac4']['object']['attrib']['name']      = 'mac4';
		$d['mac4']['object']['attrib']['type']      = 'text';
		$d['mac4']['object']['attrib']['value']     = $mac;
		$d['mac4']['object']['attrib']['maxlength'] = 50;

		$d['bridge4']['label']                       = $this->lang['form_bridge'];
		$d['bridge4']['object']['type']              = 'htmlobject_select';
		$d['bridge4']['object']['attrib']['name']    = 'bridge4';
		$d['bridge4']['object']['attrib']['index']   = array(0,1);
		$d['bridge4']['object']['attrib']['options'] = $bridges;
		if(isset($ini['htvcenter_XEN_VM_NET_5']) && $checked === true) {
			$d['bridge4']['object']['attrib']['selected'] = array($ini['htvcenter_XEN_VM_NET_5']);
		}

		// boot from
		$d['boot_cd'] = '';
		$d['boot_iso'] = '';
		$d['boot_iso_path'] = '';
		$d['boot_local'] = '';
		$d['browse_button'] = '';
		if($vmtype !== 'xen-vm-net') {

			$d['boot_cd']['label']                     = $this->lang['form_boot_cd'];
			$d['boot_cd']['object']['type']            = 'htmlobject_input';
			$d['boot_cd']['object']['attrib']['type']  = 'radio';
			$d['boot_cd']['object']['attrib']['name']  = 'boot';
			$d['boot_cd']['object']['attrib']['value'] = 'cdrom';
			if($ini['htvcenter_XEN_VM_BOOT'] === 'cdrom') {
				$d['boot_cd']['object']['attrib']['checked'] = true;
			}

			$d['boot_iso']['label']                     = $this->lang['form_boot_iso'];
			$d['boot_iso']['object']['type']            = 'htmlobject_input';
			$d['boot_iso']['object']['attrib']['type']  = 'radio';
			$d['boot_iso']['object']['attrib']['id']    = 'boot_iso';
			$d['boot_iso']['object']['attrib']['name']  = 'boot';
			$d['boot_iso']['object']['attrib']['value'] = 'iso';
			if(strpos($ini['htvcenter_XEN_VM_BOOT'], 'iso') !== false) {
				$isopath = str_replace('iso:', '',$ini['htvcenter_XEN_VM_BOOT']);
				$d['boot_iso']['object']['attrib']['checked'] = true;
			}

			$d['boot_iso_path']['label']                    = $this->lang['form_iso_path'];
			$d['boot_iso_path']['object']['type']           = 'htmlobject_input';
			$d['boot_iso_path']['object']['attrib']['type'] = 'text';
			$d['boot_iso_path']['object']['attrib']['id']   = 'iso_path';
			$d['boot_iso_path']['object']['attrib']['name'] = 'iso_path';
			if(isset($isopath)) {
				$d['boot_iso_path']['object']['attrib']['value'] = $isopath;
			}

			$d['boot_net']['label']                     = $this->lang['form_boot_net'];
			$d['boot_net']['object']['type']            = 'htmlobject_input';
			$d['boot_net']['object']['attrib']['type']  = 'radio';
			$d['boot_net']['object']['attrib']['name']  = 'boot';
			$d['boot_net']['object']['attrib']['value'] = 'network';
			if($ini['htvcenter_XEN_VM_BOOT'] === 'network') {
				$d['boot_net']['object']['attrib']['checked'] = true;
			}

			$d['boot_local']['label']                     = $this->lang['form_boot_local'];
			$d['boot_local']['object']['type']            = 'htmlobject_input';
			$d['boot_local']['object']['attrib']['type']  = 'radio';
			$d['boot_local']['object']['attrib']['name']  = 'boot';
			$d['boot_local']['object']['attrib']['value'] = 'local';
			if($ini['htvcenter_XEN_VM_BOOT'] === 'local') {
				$d['boot_local']['object']['attrib']['checked'] = true;
			}

			$d['browse_button']['static']                      = true;
			$d['browse_button']['object']['type']              = 'htmlobject_input';
			$d['browse_button']['object']['attrib']['type']    = 'button';
			$d['browse_button']['object']['attrib']['name']    = 'browse_button';
			$d['browse_button']['object']['attrib']['id']      = 'browsebutton';
			$d['browse_button']['object']['attrib']['css']     = 'browse-button';
			$d['browse_button']['object']['attrib']['handler'] = 'onclick="filepicker.init(); return false;"';
			$d['browse_button']['object']['attrib']['style']   = "display:none;";
			$d['browse_button']['object']['attrib']['value']   = $this->lang['lang_browse'];

		} else {
			$d['boot_net']['label']                     = $this->lang['form_boot_net'];
			$d['boot_net']['object']['type']            = 'htmlobject_input';
			$d['boot_net']['object']['attrib']['type']  = 'radio';
			$d['boot_net']['object']['attrib']['name']  = 'boot';
			$d['boot_net']['object']['attrib']['value'] = 'network';
			$d['boot_net']['object']['attrib']['checked'] = true;
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Reload
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload() {
		$command  = $this->htvcenter->get('basedir').'/plugins/xen/bin/htvcenter-xen-vm post_vm_config';
		$command .=  ' -n '.$this->vm;
		$command .= ' -u '.$this->htvcenter->admin()->name.' -p '.$this->htvcenter->admin()->password;
		$id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$file = $this->htvcenter->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.'.$this->vm.'.vm_config';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->file->exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}

}
?>
