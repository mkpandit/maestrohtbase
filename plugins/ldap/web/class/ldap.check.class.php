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


class ldap_check
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ldap_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "ldap_msg";
var $lang;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param db $db
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response) {
		$this->response = $response;
		$this->htvcenter  = $htvcenter;
		require_once ($this->htvcenter->get('basedir').'/plugins/ldap/web/class/ldapconfig.class.php');
		$this->ldap = new ldapconfig();
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
		$str = '';

		// get ldap from db config
		$ldap_conf = $this->ldap;
		$ldap_conf->get_instance_by_id(1);
		$ldap_host = $ldap_conf->value;
		$ldap_conf->get_instance_by_id(2);
		$ldap_port = $ldap_conf->value;
		$ldap_conf->get_instance_by_id(3);
		$ldap_base_dn = $ldap_conf->value;
		$ldap_conf->get_instance_by_id(4);
		$ldap_admin = $ldap_conf->value;
		$ldap_conf->get_instance_by_id(5);
		$ldap_password = $ldap_conf->value;

		// get user data from ldap
		$filter = "(uid=*)";
		$connect = ldap_connect($ldap_host, $ldap_port);
		ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
		$bind = @ldap_bind($connect, $ldap_admin, $ldap_password);
		if($bind === true) {
			$read = ldap_search($connect, $ldap_base_dn, $filter);
			$info = ldap_get_entries($connect, $read);
			$ldap_user_count = $info["count"];
			if ($ldap_user_count > 0) {
				$str .=  sprintf($this->lang['msg_users'], $ldap_user_count).'<br>';
			} else {
				$str .=  $this->lang['error_users'].'<br>';
			}
			// here we get the htvcenter gid number from the ldap
			$dn = "cn=htvcenter,ou=Group,".$ldap_base_dn;
			$filter="(cn=*)";
			$justthese = array("gidNumber");
			$sr=ldap_read($connect, $dn, $filter, $justthese);
			$entry = ldap_get_entries($connect, $sr);
			// in the resulting array gidNumber is full lowercase -> gidnumber
			$htvcenter_gid = $entry[0]["gidnumber"][0];
			if (strlen($htvcenter_gid)) {
				$str .=  sprintf($this->lang['msg_gid'], $htvcenter_gid).'<br>';
			} else {
				$str .= $this->lang['error_gid'].'<br>';
			}
			ldap_close($connect);
		}
		else if ($bind === false) {
			$str =  $this->lang['error_bind'];
		}
		$this->response->redirect($this->response->get_url($this->actions_name, 'update', $this->message_param, $str));
	}

}
?>
