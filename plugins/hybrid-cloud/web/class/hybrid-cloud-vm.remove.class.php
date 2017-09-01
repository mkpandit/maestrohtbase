<?php
/**
 *  Hybrid-cloud Instance remove
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hybrid_cloud_vm_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_vm_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_vm_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_vm_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_vm_tab';
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
		$this->id         = $this->response->html->request()->get('hybrid_cloud_id');
		$this->response->add('hybrid_cloud_id', $this->id);
		
		$this->instance_mac = $this->response->html->request()->get('instance_mac');
		$this->response->add('instance_mac', $this->instance_mac);
		$this->region     = $response->html->request()->get('region');
		require_once($this->htvcenter->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$hc = new hybrid_cloud();
		$hc->get_instance_by_id($this->id);
		$this->hc = $hc;
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
		$response = $this->remove();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-vm-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['tab'], 'tab');
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$response = $this->get_response();
		$instance_name  = $response->html->request()->get('instance_name');
		$form     = $response->form;

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['param_f']['label']                       = $instance_name;
		$d['param_f']['object']['type']              = 'htmlobject_input';
		$d['param_f']['object']['attrib']['type']    = 'checkbox';
		$d['param_f']['object']['attrib']['name']    = 'instance_name';
		$d['param_f']['object']['attrib']['value']   = $instance_name;
		$d['param_f']['object']['attrib']['checked'] = true;		
		$form->add($d);
	
		if(!$form->get_errors() && $response->submit()) {
			$errors = array();
			$message = array();
			$resource = new resource();
			$resource->get_instance_by_mac($this->instance_mac);
			$appliance    = new appliance();
			$appliances_using_resource = $appliance->get_ids_per_resource($resource->id);
			if (count($appliances_using_resource) > 0) {
				$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
				$errors[] = sprintf($this->lang['msg_vm_resource_still_in_use'], $instance_name, $resource->id, $appliances_using_resource_str);
			} else {

				$resource->remove($resource->id, $this->instance_mac);

				$hc_authentication = '';
				if (($this->hc->account_type == 'aws') || ($this->hc->account_type == 'euca')) {
					$hc_authentication .= ' -O '.$this->hc->access_key;
					$hc_authentication .= ' -W '.$this->hc->secret_key;
					$hc_authentication .= ' -ir '.$this->region;
				}
				if ($this->hc->account_type == 'lc-openstack') {
					$hc_authentication .= ' -u '.$this->hc->username;
					$hc_authentication .= ' -p '.$this->hc->password;
					$hc_authentication .= ' -q '.$this->hc->host;
					$hc_authentication .= ' -x '.$this->hc->port;
					$hc_authentication .= ' -g '.$this->hc->tenant;
					$hc_authentication .= ' -e '.$this->hc->endpoint;
				}
				if ($this->hc->account_type == 'lc-azure') {
					$hc_authentication .= ' -s '.$this->hc->subscription_id;
					$hc_keyfile = $this->hc->keyfile;
					$account_file_dir = $this->htvcenter->get('basedir').'/plugins/hybrid-cloud/etc/acl';
					$random_file_name  = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$filename = $account_file_dir."/".$random_file_name;
					file_put_contents($filename, $hc_keyfile);
					$hc_authentication .= ' -k '.$filename;
				}


				$command  = $this->htvcenter->get('basedir').'/plugins/hybrid-cloud/bin/htvcenter-hybrid-cloud-vm remove';
				$command .= ' -i '.$this->hc->id;
				$command .= ' -n '.$this->hc->account_name;
				$command .= ' -t '.$this->hc->account_type;
				$command .= ' -in '.$instance_name;
				$command .= $hc_authentication;
				$command .= ' --htvcenter-ui-user '.$this->user->name;
				$command .= ' --htvcenter-cmd-mode background';
				$htvcenter = new htvcenter_server();
				$htvcenter->send_command($command, NULL, true);
				$message[] = sprintf($this->lang['msg_removed'], $instance_name);
			}
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
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}
?>
