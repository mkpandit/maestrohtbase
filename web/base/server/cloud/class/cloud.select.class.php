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

class cloud_select
{
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
		$t = $this->response->html->template($this->tpldir.'/cloud-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
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
		$html_information = "";
		if(isset($_GET) && !empty($_GET['azure_vm']) ) {
			$azure_vm_id  = unserialize($_GET['azure_vm'])[2];
			$azure_vm_name  = unserialize($_GET['azure_vm'])[0];
			$azure_vm_group  = unserialize($_GET['azure_vm'])[1];
			
			$html_information .= "<h3>VM Details</h3>";
			$azvm_info_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/azurevmmanipulation.py details '.$azure_vm_group.' '.$azure_vm_name);
			$azvm_info = json_decode($azvm_info_dump, true);
			
			if(empty($azvm_info)){
				$html_information .= "<div class='nothing-found'>VM Details were not available</div>";
			} else {
				$count = 1;
				$html_information .= "<div class='instance-container'>";
				
				if(in_array("Display Status->VM running", $azvm_info)){
					$html_information .= "<a href='#' class='add btn-labeled fa fa-stop-circle fa-plus' onclick=\"stopAzureVM('".$azure_vm_group."', '".$azure_vm_name."');\">Stop the instance</a>";
				} else {
					$html_information .= "<a href='#' class='add btn-labeled fa fa-play-circle fa-plus' onclick=\"startAzureVM('".$azure_vm_group."', '".$azure_vm_name."');\">Start the instance</a>";
				}
				$html_information .= "<a href='#' class='manage btn-labeled fa fa-minus-circle' onclick=\"terminateAzureVM('".$azure_vm_group."', '".$azure_vm_name."');\">Terminate the instance</a>";
				
				foreach($azvm_info as $k => $v){
					$temp = explode("->", $v);
					if($temp[1]){
						$html_information .= "<div class='instance-properties'><div class='instance-label'>".$temp[0]. "</div><div class'instance-prop'>: " . $temp[1]."</div></div>";
					}
				}
				
				$html_information .= "</div>";
			}
			$d['html_information'] = $html_information;
			
		} else {
			$vm_info_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/listazurevm.py');
			$vm_info = json_decode($vm_info_dump, true);		
			if(empty($vm_info)){
				$html_information .= "<div class='nothing-found'>Currenly no disk available on Azure</div>";
			} else {
				$count = 1;
				foreach($vm_info as $k => $v){
					$temp = explode("_*_", $v);
				
					$vm_status = str_replace(array("VM", " "), "", $temp[5]);
				
					if($vm_status == "running"){
						$vm_status_class = 'running';
					} else {
						$vm_status_class = 'stopped';
					}
					$param = serialize(array($temp[0], $temp[6], $temp[3]));
					$html_information .= "<div class='cloud-item item-".$count."'><h3>".$temp[0]."</h3><i class='fa fa-cloud fa-3 ".$vm_status_class."'></i>";
					$html_information .= "<div class='cloud-properties'><br />Location: ".$temp[1]."<br /><br />OS: ".$temp[2] ;
					$html_information .= "<br /><br /><i class=\"fa fa-cogs\" aria-hidden=\"true\"></i><a class='azure-vm-details-popup' href='index.php?base=cloud&azure_vm=".$param."'> details</a> <i class=\"fa fa-cog\" aria-hidden=\"true\"></i><a class='az-disk-details-popup' href='index.php?base=cloud&cloud_action=azuredisk&azure_vm=".$param."'> disk</a> <i class=\"fa fa-history\" aria-hidden=\"true\"></i><a class='az-log-popup' href='index.php?base=cloud&cloud_action=azurelog&azure_vm=".$param."'> log</a></div></div>";
					$count++;
				}
			}
			$d['html_information']  = $html_information;
		}
		return $d;
	}
}
?>
