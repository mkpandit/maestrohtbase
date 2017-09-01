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

class awsinstance {
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
		$data = $this->disks();
		$t = $this->response->html->template($this->tpldir.'/storage-aws-instance.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->response->get_array());
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		
		$t->add($data['html_information'], 'html_information');
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
	function disks() {
		$d = array();
		$html_information = "<div class='disk-loader-azure'>";
		
		$html_information .= "<table class='azure-resource-table'>";
		$html_information .= "<tr> <th>#</th> <th>Instance ID</th> <th>Name</th> <th>IP Address</th> <th>Status</th> <th>Launch time</th></tr>";
		$disk_info_dump = shell_exec('python '.$this->rootdir.'/server/storage/script/scanawsinstance.py');
		$disk_info = json_decode($disk_info_dump, true);
		
		if(empty($disk_info)){
			$html_information .= "<tr> <td colspan=6>Currenly no Instance available on AWS</td></tr>";
		} else {
			$count = 1;
			foreach($disk_info as $k => $v){
				$temp = explode("_", $v);
				//echo $temp[3]['Code'] . " - " .$temp[3]['Name'];
				$html_information .= "<tr> <td>" .$count. ".</td> <td>" .$temp[0]. "</td> <td>" .$temp[1]. "</td> <td>" .$temp[2]. "</td> <td>" .$temp[3]. "</td> <td>" .$temp[4]. "</td></tr>";
				$count++;
			}
		}
		$html_information .= "</table>";
		$html_information .= "</div>";
		$d['html_information']  = $html_information;
		return $d;
	}
}
?>
