<?php
/**
 * Storage Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class storage_diskadd {
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'storage_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'storage_identifier';
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
		$data = $this->diskadd();
		$t = $this->response->html->template($this->tpldir.'/storage-diskadd.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->response->get_array());
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($data['html_information'], 'html_information');
		
		/*$storage_data = $this->storage_piechart();
		$t->add($storage_data['sfree'], 'free_storage_data');
		$t->add($storage_data['stotal'], 'total_storage_data');
		$t->add($storage_data['sused'], 'used_storage_data');
		$t->add($storage_data['spercent'], 'percent_storage_data');*/
		
		$space = $this->space_details();
		$t->add($space['sfree'], 'free_storage_data');
		$t->add($space['stotal'], 'total_storage_data');
		$t->add($space['sused'], 'used_storage_data');
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
	function diskadd() {
		$d = array();
		$html_information = "<div class='disk-loader'>
			<span class='icon-wrap icon-wrap-lg icon-circle'><i class='fa fa fa-spinner fa-spin fa-4x'></i></span>
			<p>&nbsp;</p>
			<p>Loading available resource hosts</p>
		</div>";
		$count = 1;
		$d['html_information']  = $html_information;
		return $d;
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
