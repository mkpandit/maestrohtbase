<?php
/**
 * Appliance Release
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class appliance_release
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
		$response = $this->release();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$t = $this->response->html->template($this->tpldir.'/appliance-release.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Release
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function release() {

		$response = $this->get_response();
		$id        = $this->response->html->request()->get('appliance_id');
		$form     = $response->form;
		$appliance = new appliance();
		if( $id !== '' ) {
		    $appliance = $appliance->get_instance_by_id($id);

			// TODO only appliances which are not active





		    // if resource != htvcenter
		    if ($appliance->resources == 0) {
				$errors[] = sprintf($this->lang['msg_htvcenter'], $id);
				continue;
		    }
		    $fields['appliance_resources'] = -1;
		    $appliance->update($id, $fields);
		    $form->remove($this->identifier_name.'['.$id.']');
		    $message[] = sprintf($this->lang['msg'], $id);

		    if(count($errors) === 0) {
			    $response->msg = join('<br>', $message);
		    } else {
			    $msg = array_merge($errors, $message);
			    $response->error = join('<br>', $msg);
		    }
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		// add $this->identifier_name to response
		$response->add($this->identifier_name.'[]', $response->html->request()->get($this->identifier_name));
		$form = $response->get_form($this->actions_name, 'release');
		$response->form = $form;
		return $response;
	}

}
?>
