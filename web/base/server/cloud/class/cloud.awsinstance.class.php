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
		$data = $this->awsec2();
		$t = $this->response->html->template($this->tpldir.'/cloud-aws-instance.tpl.php');
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
	function awsec2() {
		$d = array();
		if(isset($_GET) && !empty($_GET['ec2_id'])){
			$ec2ID  = trim(unserialize($_GET['ec2_id']));
			$html_information .= "<h3>Instance Details</h3>";
			$ec2_info_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsec2manipulation.py '.unserialize($_GET['ec2_id']).' details' );
			$ec2_info = json_decode($ec2_info_dump, true);
			
			if(empty($ec2_info)){
				$html_information = "<div class='nothing-found'>Instance Details were not available</div>";
			} else {
				$count = 1;
				$html_information .= "<div class='instance-container'>";
				
				if(in_array("Code->16", $ec2_info)){
					$html_information .= "<a href='#' class='add btn-labeled fa fa-stop-circle fa-plus' onclick=\"stopAWSInstance('".$ec2ID."');\">Stop the instance</a>";
				} else {
					$html_information .= "<a href='#' class='add btn-labeled fa fa-play-circle fa-plus' onclick=\"startAWSInstance('".$ec2ID."');\">Start the instance</a>";
				}
				$html_information .= "<a href='#' class='manage btn-labeled fa fa-minus-circle' onclick=\"terminateAWSInstance('".$ec2ID."');\">Terminate the instance</a>";
				
				foreach($ec2_info as $k => $v){
					$temp = explode("->", $v);
					if($temp[1]){
						$html_information .= "<div class='instance-properties'><div class='instance-label'>".$temp[0]. "</div><div class'instance-prop'>: " . $temp[1]."</div></div>";
					}
				}
				
				$html_information .= "</div>";
			}
			$d['html_information'] = $html_information;
		} else {
			$disk_info_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/scanawsinstance.py');
			$disk_info = json_decode($disk_info_dump, true);
			if(empty($disk_info)){
				$html_information .= "<div class='nothing-found'>Currenly no Instance available on AWS</div>";
			} else {
				$count = 1;
				foreach($disk_info as $k => $v){
					$temp = explode("_", $v);
					$vm_status = str_replace(array("{", "}", "u", "'"), "", $temp[3]);
					$vm_status = explode(",", $vm_status);
					$vm_status = str_replace(array('Code', ':', ' '), "", $vm_status[0]);
				
					if($vm_status == 16){
						$vm_status_class = 'running';
					} else if($vm_status == 80 || $vm_status == 48){
						$vm_status_class = 'stopped';
					}
					$serializeEc2ID = serialize($temp[0]);
					$html_information .= "<div class='cloud-item item-".$count."'><h4>".$temp[0]."</h4><i class='fa fa-cloud fa-3 ".$vm_status_class."'></i>";
					$html_information .= "<div class='cloud-properties'><br />Name: ".$temp[1]."<br /><br />IP: ".$temp[2]."<br /><br /><i class=\"fa fa-cogs\" aria-hidden=\"true\"></i><a class='aws-vm-details-popup' href='index.php?base=cloud&cloud_action=awsinstance&ec2_id=".serialize($temp[0])."'> details</a> <i class=\"fa fa-cog\" aria-hidden=\"true\"></i><a class='aws-disk-details-popup' href='index.php?base=cloud&cloud_action=awsdisk&ec2_id=".serialize($temp[0])."'> disk</a> <i class=\"fa fa-history\" aria-hidden=\"true\"></i><a class='aws-log-popup' href='index.php?base=cloud&cloud_action=awslog&ec2_id=".serialize($temp[0])."'> log</a></div>";
					$html_information .= "</div>";
					$count++;
				}
			}
			$d['html_information']  = $html_information;
		}
		return $d;
	}
}
?>