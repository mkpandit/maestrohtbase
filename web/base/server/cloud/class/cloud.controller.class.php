<?php
/**
 * Resource Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class cloud_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cloud_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "cloud_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'cloud_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'cloud_identifier';
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
	'select' => array (
		'tab' => 'VMs on Azure',
		'label' => 'VMs on Azure',
		'action_remove' => 'remove',
		'action_reboot' => 'reboot',
		'action_poweroff' => 'poweroff',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add a new resource',
		'action_new' => 'new',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_mac' => 'Mac',
		'table_ip' => 'IP',
		'table_cpu' => 'CPU',
		'table_nics' => 'NIC',
		'table_memory' => 'RAM',
		'table_load' => 'Load',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_type_filter' => 'Filter by Resource Type',
		'lang_filter' => 'Filter by Resource',
		'lang_filter_title' => 'Filter by Resource ID, Name or Mac. Use ? as single and * as multi wildcard.',
		'please_wait' => 'Loading. Please wait ..',
	),
	'cloudmenu' => array (
		'tab' => 'Maestro Multi-cloud',
		'label' => 'Maestro Multi-cloud',
	),
	'cloudprice' => array (
		'tab' => 'Maestro Multi-cloud Pricing',
		'label' => 'Maestro Multi-cloud Pricing',
		'memory' => 'Memory / RAM',
		'operating_system' => 'Operating System',
		'vcpu' => 'V CPUs / Cores',
	),
	'add' => array (
		'label' => 'Add resource',
		'title' => 'Add a new resource as ',
		'vm_type' => 'a Virtual Machine from type',
		'local' => 'or by integrating an existing, local-installed server',
		'unmanaged' => 'Manual add an un-managed system',
		'manual_new_resource' => 'or manual add an un-managed system',
		'start_local_server' => 'Please enable and start the "local-server" plugin!',
		'integrate_local_server' => 'Integrate an existing local installed Server',
		'start_vm_plugin' => 'Please enable and start one of the virtualization plugins!',
		'create_vm' => 'Create a %s Virtual Machine',
		'vm' => 'Virtual Machine',
		'msg' => 'Added resource %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'addazurevm' => array(
		'label' => 'Add Azure VM',
		'title' => 'Add Azure VM',
		'azure_vm_name' => 'Azure VM Name',
		'azure_vnet_name' => 'Azure V Net Name',
		'azure_subnet_name' => 'Azure Sub Net Name',
		'azure_osdisk_name' => 'Azure OS Disk Name',
		'azure_ipconfig_name' => 'Azure IP Config Name',
		'azure_nic_name' => 'Azure NIC Name',
		'azure_user_name' => 'VM User Name',
		'azure_password' => 'VM Password',
		'azure_resource_group' => 'Select resource group',
	),
	'addazurevmfromimage' => array(
		'label' => 'Add Azure VM from Maestro Image',
		'title' => 'Add Azure VM from Maestro Image',
		'azure_vm_name' => 'Azure VM Name',
		'azure_vnet_name' => 'Azure V Net Name',
		'azure_subnet_name' => 'Azure Sub Net Name',
		'azure_osdisk_name' => 'Azure OS Disk Name',
		'azure_ipconfig_name' => 'Azure IP Config Name',
		'azure_nic_name' => 'Azure NIC Name',
		'azure_user_name' => 'VM User Name',
		'azure_password' => 'VM Password',
		'azure_resource_group' => 'Select resource group',
	),
	'configazure' => array (
		'tab' => 'Azure Credential Configuration',
		'label' => 'Azure Credential Configuration',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
		'secret_key' => 'Secret Key',
		'subscription_id' => 'Subscription ID',
		'client_id' => 'Client ID',
		'tenant_id' => 'Tenant ID',
	),
	'azuredisk' => array (
		'tab' => 'Disks on Azure',
		'label' => 'Disks on Azure',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'azurestorage' => array ( 
		'tab' => 'Storage on Azure',
		'label' => 'Storage on Azure',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'azurelog' => array ( 
		'tab' => 'Activity logs from Azure',
		'label' => 'Activity logs from Azure',
	),
	'addazurestorage' => array ( 
		'tab' => 'Add Storage on Azure',
		'label' => 'Add Storage on Azure',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
		'azure_storage_name' => 'Azure Storage Name',
		'azure_resource_group' => 'Azure Resource Group',
	),
	'awsvolumes' => array ( 
		'tab' => 'Aws Volumes',
		'label' => 'Aws Volumes',
	),
	'addawsvolume' => array ( 
		'tab' => 'Add Aws Volumes',
		'label' => 'Add Aws Volumes',
		'aws_volume_size' => 'Volume Size',
		'aws_volume_type' => 'Volume type',
	),
	'addazuredisk' => array (
		'label' => 'Add Azure Disk',
		'tab' => 'Add Azure Disk',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
		'azure_resource_group' => 'Azure Resource Group',
		'azure_disk_name' => 'Disk name',
		'azure_disk_size' => 'Disk size (GB)',
	),
	'uploadfiles' => array (
		'label' => 'Upload file(s) to Azure Storage',
		'tab' => 'Upload file(s) to Azure Storage',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
		'azure_resource_group' => 'Azure Resource Group',
		'azure_storage_name' => 'Azure Storage Name',
		'storage_container_name' => 'Container Name',
		'file_full_path' => 'Full Path of File(s)',
	),
	'awsinstance' => array (
		'tab' => 'AWS Instance (EC2)',
		'label' => 'AWS Instance (EC2)',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'addawsinstance' => array (
		'tab' => 'Add AWS Instance (EC2)',
		'label' => 'Add AWS Instance (EC2)',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
		'aws_ami_id' => 'AWS AMI ID',
		'aws_instance_type' => 'AWS Instance type',
		'aws_instance_min' => 'Instances MIN Count',
		'aws_instance_max' => 'Instance MAX Count'
	),
	'awsdisk' => array (
		'tab' => 'Disks on AWS',
		'label' => 'Disks on AWS',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'addawsstorage' => array (
		'tab' => 'Add AWS Bucket (S3)',
		'label' => 'Add AWS Bucket (S3)',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
		'aws_bucket_name' => 'AWS S3 Bucket Name'
	),
	'awslog' => array (
		'tab' => 'Activity logs from AWS',
		'label' => 'Activity logs from AWS',
	),
	'configaws' => array (
		'tab' => 'AWS Credential Configuration',
		'label' => 'AWS Credential Configuration',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add new storage',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'lang_filter' => 'Filter by Disk type',
		'please_wait' => 'Loading. Please wait ..',
		'secret_key' => 'Secret Key',
		'aws_access_key_id' => 'Access Key ID',
		'aws_secret_access_key' => 'AWS Secret Access Key',
		'tenant_id' => 'Tenant ID',
	),
	'edit' => array (
		'label' => 'Edit resource %s',
		'form_docu' => 'This form allows to manually set the IP address for a local booting VM<br>
			if this is not automatically provided e.g. by the "dhcpd plugin".',
		'form_edit_resource' => 'Edit Resource IP address of local booting VM',
		'form_ip' => 'IP-Adress',
		'error_ip' => 'IP-Adress must be %s',
		'msg_ip_in_use' => 'IP Adress already in use! Not adding resource',
		'msg' => 'Edited resource %s',
		'msg_add_failed' => 'Failed editing resource',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	)
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
		$this->tpldir   = $this->rootdir.'/server/cloud/tpl';
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/cloud/lang", 'cloud.ini');
		
		if(isset($_GET) && $_GET['cloudprice'] == 'yes') {
			$memory 					= trim($_GET['memory']);
			$operating_system			= trim($_GET['operating_system']);
			$vcpu						= trim($_GET['vcpu']);
			
			$price_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/awspriceparsing.py "'.$memory.'" "'.$operating_system.'" "'.$vcpu.'"');
			$price_info = json_decode($price_dump, true);
			
			$aws_count = 0; $az_count = 0;
			
			foreach($price_info as $k => $v){
				if(substr( $v, 0, 3 ) === "az_"){
					$az_count++;
					if($az_count % 2 != 0){
						$az_data .= '<p class="price-item">';
						$fomatted_price = number_format((float)str_replace("az_", "", $v), 2, '.', '');
						$az_data .= 'Price: $'.$fomatted_price.'/hour ( $'. number_format(( (float)$fomatted_price * 24 * 30), 2, '.', '') .'/month)<br />';
					} else {
						$az_data .= 'Description: '.str_replace("az_", "", $v).'<br />';
						$az_data .= '</p>';
					}
				} else {
					$aws_count++;
					if($aws_count % 2 != 0){
						$aws_data .= '<p class="price-item">';
						$fomatted_price = number_format((float) $v, 2, '.', '');
						$aws_data .= 'Price: $' .$fomatted_price. '/hour ( $'.number_format( ((float) $fomatted_price * 24 * 30), 2, '.', '').'/month) <br />';
					} else {
						$aws_data .= 'Desccription: ' . $v. '<br />';
						$aws_data .= '</p>';
					}
				}
			}
			
			$data .= '<div class="aws-prices"><h4><b>'.($aws_count / 2).'</b> prices on AWS</h4>'.$aws_data.'</div>';
			$data .= '<div class="azure-prices"><h4><b>'.($az_count / 2).'</b> prices on Azure</h4>'.$az_data.'</div>';
			
			if(empty($data)) {
				$data = "<p>No <b>".$operating_system."</b> VM found with <b>".$memory." RAM</b>, <b>".$vcpu." CPU</b>. Try with different combination.</p>";
			} else {
				$data = $data;
			}
			echo $data; exit();
		}
		
		if(isset($_GET) && $_GET['graphprice'] == 'yes'){
			$price_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/awspriceparsing.py coreprice');
			$price_info = json_decode($price_dump, true);
			$data = "";
			foreach($price_info as $k => $v){
				$data = $data . "_" . ((float)$v * 24 * 30);
			}
			echo $data;
			exit();
		}
		
		if(isset($_GET) && $_GET['awsec2'] == 'stop'){
			$ec2Stop = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsec2manipulation.py '.$_GET['ec2ID'].' stop');
			$ec2Stop_info = json_decode($ec2Stop, true);
			$data = "";
			foreach($ec2Stop_info as $k => $v){
				$data = $data . $v;
			}
			echo $data;
			exit();
		}
		
		if(isset($_GET) && $_GET['awsec2'] == 'start'){
			$ec2Start = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsec2manipulation.py '.$_GET['ec2ID'].' start' );
			$ec2Start_info = json_decode($ec2Start, true);
			$data = "";
			foreach($ec2Start_info as $k => $v){
				$data = $data . $v;
			}
			echo $data;
			exit();
		}
		
		if(isset($_GET) && $_GET['awsec2'] == 'terminate'){
			$ec2Sterminate = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsec2manipulation.py '.$_GET['ec2ID'].' terminate' );
			$ec2Sterminate_info = json_decode($ec2Sterminate, true);
			$data = "";
			foreach($ec2Sterminate_info as $k => $v){
				$data = $data . $v;
			}
			echo $data;
			exit();
		}
		
		if(isset($_GET) && $_GET['awsdisk'] == 'update'){
			$ec2Sterminate = shell_exec('python '.$this->rootdir.'/server/cloud/script/awsvolume.py update '.$_GET['ec2_id'].' '.$_GET['volume_id'].' '.$_GET['disk_size']. ' '.$_GET['disk_iops']. ' '.$_GET['disk_type']);
			$ec2Sterminate_info = json_decode($ec2Sterminate, true);
			$data = "";
			foreach($ec2Sterminate_info as $k => $v){
				$data = $data . $v;
			}
			echo $data;
			exit();
		}
		
		if(isset($_GET) && !empty($_GET['azurevm'])){
			if($_GET['azurevm'] == 'stop'){
				$az_vm_op_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/azurevmmanipulation.py stop '.$_GET['group_name'].' '.$_GET['vm_name']);
				$az_vm_op = json_decode($az_vm_op_dump, true);
			}
			else if($_GET['azurevm'] == 'start'){
				$az_vm_op_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/azurevmmanipulation.py start '.$_GET['group_name'].' '.$_GET['vm_name']);
				$az_vm_op = json_decode($az_vm_op_dump, true);
			}
			else if($_GET['azurevm'] == 'disk_resize'){
				$az_vm_op_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/azurevmmanipulation.py dupdate '.$_GET['group_name'].' '.$_GET['vm_name'].' '.$_GET['disk_size']);
				$az_vm_op = json_decode($az_vm_op_dump, true);
			}
			else {
				$az_vm_op_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/azurevmmanipulation.py terminate '.$_GET['group_name'].' '.$_GET['vm_name']);
				$az_vm_op = json_decode($az_vm_op_dump, true);
			}
			$data = "";
			foreach($az_vm_op as $k => $v){
				$data = $data . $v;
			}
			echo $data;
			exit();
		}
		
		if(isset($_GET) && !empty($_GET['azurediskattach'])){
			if($_GET['azurediskattach'] == 'disk_attach'){
				$az_vm_attach_dump = shell_exec('python '.$this->rootdir.'/server/cloud/script/attachazuredisk.py attach '.$_GET['group_name'].' '.$_GET['vm_name'].' '.$_GET['disk_name']);
				$az_vm_attach = json_decode($az_vm_attach_dump, true);
			}
			$data = "";
			foreach($az_vm_attach as $k => $v){
				$data = $data . $v;
			}
			echo $data;
			exit();
		}
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
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}
		$this->response->params['resource_filter'] = $this->response->html->request()->get('resource_filter');

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'cloudmenu':
				$content[] = $this->select(false);
				$content[] = $this->cloudmenu(true);
			break;
			case 'cloudprice':
				$content[] = $this->select(false);
				$content[] = $this->cloudprice(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->add(true);
			break;
			case 'addazurevm':
				$content[] = $this->select(false);
				$content[] = $this->addazurevm(true);
			break;
			case 'addazurevmfromimage':
				$content[] = $this->select(false);
				$content[] = $this->addazurevmfromimage(true);
			break;
			case 'azuredisk':
				$content[]  = $this->select(false);
				$content[]  = $this->azuredisk(true);
			break;
			case 'configazure':
				$content[]  = $this->select(false);
				$content[]  = $this->configazure(true);
			break;
			case 'addazuredisk':
				$content[]  = $this->select(false);
				$content[]  = $this->addazuredisk(true);
			break;
			case 'azurestorage':
				$content[]  = $this->select(false);
				$content[]  = $this->azurestorage(true);
			break;
			case 'addazurestorage':
				$content[]  = $this->select(false);
				$content[]  = $this->addazurestorage(true);
			break;
			case 'azurelog':
				$content[]  = $this->select(false);
				$content[]  = $this->azurelog(true);
			break;
			case 'uploadfiles':
				$content[]  = $this->select(false);
				$content[]  = $this->uploadfiles(true);
			break;
			case 'awsinstance':
				$content[]  = $this->select(false);
				$content[]  = $this->awsinstance(true);
			break;
			case 'addawsinstance':
				$content[]  = $this->select(false);
				$content[]  = $this->addawsinstance(true);
			break;
			case 'awsdisk':
				$content[]  = $this->select(false);
				$content[]  = $this->awsdisk(true);
			break;
			case 'awsvolumes':
				$content[]  = $this->select(false);
				$content[]  = $this->awsvolumes(true);
			break;
			case 'awslog':
				$content[]  = $this->select(false);
				$content[]  = $this->awslog(true);
			break;
			case 'addawsstorage':
				$content[]  = $this->select(false);
				$content[]  = $this->addawsstorage(true);
			break;
			case 'addawsvolume':
				$content[]  = $this->select(false);
				$content[]  = $this->addawsvolume(true);
			break;
			case 'configaws':
				$content[]  = $this->select(false);
				$content[]  = $this->configaws(true);
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
	 * Select resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.select.class.php');
			$controller = new cloud_select($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Build Cloud Menu
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function cloudmenu( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.menu.class.php');
			$controller = new cloudmenu($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['cloudmenu'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloudmenu']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'cloudmenu' );
		$content['onclick'] = false;
		if($this->action === 'cloudmenu'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Build Cloud Price Menu
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function cloudprice( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.price.class.php');
			$controller = new cloudprice($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['cloudprice'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloudprice']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'cloudprice' );
		$content['onclick'] = false;
		if($this->action === 'cloudprice'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/resource/class/resource.add.class.php');
			$controller                  = new resource_add($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['add'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add Azure VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function addazurevm( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/add.azurevm.class.php');
			$controller                  = new addazurevm($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addazurevm'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addazurevm']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addazurevm' );
		$content['onclick'] = false;
		if($this->action === 'addazurevm'){
			$content['active']  = true;
		}
		return $content;
	}	
	
	//--------------------------------------------
	/**
	 * Add Azure VM from Image
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function addazurevmfromimage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/add.azurevmfromimage.class.php');
			$controller                  = new addazurevmfromimage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addazurevmfromimage'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addazurevmfromimage']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addazurevmfromimage' );
		$content['onclick'] = false;
		if($this->action === 'addazurevmfromimage'){
			$content['active']  = true;
		}
		return $content;
	}	

	//--------------------------------------------
	/**
	 * Config Azure with Credentials
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function configazure( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.configazure.class.php');
			$controller = new cloud_az_config($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['configazure'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'configazure' );
		$content['onclick'] = false;
		if($this->action === 'configazure' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------azurestorage
	/**
	 * Disks on Microsoft Azure
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function azuredisk( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.azuredisk.class.php');
			$controller = new cloud_azuredisk($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['azuredisk'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['azuredisk']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'azuredisk' );
		$content['onclick'] = false;
		if($this->action === 'azuredisk'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------addazurestorage
	/**
	 * Storage on Microsoft Azure
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function azurestorage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.azurestorage.class.php');
			$controller = new cloud_azurestorage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['azurestorage'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['azurestorage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'azurestorage' );
		$content['onclick'] = false;
		if($this->action === 'azurestorage'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------awsvolumes
	/**
	 * Storage on Microsoft Azure
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function awsvolumes( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.awsvolumes.class.php');
			$controller = new cloud_awsvolumes($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['awsvolumes'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['awsvolumes']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'awsvolumes' );
		$content['onclick'] = false;
		if($this->action === 'awsvolumes'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------azurelog
	/**
	 * Activity log from Azure
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function azurelog( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.azurelog.class.php');
			$controller = new azurelog($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['azurelog'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['azurelog']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'azurelog' );
		$content['onclick'] = false;
		if($this->action === 'azurelog'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add Azure Disk (Managed Disk)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function addazuredisk( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.addazuredisk.class.php');
			$controller                  = new addazuredisk($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addazuredisk'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addazuredisk']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'new' );
		$content['onclick'] = false;
		if($this->action === 'addazuredisk' || $this->action === $this->lang['select']['action_new']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add Storage on Microsoft Azure
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function addazurestorage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.addazurestorage.class.php');
			$controller = new addazurestorage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addazurestorage'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['addazurestorage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addazurestorage' );
		$content['onclick'] = false;
		if($this->action === 'addazurestorage'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Remove resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function uploadfiles( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.uploadfiles.class.php');
			$controller                  = new uploadfiles($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['uploadfiles'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'uploadfiles';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'uploadfiles' );
		$content['onclick'] = false;
		if($this->action === 'uploadfiles'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Instance on AWS
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function awsinstance( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.awsinstance.class.php');
			$controller = new awsinstance($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['awsinstance'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['awsinstance']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'awsinstance' );
		$content['onclick'] = false;
		if($this->action === 'awsinstance'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add an AWS Instance (EC2)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function addawsinstance( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.addawsinstance.class.php');
			$controller = new addawsinstance($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addawsinstance'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addawsinstance']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addawsinstance' );
		$content['onclick'] = false;
		if($this->action === 'addawsinstance' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Disks on AWS
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function awsdisk( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.awsdisk.class.php');
			$controller = new cloud_awsdisk($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['awsdisk'];
			$data = $controller->action();
		}
		
		$content['label']   = $this->lang['awsdisk']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'awsdisk' );
		$content['onclick'] = false;
		if($this->action === 'awsdisk'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add an AWS Storage (S3)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function addawsstorage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.addawsstorage.class.php');
			$controller = new addawsstorage($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addawsstorage'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addawsstorage']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addawsstorage' );
		$content['onclick'] = false;
		if($this->action === 'addawsstorage' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add an AWS addawsvolume (EBS)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function addawsvolume( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.addawsvolume.class.php');
			$controller = new addawsvolume($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['addawsvolume'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addawsvolume']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addawsvolume' );
		$content['onclick'] = false;
		if($this->action === 'addawsvolume' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add an AWS Storage (S3)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function awslog( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.awslog.class.php');
			$controller = new awslog($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['awslog'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['awslog']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'awslog' );
		$content['onclick'] = false;
		if($this->action === 'awslog'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Config AWS with Credentials
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	
	function configaws( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/cloud/class/cloud.configaws.class.php');
			$controller = new cloud_aws_config($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['configaws'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'configaws' );
		$content['onclick'] = false;
		if($this->action === 'configaws' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Reboot resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function reboot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/resource/class/resource.reboot.class.php');
			$controller                  = new resource_reboot($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['reboot'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Reboot';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'reboot' );
		$content['onclick'] = false;
		if($this->action === 'reboot' || $this->action === $this->lang['select']['action_reboot']){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Poweroff resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function poweroff( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/resource/class/resource.poweroff.class.php');
			$controller                  = new resource_poweroff($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['poweroff'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Poweroff';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'poweroff' );
		$content['onclick'] = false;
		if($this->action === 'poweroff' || $this->action === $this->lang['select']['action_poweroff']){
			$content['active']  = true;
		}
		return $content;
	}

	

	//--------------------------------------------
	/**
	 * Edit resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/resource/class/resource.edit.class.php');
			$controller                  = new resource_edit($this->htvcenter, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit' || $this->action === $this->lang['select']['action_edit']){
			$content['active']  = true;
		}
		return $content;
	}
	
	
	//--------------------------------------------
	/**
	 * Load Plugin as new tab
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function __loader() {

		$plugin = $this->response->html->request()->get('rplugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('rcontroller') !== '') {
			$class = $this->response->html->request()->get('rcontroller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';

		// handle new response object
		$response = $this->response->response();
		$response->id = 'rload';
		unset($response->params['resource[sort]']);
		unset($response->params['resource[order]']);
		unset($response->params['resource[limit]']);
		unset($response->params['resource[offset]']);
		unset($response->params['resource_filter']);
		$response->add('rplugin', $plugin);
		$response->add('rcontroller', $name);
		$response->add($this->actions_name, 'load');

		$path   = $this->htvcenter->get('webdir').'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
		$role = $this->htvcenter->role($response);
		$data = $role->get_plugin($class, $path);
		$data->pluginroot = '/plugins/'.$plugin;
		return $data;
	}

}
?>
