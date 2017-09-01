<?php

/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class ip_mgmt_delete
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ip_mgmt';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ip_mgmt_msg';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'ip_mgmt_id';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ip_mgmt_tab';
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
var $lang;



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
		$this->htvcenter = $htvcenter;
		$this->response = $response;
		$this->thisfile = $this->response->html->thisfile;
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		if ($this->response->html->request()->get($this->identifier_name) === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}
		$response = $this->delete();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}

		$t = $this->response->html->template($this->tpldir."/ip-mgmt-delete.tpl.php");
		$t->add($response->form->get_elements());
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($response->html->thisfile, "thisfile");
		$t->add($this->lang['label_delete'], 'label');
		$t->group_elements(array('param_' => 'form'));

		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function delete() {
		$response = $this->get_response();
		$form = $response->form;

		if(!$form->get_errors() && $this->response->submit()) {
			$request = $form->get_request($this->identifier_name);

			if(isset($request) && is_array($request)) {
				$errors  = array();
				$message = array();
				foreach($request as $key => $ipnet) {
					// check not in use
					$check = $this->ip_mgmt->get_list($ipnet);
					$check = array_shift($check);
					if(isset($check['first']['ip_mgmt_user_id']) && $check['first']['ip_mgmt_user_id'] !== '-1') {
						$errors[] = sprintf($this->lang['error_network_in_use'], $ipnet);
					} else {
						// DHCP
                                                $database = $this->ip_mgmt->get_instance('name', $ipnet);
                                                $vlan = $database["ip_mgmt_vlan_id"];
						// END DHCP
						$error = $this->ip_mgmt->remove_by_name($ipnet);
						if($error !== '') {
							$errors[] = $error;
						} else {
							$form->remove($this->identifier_name.'['.$key.']');
							
							// DHCP
							$dhcpdfile = file_get_contents("/usr/share/htvcenter/plugins/dhcpd/etc/dhcpd.conf");
							$dhcpdfile = preg_replace("/# start_vlan_$vlan.*?# end_vlan_$vlan/sm", "", $dhcpdfile);
							if (!empty($dhcpdfile)) {
								file_put_contents("/usr/share/htvcenter/plugins/dhcpd/etc/dhcpd.conf", $dhcpdfile);
							}
							$dhcpdvlanfile = file_get_contents("/usr/share/htvcenter/plugins/dhcpd/etc/htvcenter-plugin-dhcpd.conf");
                                        		$dhcpdvlanfile = str_replace("brvlan$vlan", "", $dhcpdvlanfile);
                                                        file_put_contents("/usr/share/htvcenter/plugins/dhcpd/etc/htvcenter-plugin-dhcpd.conf", $dhcpdvlanfile);
							$manageVlan = "sudo /usr/share/htvcenter/plugins/dhcpd/bin/perl/manageVlan.pl del $vlan";
							$htvcenter_server = new htvcenter_server();
							$htvcenter_server->send_command($manageVlan, NULL, true);
							// END DHCP
							
							$message[] = $this->lang['ip_mgmt_deleted']." ".$ipnet;
						}
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		}
		return $response;
	}


	function get_response() {
		$todelete = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'delete');
		$d        = array();
		if( $todelete !== '' ) {
			$i = 0;
			foreach($todelete as $folder) {
				$d['param_f'.$i]['label']                       = $folder;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['id']      = $this->identifier_name.'_'.$i;
				$d['param_f'.$i]['object']['attrib']['value']   = $folder;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
		}
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>
