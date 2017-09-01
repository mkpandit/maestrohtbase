<?php

require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
require_once('/usr/share/htvcenter/web/base/class/resource.class.php');
require_once('/usr/share/htvcenter/web/base/class/kernel.class.php');
	

echo 'here';
$id = $_GET['id'];
$taskid = $_GET['taskid'];
$admin = $_GET['user'];
$pass = $_GET['password'];

stopit($id, $admin, $pass);

$query = 'DELETE FROM `callendar_rules` WHERE `id`='.$taskid;
mysql_query($query);


function stopit($id, $admin, $pass) {

		$appliance = new appliance();		
		$appliance = $appliance->get_instance_by_id($id);

		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		
		if ($appliance->resources == 0) {
			$error = sprintf($this->lang['msg_always_active'], $id);				
		}
		
		// if no errors then we stop the appliance
		$kernel = new kernel();
		$kernel->get_instance_by_id($appliance->kernelid);
					// send command to the htvcenter-server
		$resource->send_command("127.0.0.1", "htvcenter_assign_kernel ".$resource->id." ".$resource->mac." default");
		$appliance->stop();
		//$form->remove($this->identifier_name.'['.$key.']');
		removeit($id);
}

function removeit($id) {

		$appliance = new appliance();
		$appliance = $appliance->get_instance_by_id($id);
					// allow removing active htvcenter appliances
		if ($appliance->resources != 0) {
						// check that the appliance is stopped before
			if ($appliance->state == 'active') {
				$error = sprintf($this->lang['msg_still_active'], $id);
				echo $error; return;
			}
		}

		$appliance->remove($id);
		//$form->remove($this->identifier_name.'['.$key.']');
		//$message[] = sprintf($this->lang['msg'], $id);
						
			
}

?>
