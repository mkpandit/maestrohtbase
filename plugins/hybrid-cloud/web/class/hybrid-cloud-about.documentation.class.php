<?php
/**
 * hybrid-cloud-about Documentation
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class hybrid_cloud_about_documentation
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_about_msg";
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
		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-about-documentation.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['type_title'], 'type_title');
		$t->add($this->lang['type_content'], 'type_content');
		$t->add($this->lang['tested_title'], 'tested_title');
		$t->add($this->lang['tested_content'], 'tested_content');
		$t->add($this->lang['introduction_title'], 'introduction_title');
		$t->add($this->lang['introduction_content'], 'introduction_content');
		$t->add($this->lang['introduction_title1'], 'introduction_title1');
		$t->add($this->lang['introduction_content1'], 'introduction_content1');
		$t->add($this->lang['introduction_title2'], 'introduction_title2');
		$t->add($this->lang['introduction_content2'], 'introduction_content2');
		$t->add($this->lang['introduction_title3'], 'introduction_title3');
		$t->add($this->lang['introduction_content3'], 'introduction_content3');
		$t->add($this->lang['introduction_title4'], 'introduction_title4');
		$t->add($this->lang['introduction_content4'], 'introduction_content4');
		$t->add($this->lang['introduction_title5'], 'introduction_title5');
		$t->add($this->lang['introduction_content5'], 'introduction_content5');
		$t->add($this->lang['introduction_title6'], 'introduction_title6');
		$t->add($this->lang['introduction_content6'], 'introduction_content6');
		$t->add($this->lang['requirements_title'], 'requirements_title');
		$t->add($this->lang['requirements_list'], 'requirements_list');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		return $t;
	}


}
?>
