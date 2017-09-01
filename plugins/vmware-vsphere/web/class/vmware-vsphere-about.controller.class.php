<?php
/**
 * vmware-vsphere-about Controller
 *
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class vmware_vsphere_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_vsphere_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'vmware_vsphere_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_vsphere_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmware_vsphere_about_identifier';
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
var $lang = array(
	'documentation' => array (
		'tab' => 'About VMware VSPHERE',
		'label' => 'About VMware VSPHERE',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "VMware VSPHERE" plugin integrates VMware VSPHERE-Server.',

		'requirements_title' => 'Requirements',
		'requirements_list' => 'VMware vSphere Server integrated in HyperTask',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the vSphere 4,x, 5.0 and 5.1.',
		'provides_title' => 'Provides',
		'provides_list' => 'Virtualization types: "VMware-vSphere Host" and "VMware-vSphere VM"',
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
	),
	
	'usage' => array (
		'tab' => 'About VMware VSPHERE',
		'label' => 'VMware vSphere Use-Cases',
	),

	'vnc' => array (
		'tab' => 'VNC Access',
		'label' => 'VNC Access',
		'introduction_title' => 'VNC Access to the Virtual Machines on the VMware VSPHERE-Server',
		'introduction_content' => 'HyperTask provides VNC Console access directly in its Web UI to Virtual Machines on the VMware VSPHERE-Server via the <a href="/htvcenter/base/index.php?plugin=aa_plugins&plugin_filter=management">NoVNC Plugin</a>.',

		'requirements_title' => 'Requirements for the VNC Access',
		'requirements_list' => 'To enable VNC Access to the Virtual Machine console the Firewall on the vSphere Server needs to be adapted to allow the VNC connection.
			The method to enable VNC Access in the vSphere Firewall differ depending on the vSphere version.
			<br><br><br>
			<strong>vSphere 4.x</strong>
			<br><br>
			To enable VNC Access on an vSphere 4.x please login to the vSphere console and run:
			<br><br>
			<i>vspherecfg-firewall -e vncServer</i>
			<br><br>

			<br><br>
			<strong>vSphere 5.x</strong>
			<br><br>
			To enable VNC Access on an vSphere 5.x please login to the vSphere console and run:
			<br><br>
			<i>cp /etc/vmware/firewall/service.xml /etc/vmware/firewall/service.xml.bak</i>
			<br>
			<i>chmod 644 /etc/vmware/firewall/service.xml</i>
			<br>
			<i>chmod +t /etc/vmware/firewall/service.xml</i>
			<br><br>
			Then open the /etc/vmware/firewall/service.xml in a text editor.
			<br><br>
			<i>vi /etc/vmware/firewall/service.xml</i>
			<br><br>
			and add the following XML Configuration between the <ConfigRoot> node:
			<br><br>
			<pre>

  &lt;service>
   &lt;id>VNC&lt;/id>
    &lt;rule id="0000">
     &lt;direction>inbound&lt;/direction>
     &lt;protocol>tcp&lt;/protocol>
     &lt;porttype>dst&lt;/porttype>
     &lt;port>
     &lt;begin>5901&lt;/begin>
     &lt;end>6000&lt;/end>
     &lt;/port>
    &lt;/rule>
    &lt;rule id="0001">
     &lt;direction>outbound&lt;/direction>
     &lt;protocol>tcp&lt;/protocol>
     &lt;porttype>dst&lt;/porttype>
     &lt;port>
      &lt;begin>0&lt;/begin>
      &lt;end>65535&lt;/end>
     &lt;/port>
    &lt;/rule>
    &lt;enabled>true&lt;/enabled>
    &lt;required>false&lt;/required>
  &lt;/service>


			</pre>
			<br><br>
			To refresh the firewall ruleset please then run:
			<br><br>
			<i>vspherecli network firewall refresh</i>
			<br><br>
			To verify the additional VNC rule please run:
			<br><br>
			<i>vspherecli network firewall ruleset list</i>
			<br><br>
			',
	),

);

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
		$this->htvcenter  = $htvcenter;
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-vsphere/lang", 'vmware-vsphere-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/vmware-vsphere/tpl';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "documentation";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'documentation':
				$content[] = $this->documentation(true);
			break;
			case 'usage':
				$content[] = $this->usage(true);
			break;
			case 'vnc':
				$content[] = $this->vnc(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}


	//--------------------------------------------
	/**
	 * About VMware VSPHERE
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-about.documentation.class.php');
			$controller = new vmware_vsphere_about_documentation($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['documentation'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['documentation']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'documentation' );
		$content['onclick'] = false;
		if($this->action === 'documentation'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * About VMware vSphere Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-about.usage.class.php');
			$controller = new vmware_vsphere_about_usage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['usage'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['usage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'usage' );
		$content['onclick'] = false;
		if($this->action === 'usage'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * VNC VMware VSPHERE
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vnc( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-vsphere/class/vmware-vsphere-about.vnc.class.php');
			$controller = new vmware_vsphere_about_vnc($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['vnc'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['vnc']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vnc' );
		$content['onclick'] = false;
		if($this->action === 'vnc'){
			$content['active']  = true;
		}
		return $content;
	}



}
?>
