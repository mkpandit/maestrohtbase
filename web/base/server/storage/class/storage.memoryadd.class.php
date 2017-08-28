<?php
/**
 * Storage Add
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class storage_memoryadd {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aws_config_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aws_config_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aws_config_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aws_config_identifier';
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'memoryadd', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/storage-memoryadd.tpl.php');
		$t->add($this->lang['label'], 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['label'], 'form_add');
		$t->group_elements(array('param_' => 'form'));
		
		$space = $this->space_details();
		$t->add($space['sfree'], 'free_storage_data');
		$t->add($space['stotal'], 'total_storage_data');
		$t->add($space['sused'], 'used_storage_data');
		
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$memory_amount				= $form->get_request('memory_amount');
			$message = shell_exec("sudo " . $this->rootdir."/server/storage/script/mfstmpfs integrate -s ". $memory_amount ." 2>&1");
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
		$d = array();
		$html_information = "<div class='disk-loader'>";
		
		$memory_info_dunp = shell_exec('python '.$this->rootdir.'/server/storage/script/scanmemory.py');
		$memory_info = json_decode($memory_info_dunp, true);
		if(empty($memory_info)){
			$html_information .= "<div class='nothing-found'>No memory found to add as disk storage</div>";
		} else {
			$count = 0;
			foreach($memory_info as $k => $v){
				$temp = explode("_", $v);
				$html_information .= "<p class='memory item-".$count."'><i class='fa fa-database fa-3'></i>";
				if($count === 0){
					$html_information .= "Totoal Memory: " . $temp[1] . " GB";
				}
				if($count > 0){
					$html_information .= "Free Memory: " . $temp[1]. " GB";
				}
				$html_information .= "</p>";
				$count++;
			}
		}
		$html_information .= "</div>";
		$d['html_information']  = $html_information;
		
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'memoryadd');
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->value = 'Mount Memory';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		
		$d['memory_amount']['label']                             	= "Memory amount to add:";
		$d['memory_amount']['required']                          	= true;
		$d['memory_amount']['object']['type']                    	= 'htmlobject_input';
		$d['memory_amount']['object']['attrib']['name']          	= 'memory_amount';
		$d['memory_amount']['object']['attrib']['id']            	= 'memory_amount';
		$d['memory_amount']['object']['attrib']['type']          	= 'text';
		$d['memory_amount']['object']['attrib']['value']         	= $aws_access_key_id;
		$d['memory_amount']['object']['attrib']['maxlength']     	= 50;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
	function storage_piechart(){
		require_once($this->rootdir.'/server/aa_server/class/datacenter.dashboard.class.php');
		$storage_info = new datacenter_dashboard($this->htvcenter, $this->response);
		$data = $storage_info->storagetaken();
		//print_r($data['l_content']);
		return $data;
	}
	
	function space_details(){
		$disk_info_dump = shell_exec('python '.$this->rootdir.'/server/storage/script/diskspace.py');
		$disk_info = json_decode($disk_info_dump, true);
		$data['stotal'] = str_replace(" Gi", "", $disk_info[0]);
		$data['sfree'] = str_replace(" Gi", "", $disk_info[1]);
		$data['sused'] = $data['stotal'] - $data['sfree'];
		return $data;
	}

}
?>
