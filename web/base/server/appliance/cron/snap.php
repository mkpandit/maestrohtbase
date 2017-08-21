
<?php 


require_once('/usr/share/htvcenter/web/base/class/resource.class.php');
require_once('/usr/share/htvcenter/web/base/class/kernel.class.php');
require_once('/usr/share/htvcenter/web/base/class/virtualization.class.php');
require_once('/usr/share/htvcenter/web/base/class/file.handler.class.php');
require_once('/usr/share/htvcenter/web/base/class/image.class.php');		
require_once('/usr/share/htvcenter/web/base/class/deployment.class.php');	

echo 'snap';

$taskid = $_GET['taskid'];
$resid = $_GET['resid'];
$lvol = $_GET['lvol'];
$volgroup = $_GET['volgroup'];

$storageid = $_GET['storageid'];
$name = $_GET['name'];
$admin = $_GET['user'];
$pass = $_GET['password'];



$deptype = 'kvm-bf-deployment';


snap($name, $deptype, $volgroup, $lvol, $resid, $storageid, $admin, $pass);

$query = 'DELETE FROM `callendar_volgroup_rules` WHERE `id`='.$taskid;
mysql_query($query);

function snap($name, $deptype, $volgroup, $lvol, $resid, $storageid, $admin, $pass) {
			
			/*if($form->get_request('size') > $this->max) {
				$form->set_error('size', sprintf($this->lang['error_size_exeeded'], number_format($this->max, 0, '', '')));
			}*/
			
				
				$uname = $admin;

				
				$command  = '/usr/share/htvcenter/plugins/kvm/bin/htvcenter-kvm snap';
				$command .= ' -t '.$deptype;
				$command .= ' -v '.$volgroup;
				$command .= ' -n '.$lvol;
				if ($deptype == 'kvm-lvm-deployment') {
					// snapshot size is only valid for kvm-lvm-deployment
					$command .= ' -m '.$size;
				}

				$command .= ' -s '.$name;
				$command .= ' -u '.$admin.' -p '.$pass;
				$command .= ' --htvcenter-ui-user '.$uname;
				$command .= ' --htvcenter-cmd-mode background';

				//var_dump($command); die();
				
				$fileobj = new file_handler();
				
				$statfile = '/usr/share/htvcenter/plugins/kvm/web/storage/'.$resid.'.'.$volgroup.'.lv.stat';
				
				if ($fileobj->exists($statfile)) {
					$lines = explode("\n", $fileobj->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$error = sprintf('Already exist', $name);
								}
							}
						}
					}
				}
				// check for image name
				$image = new image();
				$image->get_instance_by_name($name);
				if ((isset($image->id)) && ($image->id > 1)) {
				    $error = sprintf('Allready exist', $name);
				}

				if(isset($error)) {
					$response->error = $error;
				} else {
					if($fileobj->exists($statfile)) {
						$fileobj->remove($statfile);
					}

					
				$resourceo = new resource();
				$resourceo = $resourceo->get_instance_by_id($resid);
				$resourceo->send_command($resourceo->ip, $command);


					while (!$fileobj->exists($statfile)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}

					$created = false;
					$bf_volume_path = "";
					$lines = explode("\n", $fileobj->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$created = true;
									$bf_volume_path = $line[2];
									break;
								}
							}
						}
					}

					if ($created) {
						
					    $image_fields = array();
					    $image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					    $image_fields['image_name'] = $name;
					    $image_fields['image_type'] = $deptype;
					    $image_fields['image_rootfstype'] = 'local';
					    $image_fields['image_storageid'] = $storageid;
					    $image_fields['image_comment'] = "Image Object for volume $name";
					    switch($deptype) {
						case 'kvm-lvm-deployment':
						    $image_fields['image_rootdevice'] = '/dev/'.$volgroup.'/'.$name;
						    break;
						case 'kvm-bf-deployment':
						    $image_fields['image_rootdevice'] = $bf_volume_path;
						    break;
						case 'kvm-gluster-deployment':
						    $image_fields['image_rootdevice'] = "gluster://".$resourceo->ip."/".$volgroup."/".$name;
						    break;
					    }
					    $image = new image();
					    $image->add($image_fields);
						
					} else {
					    echo $error;
					}
				}
			

}
?>