<?php
/**
 * Hyper-V Hosts Add DataStore
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hyperv_ds_add_pool
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hyperv_ds_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hyperv_ds_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hyperv_ds_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hyperv_ds_id';
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
		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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
		$appliance	= new appliance();
		$resource	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		require_once $this->rootdir.'/plugins/hyperv/class/hyperv-pool.class.php';
		$hyperv_pool = new hyperv_pool();
		$this->pool = $hyperv_pool;
		
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
		$response = $this->ds_add_pool();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hyperv-ds-add-pool.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['lang_browser'], 'lang_browser');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->response->html->request()->get('volgroup'), 'volgroup');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add('hyperv_vm_action', 'actions_name');
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds_add_pool() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name			= $form->get_request('name');
			$comment		= $form->get_request('comment');
			$path			= $form->get_request('path');
			
			if (!$this->pool->is_name_free($name)) {
				$error = sprintf($this->lang['error_exists'], $name);
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$hyperv_pool_fields["hyperv_pool_name"]=$name;
				$hyperv_pool_fields["hyperv_pool_comment"]=$comment;
				$hyperv_pool_fields["hyperv_pool_path"]=$path;
				$this->pool->add($hyperv_pool_fields);
				$response->msg = sprintf($this->lang['msg_added'], $name);
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
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add_pool');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 100;

		// derfault base dir
		$d['path'] = '';
		$d['browse_button'] = '';

		$d['path']['label']                    = $this->lang['form_path'];
		$d['path']['required']				    = true;
		$d['path']['object']['type']           = 'htmlobject_input';
		$d['path']['object']['attrib']['type'] = 'text';
		$d['path']['object']['attrib']['id']   = 'path';
		$d['path']['object']['attrib']['name'] = 'path';

		$d['browse_button']['static']                      = true;
		$d['browse_button']['object']['type']              = 'htmlobject_input';
		$d['browse_button']['object']['attrib']['type']    = 'button';
		$d['browse_button']['object']['attrib']['name']    = 'browse_button';
		$d['browse_button']['object']['attrib']['id']      = 'browsebutton';
		$d['browse_button']['object']['attrib']['css']     = 'browse-button';
		$d['browse_button']['object']['attrib']['handler'] = 'onclick="filepicker.init(); return false;"';
		$d['browse_button']['object']['attrib']['style']   = "display:none;";
		$d['browse_button']['object']['attrib']['value']   = $this->lang['lang_browse'];
		
		$d['comment']['label']							= $this->lang['form_comment'];
		$d['comment']['required']						= true;
//		$d['comment']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['comment']['validate']['errormsg']			= sprintf($this->lang['error_comment'], 'a-z0-9._-');
		$d['comment']['object']['type']					= 'htmlobject_input';
		$d['comment']['object']['attrib']['name']		= 'comment';
		$d['comment']['object']['attrib']['type']		= 'text';
		$d['comment']['object']['attrib']['value']		= '';
		$d['comment']['object']['attrib']['maxlength']	= 255;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}
?>
