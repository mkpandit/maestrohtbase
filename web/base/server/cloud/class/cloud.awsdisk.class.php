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

class cloud_awsdisk {
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
		$t = $this->response->html->template($this->tpldir.'/cloud-aws-disk.tpl.php');
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
		if(isset($_GET) && !empty($_GET['ec2_id'])){
			$html_information = "";
			$html_information .= "<h3>Disk/Volume Details</h3>";
			$awsVolumeDisk = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsvolume.py '.unserialize($_GET['ec2_id']));
			$awsDisk = json_decode($awsVolumeDisk, true);
			if(empty($awsDisk)){
				$html_information = "<div class='nothing-found'>Disk/Volume Details were not available</div>";
			} else {
				$count = 0;
				$html_information .= "<div class='volume-container'>";
				
				//Volume ID
				$volumeid = explode(":", $awsDisk[0]);
				$html_information .= "<div class='instance-properties'><div class='instance-label'>".$volumeid[0]. "</div>";
				$html_information .= "<div class'instance-prop'>: " . $volumeid[1]."</div></div>";
				
				//Disk Size
				$diskSize = explode(":", $awsDisk[1]);
				$html_information .= "<div class='instance-properties'><div class='instance-label'>".$diskSize[0]. "</div>";
				$html_information .= "<div class'instance-prop'>: ";
				
				$html_information .= '<input type="text" name="disk_size" id="disk-size" value="'.$diskSize[1].'" />';
				
				$html_information .= "</div></div>";
				
				//IOPS
				$diskIOPS = explode(":", $awsDisk[2]);
				$html_information .= "<div class='instance-properties'><div class='instance-label'>".$diskIOPS[0]. "</div>";
				$html_information .= "<div class'instance-prop'>: ";
				$html_information .= '<input type="text" name="disk_iops" id="disk-iops" value="'.$diskIOPS[1].'" />';
				$html_information .= "</div></div>";
				
				//Volume State;
				$volumeState = explode(":", $awsDisk[3]);
				$html_information .= "<div class='instance-properties'><div class='instance-label'>".$volumeState[0]. "</div>";
				$html_information .= "<div class'instance-prop'>: " . $volumeState[1]."</div></div>";
				
				//Volume Type
				$volumeType = explode(":", $awsDisk[4]);
				$html_information .= "<div class='instance-properties'><div class='instance-label'>".$volumeType[0]. "</div>";
				$html_information .= "<div class'instance-prop'>: "; 
				$html_information .= "<select name='volume_type' id='disk-type'>";
				$vType_array = array("gp2" => "General Purpose SSD", "io1" => "Provisioned IOPS SSD", "st1" => "Throughput Optimized HDD", "sc1" => "Cold HDD");
				$vTypeShortCode = explode("-", $volumeType[1]);
				foreach($vType_array as $k => $v){
					if($k == trim($vTypeShortCode[1])){
						$selected = "selected";
					} else {
						$selected = "";
					}
					$html_information .= "<option value='".$k."' ".$selected.">".$v."</option>";
				}
				$html_information .= "</select>";
				
				$html_information .= "</div></div>";
				
				$html_information .= "<div class='instance-properties'><div class='instance-label'>&nbsp;</div>";
				//$volEc2ID = trim($volumeid[1]) . "*" .unserialize($_GET['ec2_id']);
				$html_information .= "<div class'instance-prop'>  <span class='update-volume' id='update-volume-disk' onclick='supdateVolume(\"".trim($volumeid[1])."\", \"".unserialize($_GET['ec2_id'])."\"); '>Update Volume/Disk</span></div></div>";
				
				/*foreach($awsDisk as $k => $v){
					$temp = explode(":", $v);
					$html_information .= "<div class='instance-properties'><div class='instance-label'>".$temp[0]. "</div><div class'instance-prop'>: " . $temp[1]."</div></div>";
				}*/
				$html_information .= "</div>";
			}
			$d['html_information']  = $html_information;
		} else {
			$html_information = "";
			$disk_info_dump = shell_exec('/usr/bin/python '.$this->rootdir.'/server/storage/script/scanawsstorage.py');
			$disk_info = json_decode($disk_info_dump, true);
			if(empty($disk_info)){
				$html_information .= "<div class='nothing-found'>Currenly no disk available on AWS</div>";
			} else {
				$count = 1;
				foreach($disk_info as $k => $v){
					$temp = explode("_*_", $v);
					//$html_information .= "<tr> <td>" .$count. ".</td> <td>" .$temp[0]. "</td> <td>" .$temp[1]. "</td> </tr>";
					$html_information .= "<div class='cloud-item item-".$count."'><h4>".$temp[0]."</h4><i class='fa fa-database fa-3'></i>";
					$html_information .= "<div class='cloud-properties'><br />Created on: ".$temp[1]."<br /></div>";
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
