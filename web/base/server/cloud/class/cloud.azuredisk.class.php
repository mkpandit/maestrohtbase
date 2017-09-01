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

class cloud_azuredisk {
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
		$t = $this->response->html->template($this->tpldir.'/cloud-azure-disk.tpl.php');
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
		if(isset($_GET) && !empty($_GET['azure_vm'])){
			$html_information = "";
			$azure_vm_id  = unserialize($_GET['azure_vm'])[2];
			$azure_vm_name  = unserialize($_GET['azure_vm'])[0];
			$azure_vm_group  = unserialize($_GET['azure_vm'])[1];
			
			$html_information .= "<h3>Disk/Volume Details</h3>"; //. $azure_vm_name . $azure_vm_group;
			$azVolumeDisk = shell_exec('python '.$this->rootdir.'/server/cloud/script/azurevmmanipulation.py disk '.$azure_vm_group.' '.$azure_vm_name);
			$azDisk = json_decode($azVolumeDisk, true);
			if(empty($azDisk)){
				$html_information .= "<div class='nothing-found'>Disk/Volume Details were not available</div>";
			} else {
				$html_information .= "<div class='volume-container'>";
				
				//Disk Size
				$diskSize = explode(":", $azDisk[0]);
				$html_information .= "<div class='instance-properties'><div class='instance-label'>".$diskSize[0]. " (OS)</div>";
				$html_information .= "<div class'instance-prop'>: ";
				
				$html_information .= '<input type="text" name="disk_size" id="disk-size" value="'.$diskSize[1].'" /> GB';
				
				$html_information .= "</div></div>";
				
				$html_information .= "<div class='instance-properties'><div class='instance-label'>&nbsp;</div>";
				$html_information .= "<div class'instance-prop'>  <span class='update-volume' id='update-volume-disk' onclick='azUpdateVolume(\"".$azure_vm_group."\", \"".$azure_vm_name."\"); '>Update Volume/Disk</span></div></div>";
				
				$azDataDiskDump = shell_exec('python '.$this->rootdir.'/server/cloud/script/attachazuredisk.py info '.$azure_vm_group.' '.$azure_vm_name);
				$azDataDisk = json_decode($azDataDiskDump, true);
				
				if(empty($azDataDisk)){
					$html_information .= "<div class='instance-properties'><div class='instance-label'>Data Disks</div>";
					$html_information .= "<div class'instance-prop'>: No Data Disk Attached to this VM</div></div>";
				} else {
					foreach($azDataDisk as $x => $y){
						if(strpos($y, 'DiskList') === false){
							$html_information .= "<div class='instance-properties'><div class='instance-label'>Data Disks</div>";
							$html_information .= "<div class'instance-prop'>: ".$y."</div></div>";
						} else {
							$disk_list = $y;
						}
					}
				}
				
				$html_information .= "<div class='instance-properties'><div class='instance-label'>Attach a Data Disks</div>";
				$html_information .= "<div class'instance-prop'>: ";
				
				$html_information .= "<select name='volume_type' id='attach-disk-name'>";
				$vType_array = explode("_*_", $disk_list);
				array_shift($vType_array);
				foreach($vType_array as $k => $v){
					$html_information .= "<option value='".$v."' >".$v."</option>";
				}
				
				$html_information .= "</select>";
				$html_information .= "<span class='update-volume' id='attach-volume-disk' onclick='azDiskAttach(\"".$azure_vm_group."\", \"".$azure_vm_name."\"); '>Attach Volume/Disk</span>";
					
				$html_information .= "</div></div>";
				
				$html_information .= "</div>";
			}
		} else {
			$disk_info_dump = shell_exec('/usr/bin/python '.$this->rootdir.'/server/storage/script/scanazurestorage.py');
			$disk_info = json_decode($disk_info_dump, true);
			if(empty($disk_info)){
				$html_information .= "<div class='nothing-found'>Currenly no disk available on Azure</div>";
			} else {
				$count = 1;
				foreach($disk_info as $k => $v){
					$temp = explode("_*_", $v);
					$html_information .= "<div class='cloud-item item-".$count."'><h4>".substr($temp[0], 0, 20)."</h4><i class='fa fa-database fa-3'></i>";
					$html_information .= "<div class='cloud-properties'><br />Size: ".$temp[1]." GB<br /><br />Location: ".$temp[2]."</div>";
					$html_information .= "</div>";
					$count++;
				}
			}
		}
		$d['html_information']  = $html_information;
		return $d;
	}
}
?>
