<?php
/**
 * Appliance step2 (resource)
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class appliance_step2
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
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
		$this->response   = $response;
		$this->file       = $htvcenter->file();
		$this->htvcenter    = $htvcenter;
		$this->user       = $htvcenter->user();

		$wid = $this->response->html->request()->get('appliance_wizard_id');
		if($wid === '' && $this->response->html->request()->get('appliance_id') !== '') {
			$wid = $this->response->html->request()->get('appliance_id');
		}
		$this->apliance_wizard_id = $wid;
		$this->appliance  = $this->htvcenter->appliance($wid);
		$this->appliance->get_instance_by_id($wid);
		$this->response->add('appliance_wizard_id', $wid);
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'step3', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$a = $this->response->html->a();
		$a->title   = $this->lang['action_add'];
		$a->label   = $this->lang['action_add'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = $this->response->get_url($this->actions_name, 'load_radd').'&aplugin=resource&resource_action=add&appliance_id='.$this->apliance_wizard_id;

		$t = $this->response->html->template($this->tpldir.'/appliance-step2.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');

		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		//$t->add($this->lang['or'], 'or');
		$t->add($a, 'add');
		$t->add($this->lang['info'], 'info');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		$response  = $this->get_response();
		$form      = $response->form;
		$id        = $this->apliance_wizard_id;
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		if(!$form->get_errors() && $this->response->submit()) {
			$resource  = $form->get_request('resource');
			// special handling when htvcenter itself is the resource
			if ($resource == 0) {
				$fields['appliance_virtualization'] = 1;
			} else {
				// get resource type -> virtualization type
				$get_resource_type = new resource();
				$get_resource_type->get_instance_by_id($resource);
				// update appliance
				$fields['appliance_virtualization'] = $get_resource_type->vtype;
			}
			$fields['appliance_resources'] = $resource;
			$fields['appliance_wizard'] = 'wizard=step3,user='.$this->user->name;
			$appliance->update($id, $fields);
			// wizard
			$rs = $this->user->set_wizard($this->user->name, 'appliance', 3, $id);
			$response->msg = sprintf($this->lang['msg'], $resource, $appliance->name);
			// update long term event, remove old event and add new one
			$event = new event();
			$event_description_step1 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 1, $this->user->name);
			$event_description_step2 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 2, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step1, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 9, "add", $event_description_step2, "", "", 0, 0, 0);
		}


		$response->name = $appliance->name;

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
		$form = $response->get_form($this->actions_name, 'step2');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$resource  = new resource();
		$list      = $resource->get_list();
		$resources = array();
		$htmlrow = '<div id="resourceservadd"><div class="row">';
		foreach ($list as $value) {
			$id = $value['resource_id'];
			$resource->get_instance_by_id($id);
                        $virtualization_id = $resource->vtype;
                        if ($resource->id == 0) {
                            $virtualization_id = 1;
                        }
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($virtualization_id);
			$resources[] = array($id, ''.$resource->id.' / '.$resource->ip.' '.$resource->hostname.' ('.$virtualization->name.')');

			$icon = 'fa-plus';
			$colorbg = 'bg-info';

			if (preg_match('@Xen@', $virtualization->name) == true || preg_match('@xen@', $virtualization->name) == true ) {
				$icon = 'fa-close';
				$colorbg = 'bg-warning';
			}

			if ($virtualization->name == 'Physical System' ) {
				$icon = 'fa-server';
				$colorbg = 'bg-info';
			}

			if (preg_match('@Kvm@', $virtualization->name) == true || preg_match('@kvm@', $virtualization->name) == true ) {
				$icon = 'fa-keyboard-o';
				$colorbg = 'bg-danger';
			}

			if (preg_match('@VMware@', $virtualization->name) == true || preg_match('@vmware@', $virtualization->name) == true || preg_match('@VMware@', $virtualization->name) == true ) {
				$icon = 'fa-clone';
				$colorbg = 'bg-success';
			}

			if (preg_match('@Citrix@', $virtualization->name) == true || preg_match('@citrix@', $virtualization->name) == true ) {
				$icon = 'fa-sun-o';
				$colorbg = 'bg-info';
			}

			if (preg_match('@Hyper@', $virtualization->name) == true || preg_match('@hyper@', $virtualization->name) == true ) {
				$icon = 'fa-windows';
				$colorbg = 'bg-danger';
			}



			$virt = $virtualization->name;
			$virt = str_replace('KVM', 'OCH', $virt);
			$virt = str_replace('(localboot)', '', $virt);

			$htmlrow .= '<div class="panel media pad-all resadd" ids="'.$resource->id.'">
								<div class="media-left">
									<span class="icon-wrap icon-wrap-sm icon-circle '.$colorbg.'">
									<i class="fa '.$icon.' fa-2x"></i>
									</span>
								</div>
					
								<div class="media-body">
									<p class="text-2x mar-no text-thin">'.$virt.'</p>
									<p class="text-muted mar-no">'.$resource->id.' / '.$resource->ip.' '.$resource->hostname.'</p>
								</div>
							</div>';
		}
		$htmlrow .= '</div></div>';
		asort($resources);

		// handle appliance is new or edited
		$selected = $this->response->html->request()->get('resource_id');
		if($selected === '' && isset($this->appliance->resources)) {
			$selected = $this->appliance->resources;
		}

		
		$d['resource']['label']                        = $this->lang['form_resource'];
		$d['resource']['required']                     = true;
		$d['resource']['object']['type']               = 'htmlobject_select';
		$d['resource']['object']['attrib']['index']    = array(0, 1);
		$d['resource']['object']['attrib']['id']       = 'resource';
		$d['resource']['object']['attrib']['name']     = 'resource';
		$d['resource']['object']['attrib']['options']  = $resources;
		$d['resource']['object']['attrib']['selected'] = array($selected);
		

		$d['resourcecode'] = $htmlrow;
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
