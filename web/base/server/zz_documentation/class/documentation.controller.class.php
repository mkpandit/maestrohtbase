<?php
/**
 * Documentation Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class documentation_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'documentation_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "documentation_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'documentation_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'documentation_identifier';
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
	'main' => array (
		'tab' => 'Documentation',
		'label' => 'Documentation',
		'title' => 'htvcenter Documentation',
		'technical' => 'Technical Documentation',
		'technical_description' => 'Please find the Technical Documentation of htvcenter at:',
		'technical_url' => 'http://www.htvcenter-enterprise.com/news/details/article/in-depth-documentation-of-htvcenter-available.html',
		'howtos' => 'htvcenter Howtos & Use Cases',
		'howto1_title' => 'Setup your own htvcenter Cloud with KVM on Ubuntu',
		'howto1_url' => 'http://www.htvcenter-enterprise.com/news/details/article/howto-setup-your-own-htvcenter-cloud-with-kvm-on-ubuntu-lucid-lynx.html',
		'howto2_title' => 'Integrate Ubuntu Enterprise Cloud, Amazon EC2 and Eucalyptus with htvcenter',
		'howto2_url' => 'http://www.htvcenter-enterprise.com/news/details/article/integrate-ubuntu-enterprise-cloud-amazon-ec2-and-eucalyptus-with-htvcenter.html',
		'howto3_title' => 'Setup htvcenter Cloud deploying physical Windows Systems on CentOS',
		'howto3_url' => 'http://www.htvcenter-enterprise.com/news/details/article/howto-setup-htvcenter-cloud-deploying-physical-windows-systems-on-centos-55.html',
		'api' => 'htvcenter Cloud SOAP API Documentation',
		'api_description' => 'Please find the API of htvcenter at:',
		'api_url' => 'http://htvcenter-support.de/documentation/htvcenter-SOAP-API/',
		'please_wait' => 'Loading. Please wait ..',

	    
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
		$this->tpldir   = $this->rootdir.'/server/zz_documentation/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/zz_documentation/lang", 'documentation.ini');
//		$response->html->debug();

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
			$this->action = "main";
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'main':
				$content[] = $this->main(true);
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
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/server/documentation/class/documentation.api.class.php');
		$controller = new documentation_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Documentation Main
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function main( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/zz_documentation/class/documentation.main.class.php');
			$controller = new documentation_main($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['main'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['main']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'main' );
		$content['onclick'] = false;
		if($this->action === 'main'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
