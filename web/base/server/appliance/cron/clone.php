
<?php 


require_once('/usr/share/htvcenter/web/base/class/resource.class.php');
require_once('/usr/share/htvcenter/web/base/class/kernel.class.php');
require_once('/usr/share/htvcenter/web/base/class/virtualization.class.php');
require_once('/usr/share/htvcenter/web/base/class/file.handler.class.php');
require_once('/usr/share/htvcenter/web/base/class/image.class.php');		
require_once('/usr/share/htvcenter/web/base/class/deployment.class.php');	

echo 'clone';

$taskid = $_GET['taskid'];
$resid = $_GET['resid'];
$lvol = $_GET['lvol'];
$volgroup = $_GET['volgroup'];

$storageid = $_GET['storageid'];
$name = $_GET['name'];
$admin = $_GET['user'];
$pass = $_GET['password'];

$deptype = 'kvm-bf-deployment';


dublicate($name, $deptype, $volgroup, $lvol, $resid, $storageid, $admin, $pass);

$query = 'DELETE FROM `callendar_volgroup_rules` WHERE `id`='.$taskid;
mysql_query($query);

function dublicate($name, $deptype, $volgroup, $lvol, $resid, $storageid, $admin, $pass) {
			
			
		
			$uname = $admin;

			//$name     = $form->get_request('name');
			$command  = '/usr/share/htvcenter/plugins/kvm/bin/htvcenter-kvm clone';
			$command .= ' -t '.$deptype;
			$command .= ' -v '.$volgroup;
			$command .= ' -n '.$lvol;
			$command .= ' -s '.$name;
			$command .= ' -u '.$admin.' -p '.$pass;
			$command .= ' --caching false';
			$command .= ' --htvcenter-ui-user '.$uname;
			$command .= ' --htvcenter-cmd-mode background';

			

			$fileobj = new file_handler();
			
			$statfile = '/usr/share/htvcenter/web/base/plugins/kvm/storage/'.$resid.'.'.$volgroup.'.lv.stat';
			
			if (file_exists($statfile)) {
				
				$lines = explode("\n", $fileobj->get_contents($statfile));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$check = $line[1];
							if($name === $check) {
								$error = sprintf('Already exists!!!', $name);
							}
						}
					}
				}
			}
			// check for image name
			$image = new image();

			$image->get_instance_by_name($name);
			if ((isset($image->id)) && ($image->id > 1)) {
			    $namer = $name."-scheduler";
			    $image->get_instance_by_name($namer);
				if ((isset($image->id)) && ($image->id > 1)) {
					$error = sprintf('Image Already exists!!!', $name);
				}
			}



			if(isset($error)) {
				echo $error;
			} else {
				$file = '/usr/share/htvcenter/web/base/plugins/kvm/storage/'.$resid.'.lvm.'.$name.'.sync_progress';

				if(file_exists($file)) {

					$fileobj->remove($file);
				}
				$root_device_ident = '/usr/share/htvcenter/web/base/plugins/kvm/storage/'.$resid.'.'.$name.'.root_device';
				if($fileobj->exists($root_device_ident)) {
					$fileobj->remove($root_device_ident);
				}

				$resourceo = new resource();
				$resourceo = $resourceo->get_instance_by_id($resid);
				$resourceo->send_command($resourceo->ip, $command);
				
				

				while (!file_exists($file))
				{
								
				  usleep(10000);
				  clearstatcache();
				}
				// wait for the root-device identifier
				
				while (!file_exists($root_device_ident))
				{ 
				  usleep(10000);
				  clearstatcache();
				} 

				var_dump($root_device_ident);
				$root_device = trim($fileobj->get_contents($root_device_ident));
				var_dump($root_device);
				$fileobj->remove($root_device_ident);

				//$tables = $this->htvcenter->get('table');
				$image_fields = array();
				$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields['image_name'] = $name;
				$image_fields['image_type'] = $deptype;
				$image_fields['image_rootfstype'] = 'local';
				$image_fields['image_storageid'] = $storageid;
				$image_fields['image_comment'] = "Image Object for volume $name";
				$image_fields['image_rootdevice'] = $root_device;
				
				$image = new image();
				$image->add($image_fields);
				//$response->image_id = $image_fields["image_id"];
				//$response->msg = sprintf($this->lang['msg_cloned'], $this->lvol, $name); */
			}

}
?>