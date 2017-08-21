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



class ip_mgmt_update
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
	function __construct($htvcenter, $response, $controller) {
		$this->htvcenter = $htvcenter;
		$this->response = $response;
		$this->thisfile = $this->response->html->thisfile;
		$this->controller = $controller;
		$this->ip_mgmt = $this->controller->ip_mgmt;
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
		$response = $this->update();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}

		$t = $response->html->template($this->tpldir."/ip-mgmt-update.tpl.php");
		$t->add($response->form->get_elements());
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['label_update'], 'label');
		$t->add($response->html->thisfile, "thisfile");
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {

			$data = $form->get_request();
			$network_name = $response->html->request()->get($this->identifier_name);

			if($network_name !== '') {
				$check = $this->ip_mgmt->get_ips_by_name($network_name);
				if(!is_array($check)) {
					$form->set_error("ip_mgmt_name", $this->lang['ip_mgmt_name_invalid']);
				}
			} else {
				$form->set_error("ip_mgmt_name", $this->lang['ip_mgmt_name_invalid']);
			}

			$net1 = $form->get_request('ip_mgmt_network_1');
			$mask = $form->get_request('ip_mgmt_subnet');
			if(isset($net1) && isset($mask) && !$form->get_errors()) {

				$ips = $this->controller->__get_ips($net1, $mask, $network_name);
				if(isset($ips['error'])) {
					foreach($ips['error'] as $k => $v) {
						$form->set_error($k, $v);
					}
				} else {
					$data = $form->get_request();
					$data['ip_mgmt_name'] = $network_name;
					if(!isset($data['ip_mgmt_broadcast'])) {
						$data['ip_mgmt_broadcast'] = $this->ip_mgmt->broadcast($net1, $mask);
					}
					$data['ip_mgmt_network'] = $this->ip_mgmt->network($net1, $mask);

					// here we are ready to update, update = remove + add
					$this->ip_mgmt->remove_by_name($data['ip_mgmt_name']);

					foreach($ips as $k => $ip) {
						if($k === 1) { unset($data['ip_mgmt_comment']); }
						$data['ip_mgmt_address'] = $ip;
						$dberror = $this->ip_mgmt->add($data);
						// check for db errors
					}

					// DHCP
                                        $subnetArray = explode('.',$data['ip_mgmt_subnet']);
                                        $netArray = explode('.',$data['ip_mgmt_network_1']);
                                        $subnet  = ((int)$subnetArray[0] & (int)$netArray[0]);
                                        $subnet .= '.'.((int)$subnetArray[1] & (int)$netArray[1]);
                                        $subnet .= '.'.((int)$subnetArray[2] & (int)$netArray[2]);
                                        $subnet .= '.'.((int)$subnetArray[3] & (int)$netArray[3]);

                                        $vlan = $data['ip_mgmt_vlan_id'];
                                        $gateway = $data['ip_mgmt_gateway'];
                                        $mask = $data['ip_mgmt_subnet'];
                                        $dns1 = $data['ip_mgmt_dns1'];
                                        $dns2 = $data['ip_mgmt_dns2'];;
                                        $net1 = $data['ip_mgmt_network_1'];
                                        $net2 = $data['ip_mgmt_network_2'];
                                        $dhcpdfile = file_get_contents("/usr/share/htvcenter/plugins/dhcpd/etc/dhcpd.conf");
                                        $dhcpdfile = preg_replace("/# start_vlan_$vlan.*?# end_vlan_$vlan/sm", "", $dhcpdfile);
                                        file_put_contents("/usr/share/htvcenter/plugins/dhcpd/etc/dhcpd.conf", $dhcpdfile.PHP_EOL);
                                        $group = "# start_vlan_$vlan\n";
                                        $group .= "group {\n";
                                        $group .= "\tdefault-lease-time 600;\n";
                                        $group .= "\tmax-lease-time 7200;\n";
                                        $group .= "\toption routers $gateway;\n";
                                        $group .= "\toption subnet-mask $mask;\n";
                                        $group .= "\toption domain-name-servers $dns1";
                                        if(!$dns2 == ""){
                                              $group .= ", $dns2";
                                        }
                                        $group .= ";\n";
                                        $group .= "\n";
                                        $group .= "\tsubnet $subnet netmask $mask {\n";
                                        $group .= "\t\trange $net1 $net2;\n";
                                        $group .= "\t}\n";
                                        $group .= "\n";
                                        $group .= "}\n";
                                        $group .= "# end_vlan_$vlan\n";
                                        file_put_contents("/usr/share/htvcenter/plugins/dhcpd/etc/dhcpd.conf", $group, FILE_APPEND);
                                        #$dhcpdvlanfile = file_get_contents("/usr/share/htvcenter/plugins/dhcpd/etc/htvcenter-plugin-dhcpd.conf");
                                        #$dhcpdvlanfile = preg_replace("/htvcenter_PLUGIN_DHCPD_INTERFACES=\"(.*?)\"/sm", "htvcenter_PLUGIN_DHCPD_INTERFACES=\"$1 brvlan$vlan\"", $dhcpdvlanfile);
                                        #file_put_contents("/usr/share/htvcenter/plugins/dhcpd/etc/htvcenter-plugin-dhcpd.conf", $dhcpdvlanfile.PHP_EOL);
                                        $manageVlan = "sudo /usr/share/htvcenter/plugins/dhcpd/bin/perl/manageVlan.pl update $vlan $net2 $mask";
					$htvcenter_server = new htvcenter_server();
                                        $htvcenter_server->send_command($manageVlan, NULL, true);
					// END DHCP

					// success msg
					$response->msg = $this->lang['ip_mgmt_update_successful'];
				}
			}
		}
		return $response;
	}


	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {

		$net_name = $this->response->html->request()->get($this->identifier_name);
		$form_data = $this->ip_mgmt->get_list($net_name);

		$ip_mgmt_network_1 = $form_data[$net_name]['first']['ip_mgmt_address'];
		$ip_mgmt_network_2 = $form_data[$net_name]['last']['ip_mgmt_address'];

		$form_data = $form_data[$net_name]['first'];
		$form_data['first']['ip_mgmt_network_1'] = $ip_mgmt_network_1;
		$form_data['first']['ip_mgmt_network_1'] = $ip_mgmt_network_2;

		$ip_mgmt_subnet = $form_data['ip_mgmt_subnet'];
		$ip_mgmt_broadcast = $form_data['ip_mgmt_broadcast'];
		$ip_mgmt_gateway = $form_data['ip_mgmt_gateway'];
		$ip_mgmt_dns1 = $form_data['ip_mgmt_dns1'];
		$ip_mgmt_dns2 = $form_data['ip_mgmt_dns2'];
		$ip_mgmt_domain = $form_data['ip_mgmt_domain'];
		$ip_mgmt_vlan_id = $form_data['ip_mgmt_vlan_id'];
		$ip_mgmt_comment = $form_data['ip_mgmt_comment'];

		$response = $this->response;
		$form = $response->get_form($this->actions_name, "update");

		// disabled for now
		//$regex_ip  = '~^([1-9]|[0-9][0-9]|[1][0-9][0-9]|[2][0-5][0-4])';
		//$regex_ip .= '\\.([0-9]|[0-9][0-9]|[1][0-9][0-9]|[2][0-5][0-5])';
		//$regex_ip .= '\\.([0-9]|[0-9][0-9]|[1][0-9][0-9]|[2][0-5][0-5])';
		//$regex_ip .= '\\.([1-9]|[0-9][0-9]|[1][0-9][0-9]|[2][0-5][0-4])+$~i';
		$regex_ip = '';
		
		$d = array();

		/*
		#$d['ip_mgmt_id']['label']                     = 'ID';
		$d['ip_mgmt_id']['validate']['regex']         = '/^[0-9]$/i';
		$d['ip_mgmt_id']['validate']['errormsg']      = 'ID must be a number';
		$d['ip_mgmt_id']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_id']['object']['attrib']['type']  = 'hidden';
		$d['ip_mgmt_id']['object']['attrib']['id']    = 'ip_mgmt_id';
		$d['ip_mgmt_id']['object']['attrib']['name']  = 'ip_mgmt_id';
		*/

		$d['ip_mgmt_name']['label']                     = $this->lang['ip_mgmt_name'];
		$d['ip_mgmt_name']['required']                  = false;
		$d['ip_mgmt_name']['static']                    = true;
		$d['ip_mgmt_name']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['ip_mgmt_name']['validate']['errormsg']      = 'Name must be [a-z] only';
		$d['ip_mgmt_name']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_name']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_name']['object']['attrib']['disabled']  = true;
		$d['ip_mgmt_name']['object']['attrib']['id']    = 'ip_mgmt_name';
		$d['ip_mgmt_name']['object']['attrib']['name']  = 'ip_mgmt_name';
		$d['ip_mgmt_name']['object']['attrib']['value']  = $net_name;

		/*
		$d['ip_mgmt_user_id']['validate']['regex']         = '/[0-9]/i';
		$d['ip_mgmt_user_id']['validate']['errormsg']      = 'User ID must be a number';
		$d['ip_mgmt_user_id']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_user_id']['object']['attrib']['type']  = 'hidden';
		$d['ip_mgmt_user_id']['object']['attrib']['id']    = 'ip_mgmt_user_id';
		$d['ip_mgmt_user_id']['object']['attrib']['name']  = 'ip_mgmt_user_id';

		$d['ip_mgmt_appliance_id']['validate']['regex']         = '/[0-9]/i';
		$d['ip_mgmt_appliance_id']['validate']['errormsg']      = 'User ID must be a number';
		$d['ip_mgmt_appliance_id']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_appliance_id']['object']['attrib']['type']  = 'hidden';
		$d['ip_mgmt_appliance_id']['object']['attrib']['id']    = 'ip_mgmt_appliance_id';
		$d['ip_mgmt_appliance_id']['object']['attrib']['name']  = 'ip_mgmt_appliance_id';

		$d['ip_mgmt_state']['validate']['regex']         = '/[0-9]/i';
		$d['ip_mgmt_state']['validate']['errormsg']      = 'State must be a number';
		$d['ip_mgmt_state']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_state']['object']['attrib']['type']  = 'hidden';
		$d['ip_mgmt_state']['object']['attrib']['id']    = 'ip_mgmt_state';
		$d['ip_mgmt_state']['object']['attrib']['name']  = 'ip_mgmt_state';
		*/

		$d['ip_mgmt_network_1']['label']                     = $this->lang['ip_mgmt_network_1'];
		$d['ip_mgmt_network_1']['required']                  = true;
		$d['ip_mgmt_network_1']['validate']['regex']         = $regex_ip;
		$d['ip_mgmt_network_1']['validate']['errormsg']      = $d['ip_mgmt_network_1']['label'].' must be 0.0.0.0';
		$d['ip_mgmt_network_1']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_network_1']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_network_1']['object']['attrib']['id']    = 'ip_mgmt_network_1';
		$d['ip_mgmt_network_1']['object']['attrib']['name']  = 'ip_mgmt_network_1';
		$d['ip_mgmt_network_1']['object']['attrib']['title'] = 'Insert an IP adress e.g. 192.168.0.1';
		$d['ip_mgmt_network_1']['object']['attrib']['value']  = $ip_mgmt_network_1;

		$d['ip_mgmt_network_2']['label']                     = $this->lang['ip_mgmt_network_2'];
		$d['ip_mgmt_network_2']['validate']['regex']         = $regex_ip;
		$d['ip_mgmt_network_2']['validate']['errormsg']      = $d['ip_mgmt_network_2']['label'].' must be 0.0.0.0';
		$d['ip_mgmt_network_2']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_network_2']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_network_2']['object']['attrib']['id']    = 'ip_mgmt_network_2';
		$d['ip_mgmt_network_2']['object']['attrib']['name']  = 'ip_mgmt_network_2';
		$d['ip_mgmt_network_2']['object']['attrib']['title'] = 'Insert an IP adress e.g. 192.168.0.1';
		$d['ip_mgmt_network_2']['object']['attrib']['value']  = $ip_mgmt_network_2;

		/*
		$d['ip_mgmt_address']['label']                     = 'Adress';
		$d['ip_mgmt_address']['validate']['regex']         = $regex_ip;
		$d['ip_mgmt_address']['validate']['errormsg']      = 'Adress must be 0.0.0.0';
		$d['ip_mgmt_address']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_address']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_address']['object']['attrib']['id']    = 'ip_mgmt_address';
		$d['ip_mgmt_address']['object']['attrib']['name']  = 'ip_mgmt_address';
		*/

		$d['ip_mgmt_subnet']['label']                     = 'Netmask';
		$d['ip_mgmt_subnet']['required']                  = true;
		#$d['ip_mgmt_subnet']['validate']['regex']         = $regex_ip;
		#$d['ip_mgmt_subnet']['validate']['errormsg']      = 'Subnet must be 0.0.0.0';
		$d['ip_mgmt_subnet']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_subnet']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_subnet']['object']['attrib']['id']    = 'ip_mgmt_subnet';
		$d['ip_mgmt_subnet']['object']['attrib']['name']  = 'ip_mgmt_subnet';
		$d['ip_mgmt_subnet']['object']['attrib']['value']  = $ip_mgmt_subnet;

		$d['ip_mgmt_broadcast'] = '';
		#$d['ip_mgmt_broadcast']['label']                     = $this->lang['ip_mgmt_broadcast'];
		#$d['ip_mgmt_subnet']['validate']['regex']         = $regex_ip;
		#$d['ip_mgmt_subnet']['validate']['errormsg']      = 'Subnet must be 0.0.0.0';
		#$d['ip_mgmt_broadcast']['object']['type']            = 'htmlobject_input';
		#$d['ip_mgmt_broadcast']['object']['attrib']['type']  = 'text';
		#$d['ip_mgmt_broadcast']['object']['attrib']['id']    = 'ip_mgmt_broadcast';
		#$d['ip_mgmt_broadcast']['object']['attrib']['name']  = 'ip_mgmt_broadcast';
		#$d['ip_mgmt_broadcast']['object']['attrib']['value']  = $ip_mgmt_broadcast;

		$d['ip_mgmt_gateway']['label']                     = $this->lang['ip_mgmt_gateway'];
		$d['ip_mgmt_gateway']['validate']['regex']         = $regex_ip;
		$d['ip_mgmt_gateway']['validate']['errormsg']      = 'Gateway must be 0.0.0.0';
		$d['ip_mgmt_gateway']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_gateway']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_gateway']['object']['attrib']['id']    = 'ip_mgmt_gateway';
		$d['ip_mgmt_gateway']['object']['attrib']['name']  = 'ip_mgmt_gateway';
		$d['ip_mgmt_gateway']['object']['attrib']['value']  = $ip_mgmt_gateway;

		$d['ip_mgmt_dns1']['label']                     = $this->lang['ip_mgmt_dns1'];
		$d['ip_mgmt_dns1']['validate']['regex']         = $regex_ip;
		$d['ip_mgmt_dns1']['validate']['errormsg']      = 'DNS 1 must be 0.0.0.0';
		$d['ip_mgmt_dns1']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_dns1']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_dns1']['object']['attrib']['id']    = 'ip_mgmt_dns1';
		$d['ip_mgmt_dns1']['object']['attrib']['name']  = 'ip_mgmt_dns1';
		$d['ip_mgmt_dns1']['object']['attrib']['value']  = $ip_mgmt_dns1;

		$d['ip_mgmt_dns2']['label']                     = $this->lang['ip_mgmt_dns2'];
		$d['ip_mgmt_dns2']['validate']['regex']         = $regex_ip;
		$d['ip_mgmt_dns2']['validate']['errormsg']      = 'DNS 2 must be 0.0.0.0';
		$d['ip_mgmt_dns2']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_dns2']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_dns2']['object']['attrib']['id']    = 'ip_mgmt_dns2';
		$d['ip_mgmt_dns2']['object']['attrib']['name']  = 'ip_mgmt_dns2';
		$d['ip_mgmt_dns2']['object']['attrib']['value']  = $ip_mgmt_dns2;

		$d['ip_mgmt_domain']['label']                     = $this->lang['ip_mgmt_domain'];
		$d['ip_mgmt_domain']['validate']['regex']         = '/^[a-z-\.]+$/i';
		$d['ip_mgmt_domain']['validate']['errormsg']      = 'Domain must be a-z and -';
		$d['ip_mgmt_domain']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_domain']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_domain']['object']['attrib']['id']    = 'ip_mgmt_domain';
		$d['ip_mgmt_domain']['object']['attrib']['name']  = 'ip_mgmt_domain';
		$d['ip_mgmt_domain']['object']['attrib']['value']  = $ip_mgmt_domain;
		$d['ip_mgmt_domain']['object']['attrib']['maxlength'] = 255;

		$d['ip_mgmt_vlan_id']['label']                     = $this->lang['ip_mgmt_vlan_id'];
		$d['ip_mgmt_vlan_id']['validate']['regex']         = '/^[0-9]+$/i';
		$d['ip_mgmt_vlan_id']['validate']['errormsg']           = 'VLan ID must be a number';
		$d['ip_mgmt_vlan_id']['object']['type']            = 'htmlobject_input';
		$d['ip_mgmt_vlan_id']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_vlan_id']['object']['attrib']['id']    = 'ip_mgmt_vlan_id';
		$d['ip_mgmt_vlan_id']['object']['attrib']['name']  = 'ip_mgmt_vlan_id';
		$d['ip_mgmt_vlan_id']['object']['attrib']['value']  = $ip_mgmt_vlan_id;

		$d['ip_mgmt_comment']['label']                     = $this->lang['ip_mgmt_comment'];
		#$d['ip_mgmt_comment']['validate']['regex']        = '/^[0-9]$/i';
		#$d['ip_mgmt_comment']['validate']['errormsg']          = 'VLan 1 must be a number';
		$d['ip_mgmt_comment']['object']['type']            = 'htmlobject_textarea';
		$d['ip_mgmt_comment']['object']['attrib']['type']  = 'text';
		$d['ip_mgmt_comment']['object']['attrib']['id']    = 'ip_mgmt_comment';
		$d['ip_mgmt_comment']['object']['attrib']['name']  = 'ip_mgmt_comment';
		$d['ip_mgmt_comment']['object']['attrib']['value']  = $ip_mgmt_comment;
		$d['ip_mgmt_comment']['object']['attrib']['maxlength'] = 255;

		if(isset($data)) {

			$d['fake']['label']                        = $this->lang['ip_mgmt_name'];
			$d['fake']['object']['type']               = 'htmlobject_input';
			$d['fake']['object']['attrib']['type']     = 'text';
			$d['fake']['object']['attrib']['name']     = $d['ip_mgmt_name']['object']['attrib']['name'];
			$d['fake']['object']['attrib']['disabled'] = true;

			$d['ip_mgmt_name']['label']                    = '';
			$d['ip_mgmt_name']['object']['attrib']['type'] = 'hidden';


			foreach($data as $key => $value) {

				foreach($d as $fkey => $form) {
					if($form['object']['attrib']['name'] === $key){
						$d[$fkey]['object']['attrib']['value'] = $value;
					}
				}
			}
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}





/*
	function get_ips($first, $last) {
		$n1 = explode('.', $first);
		$n2 = explode('.', $last);

		foreach($n1 as $key => $value) {
			if($value < $n2[$key]) {
				$index = $key;
				break;
			}
		}

		$base = '';
		for($i = 0; $i < $index; $i++) {

			$base .= $n2[$i].'.';

		}

		$ips = array();

		if($index === 3) {
			for($i = $n1[3]; $i <= $n2[3]; $i++) {
				$ips[] = $base.$i;
			}
		}
		if($index === 2) {
			for($i = $n1[2]; $i <= $n2[2]; $i++) {
				for($j = 0; $j < 255; $j++) {
					#echo $i.'<br>';
					if($i === $n1[2] && $j > $n1[3]) {
						$ips[] = $base.$i.'.'.$j;
					}
					else if($i !== $n2[1] && $i < $n2[2]) {
						$ips[] = $base.$i.'.'.$j.'.x';
					}
					else if($i !== $n1[2] &&  $j <= $n2[3]) {
				#echo 'x';
						$ips[] = $base.$i.'.'.$j.'.y';
					}
				}
			}
		}
		return $ips;
	}
*/



}
?>
