<?php

require_once('/usr/share/htvcenter/web/base/class/appliance.class.php');
require_once('/usr/share/htvcenter/web/base/class/resource.class.php');
require_once('/usr/share/htvcenter/web/base/class/kernel.class.php');
require_once('/usr/share/htvcenter/web/base/class/virtualization.class.php');
		


echo 'here';
$id = $_GET['id'];
$taskid = $_GET['taskid'];
$admin = $_GET['user'];
$pass = $_GET['password'];
startit($id, $admin, $pass);

$query = 'DELETE FROM `callendar_rules` WHERE `id`='.$taskid;
mysql_query($query);

function startit($id, $admin, $pass) {


					$appliance = new appliance();
					$appliance = $appliance->get_instance_by_id($id);

					$resource = new resource();

					
					if ($appliance->resources <0) {
						// an appliance with resource auto-select enabled
						$appliance_virtualization=$appliance->virtualization;
						$appliance->find_resource($appliance_virtualization);
						$appliance->get_instance_by_id($id);
						if ($appliance->resources <0) {
							$error = sprintf($this->lang['msg_no_resource'], $id);
							echo $error; return;
						}
					}

					$resource->get_instance_by_id($appliance->resources);
					if ($appliance->resources == 0) {
						$error = sprintf($this->lang['msg_always_active'], $id);
						echo $error; return;
					}

					if (!strcmp($appliance->state, "active"))  {
						$error = sprintf($this->lang['msg_already_active'], $id);
						echo $error; return;
					}


					// check that resource is idle
					$app_resource = new resource();
					$app_resource->get_instance_by_id($appliance->resources);
					// resource has ip ?
					if (!strcmp($app_resource->ip, "0.0.0.0")) {
						$error = sprintf($this->lang['msg_reource_no_ip'], $appliance->resources);
						//$response->redirect('?base=resource&resource_filter=&resource_type_filter=&resource_action=edit&resource_id='.$app_resource->id);
						echo $error; return;
					}
					// resource assinged to imageid 1 ?
					if ($app_resource->imageid != 1) {
						$error = sprintf($this->lang['msg_already_active'], $appliance->resources, $id);
						echo $error; return;
					}
					// resource active

					if (strcmp($app_resource->state, "active")) {
						$app_resource_virtualization = new virtualization();
						$app_resource_virtualization->get_instance_by_id($app_resource->vtype);
						// allow waking up physical systems via out-of-band-management plugins
						if (!strstr($app_resource_virtualization->name, "Host")) {
							if ($app_resource_virtualization->id != 1) {
								$error = sprintf($this->lang['msg_already_active'], $appliance->resources, $id);
								echo $error; return;
							}
						}
					}
					// if no errors then we start the appliance
					$kernel = new kernel();
					$kernel->get_instance_by_id($appliance->kernelid);
					// send command to the htvcenter-server
					$resource->send_command("127.0.0.1", "htvcenter_assign_kernel ".$resource->id." ".$resource->mac." ".$kernel->name);
					$appliance->start();
					//$form->remove($this->identifier_name.'['.$key.']');
					
			
	
	}

?>
