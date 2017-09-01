<?php
/**
 * Resource Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class cloudprice {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'resource_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'resource_identifier';
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
		$this->file     = $htvcenter->file();
		$this->htvcenter  = $htvcenter;
		$this->rootdir  = $this->htvcenter->get('webdir');
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/cloud-price.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($data->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {
		$d = array();
		
		$cloud_price_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/awspriceparsing.py');
		$cloud_price = json_decode($cloud_price_dump, true);

		$memory = array();
		$operatingSystem = array();
		$vcpu = array();
		
		foreach($cloud_price as $k => $v){
			if(!in_array($v['memory'], $memory)){
				$memory[] = array($v['memory'], $v['memory']);
			}
			if(!in_array($v['operatingSystem'], $operatingSystem)){
				$operatingSystem[] = array($v['operatingSystem'], $v['operatingSystem']);
			}
			if(!in_array($v['vcpu'], $vcpu)){
				$vcpu[] = array($v['vcpu'], $v['vcpu']);
			}
		}
		
		sort($processorArchitecture);
		sort($memory);
		sort($operatingSystem);
		sort($vcpu);
		sort($clockSpeed);
		
		$html_information = "";
		$html_information .= "<div class='multi-cloud'>";
		$html_information .= "<div class='cloud-spin'><span class='icon-wrap icon-wrap-lg icon-circle'><i class='fa fa fa-spinner fa-spin fa-4x'></i></span><p>Loading cloud prices ...</p></div>";
		$html_information .= "</div>";
		
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'cloudprice');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="getCloudPrice(); return false;"';
		$submit->value = 'Get Cloud Price';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$d['memory']['label']                            				= $this->lang['memory'];
		$d['memory']['object']['type']                   				= 'htmlobject_select';
		$d['memory']['object']['attrib']['index']   	   				= array(0, 1);
		$d['memory']['object']['attrib']['name']         				= 'memory';
		$d['memory']['object']['attrib']['id']           				= 'memory';
		$d['memory']['object']['attrib']['type']         				= 'text';
		$d['memory']['object']['attrib']['options']      				= $memory;
		
		$d['operating_system']['label']                            		= $this->lang['operating_system'];
		$d['operating_system']['object']['type']                   		= 'htmlobject_select';
		$d['operating_system']['object']['attrib']['index']   	   		= array(0, 1);
		$d['operating_system']['object']['attrib']['name']         		= 'operating_system';
		$d['operating_system']['object']['attrib']['id']           		= 'operating_system';
		$d['operating_system']['object']['attrib']['type']         		= 'text';
		$d['operating_system']['object']['attrib']['options']      		= $operatingSystem;
		
		$d['vcpu']['label']                            					= $this->lang['vcpu'];
		$d['vcpu']['object']['type']                   					= 'htmlobject_select';
		$d['vcpu']['object']['attrib']['index']   	   					= array(0, 1);
		$d['vcpu']['object']['attrib']['name']         					= 'vcpu';
		$d['vcpu']['object']['attrib']['id']           					= 'vcpu';
		$d['vcpu']['object']['attrib']['type']         					= 'text';
		$d['vcpu']['object']['attrib']['options']      					= $vcpu;
		
		$d['html_information']  = $html_information;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}
}
?>
