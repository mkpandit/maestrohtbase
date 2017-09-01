<?php
require 'vendor/autoload.php';
$data = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config');
$config = unserialize($data);

use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\BotMan;
use React\EventLoop\Factory;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Question;
use Mpociot\BotMan\Messages\Message;

$loop = Factory::create();
$botman = BotManFactory::createForRTM([
    'slack_token' => $config['slack_token'],
], $loop);

include 'politness.php';
include 'education.php';
include 'learn.php';
include 'conversations.php';
include 'RemoveVMConversation.php';

$botman->hears('*can you tell me about storage*||storage||*storage space*||How much storage*||How is storage*||how much storage*||how is storage*||*hdd info*||*info hdd*||*info about hdd*||*storage info*||*about storage*', function(BotMan $botman) {
	$data = getApi('hddinfo');

	if ($data->spercent < 70) {
		$cond = 'well';
	} else {
		$cond = 'not so well';
	}
	$msg = 'We are doing '.$cond.' on storage today.'.PHP_EOL;
	$msg .=''.PHP_EOL;
	$msg .='We have the following:'.PHP_EOL;
	$msg .=''.PHP_EOL;
	$msg .=$data->sfree.' of storage space available'.PHP_EOL;
	$msg .=$data->sused.' of storage being used'.PHP_EOL;

	$botman->reply($msg);
	
});

$botman->hears('image||What is the image available on Maestro*', function(BotMan $botman) {
	$data = getApi('image');
	
	$msg = 'Here is the information about Images in Maestro system:'.PHP_EOL;
	
	if (count($data) == 0) {
		$msg .= 'You have not got images :('.PHP_EOL;
	} else {
		$data = (array) $data;
		$msg .= 'You have got '.count($data).' images :)'.PHP_EOL;
		foreach ($data as $im) {
			$msg .= '*Id:* '.$im->image_id.PHP_EOL;
			$msg .= '*Name:* '.$im->image_name.PHP_EOL;
			$msg .= '*Type:* '.$im->image_type.PHP_EOL; 
		}
	}
	$botman->reply($msg);
	
});

$botman->hears('*image {imagename}*', function(BotMan $botman, $imagename) {
	$data = getApi('image');
	if (count($data) == 0) {
		$msg = 'No, I do not know this image.'.PHP_EOL;
	} else {
		$data = (array) $data;
		foreach ($data as $im) {
			if($imagename == $im->image_name){
				$msg .= "This image is available on Maestro System.".PHP_EOL;
				$msg .= 'Its ID is: '.$im->image_id. ' and '.PHP_EOL;
				$msg .= 'Its type is: '.$im->image_type.PHP_EOL; 
			}
		}
		if(empty($msg)){
			$msg = 'No, I do not know this image.'.PHP_EOL;
		}
	}
	$botman->reply($msg);
});

$botman->hears('vm*||*vm info*||*VM*||*about VM*||*VM info*||*about vm*||*virtual machine*||*virtual machines info*', function(BotMan $botman) {
	$data = getApi('vminfo');
	
	$msg = 'Here is the information about Virtual Machines in Maestro system:'.PHP_EOL;
	
	if ($data->allvmcount == 0) {
		$msg .= 'You have not got virtual machines :('.PHP_EOL;
	} else {
		$msg .= 'You have '.$data->allvmcount.' VMs'.PHP_EOL;
		if ($data->activeallvm == 0 && $data->inactiveallvm == 0) {
			$msg .= 'All of them are not active'.PHP_EOL;
		} else {
			$msg .= 'And states of them are here:'.PHP_EOL;
			$msg .= 'Active: '.$data->activeallvm.PHP_EOL;
			$msg .= 'Inctive: '.$data->inactiveallvm.' of free space'.PHP_EOL;
		}
	}

	$botman->reply($msg);
	
});

$botman->hears('How many virtual machine we have available?||*how many virtual machine*', function(BotMan $botman) {
	$data = getApi('vminfo');
	$msg = '';
	if ($data->allvmcount == 0) {
		$msg .= 'You have not got virtual machines :('.PHP_EOL;
	} else {
		$msg .= 'You have '.$data->allvmcount.' VMs'.PHP_EOL;
	}
	$botman->reply($msg);
});

$botman->hears('How many virtual machine is activated?||activated virtual machine', function(BotMan $botman) {
	$data = getApi('vminfo');
	$msg = '';
	if ($data->allvmcount == 0) {
		$msg .= 'You have not got virtual machines :('.PHP_EOL;
	} else {
		if ($data->activeallvm == 0 && $data->inactiveallvm == 0) {
			$msg .= 'None of them are active'.PHP_EOL;
		} else {
			$msg .= 'You have '.$data->activeallvm. ' actived VM(s)'.PHP_EOL;
		}
	}
	$botman->reply($msg);
});

$botman->hears('How many virtual machine is on?', function(BotMan $botman) {
	$data = getApi('vminfo');
	$msg = '';
	if ($data->allvmcount == 0) {
		$msg .= 'You have not got virtual machines :('.PHP_EOL;
	} else {
		if ($data->activeallvm == 0 && $data->inactiveallvm == 0) {
			$msg .= 'None of them are active'.PHP_EOL;
		} else {
			$msg .= 'You have '.$data->activeallvm. ' VM(s) on'.PHP_EOL;
		}
	}
	$botman->reply($msg);
});

$botman->hears('How many virtual machine is off?', function(BotMan $botman) {
	$data = getApi('vminfo');
	$msg = '';
	if ($data->allvmcount == 0) {
		$msg .= 'You have not got virtual machines :('.PHP_EOL;
	} else {
		if ($data->activeallvm == 0 && $data->inactiveallvm == 0) {
			$msg .= 'None of them are active'.PHP_EOL;
		} else {
			$msg .= 'You have '.$data->inactiveallvm. ' VM(s) off'.PHP_EOL;
		}
	}
	$botman->reply($msg);
});

$botman->hears('*appliance list*||*VMs list*||*virtual machine list*||*list of VMs||*list of virtual machines*', function(BotMan $botman) {
	$data = getApi('vmlist');
	
	$msg = 'Here is the list of Virtual Machines in Maestro system:'.PHP_EOL.PHP_EOL;

	if (count($data) == 0) {
		$msg .= 'You have not got virtual machines :('.PHP_EOL;
	} else {
		$data = (array) $data;
		foreach ($data as $vm) {
			$msg .= '*Id:* '.$vm->appliance_id.PHP_EOL;
			$msg .= '*Name:* '.$vm->appliance_name.PHP_EOL;
			$msg .= '*Type:* '.$vm->appliance_virtualization.PHP_EOL.PHP_EOL;
		}
		
	}

	$botman->reply($msg);
	
});

$botman->hears('*the servers*||*our servers*||*servers info*||*server\'s info*||*info from servers*||*about servers*', function(BotMan $botman) {
	$data = getApi('serversinfo');

	$msg = 'I see we have:'.PHP_EOL.PHP_EOL;
	if ($data->hostsinfo->hosts > 1) {
		$s='s';
	}
	$msg .= $data->hostsinfo->hosts.' host'.$s.PHP_EOL;
	$msg .= $data->vminfo->activeallvm.' virtual machines running'.PHP_EOL;
	$msg .= $data->vminfo->inactiveallvm.' virtual machines stopped'.PHP_EOL;
	$msg .= $data->storageinfo->sused.' of storage space utilized'.PHP_EOL;
	$msg .= $data->storageinfo->sfree.' of storage space available'.PHP_EOL;

	$botman->reply($msg);
});


$botman->hears('*hosts info*||*about hosts*||*many hosts*', function(BotMan $botman) {
	$data = getApi('hostsinfo');
	
	if ($data->hosts > 1) {
		$msg = 'We have '.$data->hosts.' hosts';
	} else {
		$msg = 'We have only '.$data->hosts.' host';
	}

	if ($data->hosts == 0) {
		$msg = 'There are no hosts registered at the moment';
	}

	$botman->reply($msg);
});


$botman->hears('*total memory||system memory||maestro memory', function(BotMan $botman) {
	$data = getApi('controllerinfo');
	if($data){
		$msg = 'Controller Memory:'.PHP_EOL;
		$msg .= '- '.$data->memory->available.' MB available'.PHP_EOL;
		$msg .= '- '.$data->memory->used.' MB used'.PHP_EOL.PHP_EOL;
	}
	$distributedData = getApi('memoryinfo');
	if($distributedData){
		$msg .= 'Distributed Memory:'.PHP_EOL;
		$distributedData = (array) $distributedData;
		foreach($distributedData as $v) {
			$msg .= " - ".$v. " MB".PHP_EOL;
		}
	}
	$botman->reply($msg);
});

$botman->hears('*controller*', function(BotMan $botman) {
	$data = getApi('controllerinfo');
	
	if ($data->conditions == 0) {
		$cond = 'well';	
	} else {
		$cond = 'not so well';
	}

	$msg = 'HTController is running '.$cond.'. Here is what I see:'.PHP_EOL.PHP_EOL;
	$msg .= '*Memory*:'.PHP_EOL;
	$msg .= '- '.$data->memory->available.' MB available'.PHP_EOL;
	$msg .= '- '.$data->memory->used.' MB used'.PHP_EOL.PHP_EOL;
	$msg .= '*Swap*:'.PHP_EOL;
	$msg .= '- '.$data->swap->available.' MB available'.PHP_EOL;
	$msg .= '- '.$data->swap->used.' MB used'.PHP_EOL.PHP_EOL;
	$msg .= '*Storage*:'.PHP_EOL;
	$msg .= '- '.$data->storage->available.' available'.PHP_EOL;
	$msg .= '- '.$data->storage->used.' used'.PHP_EOL;

	$botman->reply($msg);
});

$botman->hears('*networks*', function(BotMan $botman) {
	$data = getApi('networksinfo');
	
	if (count($data->names) > 0) {
		$msg = 'We currently have the following networks created:'.PHP_EOL.PHP_EOL;
		foreach($data->names as $network) {
			$msg .= ' - '.$network.PHP_EOL;
		}
	} else {
		$msg = 'We have not got any created networks'.PHP_EOL;
	}
	
	$botman->reply($msg);
});



$botman->hears('*IP range for network {name}||*ip range for network {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('iprange', '&netname='.$name);
	if ($data->range->first != null) {
		$msg = 'These are the details I see for network '.$name.':'.PHP_EOL.PHP_EOL;
		$msg .= '- *Name*: '.$name.PHP_EOL;
		$msg .= '- *First IP*: '.$data->range->first.PHP_EOL;
		$msg .= '- *Last IP*: '.$data->range->last.PHP_EOL;
	} else {
		$msg = 'We have not got network with "'.$name.'" name';
	}
	$botman->reply($msg);
});

$botman->hears('*IPs do we have available on network {name}||*ips do we have available on network {name}||*ip on network {name}||*IPs on network {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('netipnumber', '&netname='.$name);
	if ($data->range->first != null) {
		$msg = 'As of now, we have '.$data->ip_available.' IPs available'.PHP_EOL;
	} else {
		$msg = 'We have not got network with "'.$name.'" name'.PHP_EOL;
	}
	$botman->reply($msg);
});

$botman->hears('*details for network {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('networkdetails', '&netname='.$name);
	if ($data->range->first != null) {
		$msg = 'These are the details I see for network '.$name.':'.PHP_EOL.PHP_EOL;
		$msg .= '- *Name*: '.$name.PHP_EOL;

		if (!empty($data->range->first) && !empty($data->range->last)) {
			$msg .= '- *First IP*: '.$data->range->first.PHP_EOL;
			$msg .= '- *Last IP*: '.$data->range->last.PHP_EOL;
		}

		foreach ( $data->details as $key => $value) {
			if (!empty($value) && $value != $name) {
				$key = str_replace('ip_mgmt_', '', $key);
				$key = str_replace('_', ' ', $key);
				$msg .= '- *'.$key.'*: '.$value.PHP_EOL;
			}
		}
	} else {
		$msg = 'We have not got network with "'.$name.'" name';
	}

	$botman->reply($msg);
});

$botman->hears('*start vm {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('checkvm', '&netname='.$name);
	if ($data->state == 'stopped') {
		$data = getApi('startvm', '&netname='.$name);

		if ($data->done == 'true') {
			$msg = 'For sure, starting vm '.$name.PHP_EOL.PHP_EOL;
			$msg .= 'The virtual machine will be running soon'.PHP_EOL;
		} else {
			$msg = 'Something was wrong, while I tried to start the VM, sorry!';
		}
	} else {
		if (empty($data->state)) {
			$msg = 'There are no vm "'.$name.'" at the moment';
		} else {
			$msg = 'VM "'.$name.'" is running as requested';
		}
	}
	
	$botman->reply($msg);
});

$botman->hears('*remove vm {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('checkvm', '&netname='.$name);
	
	if (empty($data->state)) {
			$msg = 'There are no vm "'.$name.'" at the moment';
			$botman->reply($msg);
	} else {
		if ($data->state != 'active') {
			$data = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config');
			$config = unserialize($data);
			$botman->startConversation(new RemoveVMConversation($name, $config));
		} else {
			$msg = 'The VM is active, please, shut it down first';
			$botman->reply($msg);
		}
	}
});

$botman->hears('*stop vm {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('checkvm', '&netname='.$name);
	if ($data->state == 'active') {
		$data = getApi('stopvm', '&netname='.$name);
		if ($data->done == 'true') {
			$msg = 'Yep, stopping vm '.$name.PHP_EOL.PHP_EOL;
			$msg .= 'The virtual machine will be inactive soon'.PHP_EOL;
		} else {
			$msg = 'Something was wrong, while I tried to stop the VM, sorry!';
		}
	} else {
		if (empty($data->state)) {
			$msg = 'There are no vm "'.$name.'" at the moment';
		} else {
			$msg = 'VM "'.$name.'" is inactive as requested';
		}
	}
	$botman->reply($msg);
});

$botman->hears('*state of vm {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('checkvm', '&netname='.$name);
	
	if (empty($data->state)) {
			$msg = 'There are no vm "'.$name.'" at the moment';
	} else {
		$msg = 'The vm "'.$name.'" is in '.$data->state.' state';
	}

	$botman->reply($msg);
});

$botman->hears('*enable HA for vm {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('checkvm', '&netname='.$name);
	if (empty($data->state)) {
			$msg = 'There are no vm "'.$name.'" at the moment';
	} else {
		$data = getApi('haenable', '&netname='.$name);
		if ($data->enable == 'true!') {
			$msg = 'For sure, VM "'.$name.'" now has high availability activated';
		} else {
			$msg = 'Some problems was detected, please, do it manually or install the plugin first!';
		}
	}
	$botman->reply($msg);
});

$botman->hears('*VMs are using network {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('vmnetwork', '&netname='.$name);

	$datacheck = getApi('netipnumber', '&netname='.$name);
	if ($datacheck->range->first != null) {

		if (is_array($data->vms) && $data->vms != 'NULL') {
			$msg = 'Currently, the following virtual machines are using network "'.$name.'":'.PHP_EOL.PHP_EOL;

			foreach ($data->vms as $vm) {
				$msg .= ' - '.$vm.PHP_EOL; 
			}
		} else {
			$msg = 'This network don\'t have any VMs'.PHP_EOL;
		}
	} else {
		$msg = 'We have not got network with "'.$name.'" name';
	}

	$botman->reply($msg);
});



$botman->hears('*disable HA for vm {name}', function(BotMan $botman, $name) {
	$name = str_replace('?', '', $name);
	$data = getApi('hadisable', '&netname='.$name);
	if ($data->enable == 'false!') {
		$msg = 'Of course, VM "'.$name.'" now has high availability disabled';
	} else {
		$msg = 'Some problems was detected, please, do it manually or install the plugin first!';
	}
	$botman->reply($msg);
});

$botman->hears('*charge back we have for {month} for user {user}', function(BotMan $botman, $month, $user) {
	$user = str_replace('?', '', $user);

	if ($user != '') {
		$monthquery = humanMonth($month);

		if ($monthquery != 'wrong') {
			$data = getApi('cloudcharge', '&user='.$user.'&month='.$monthquery);
			
			if (!empty($data->all)) {
				$msg = 'The total charge back for '.$month.' and user '.$user.' is: *'.$data->all.'*'.PHP_EOL.PHP_EOL;
				$msg .= 'Details are here:'.PHP_EOL;
				foreach ($data as $key => $value) {
					if ($key != 'all') {
						$msg .= ' - *'.$key.'*: '.$value.PHP_EOL;
					}
				}
			} else {
				$msg = 'Can\'t take information about. Install Cloud-Fortis plugin first';
			}
		} else {
			$msg = 'Your query is wrong, looks, like month is invalid';
		}
	}
	$botman->reply($msg);
});

$botman->hears('*charge back we have for {month}', function(BotMan $botman, $month) {
	$month = str_replace('?', '', $month);
	$user = 'All';
	$monthquery = humanMonth($month);

	if ($monthquery != 'wrong') {
		$data = getApi('cloudcharge', '&user='.$user.'&month='.$monthquery);

		if (!empty($data->all)) {
			$msg = 'The total charge back for '.$month.' is: *'.$data->all.'*'.PHP_EOL.PHP_EOL;
			$msg .= 'Details are here:'.PHP_EOL;
			foreach ($data as $key => $value) {
				if ($key != 'all') {
					$msg .= ' - *'.$key.'*: '.$value.PHP_EOL;
				}
			}
		} else {
			$msg = 'Can\'t take information about. Install Cloud-Fortis plugin first';
		}
	} else {
		return;
	}
	$botman->reply($msg);
});

$botman->hears('*most consumed resource on {month}', function(BotMan $botman, $month) {
	$month = str_replace(' on Fortis', '', $month);
	$month = str_replace(' on fortis', '', $month);
	$month = str_replace('?', '', $month);

	$user = 'All';
	$monthquery = humanMonth($month);

	if ($monthquery != 'wrong') {
		$data = getApi('cloudcharge', '&user='.$user.'&month='.$monthquery);

		if (!empty($data->all)) {
			$max = 0;
			$cost = 0;
			foreach ($data as $key => $value) {
				if ($key != 'all') {
					$val = str_replace('$', '', $value);
					$val = (float) $val;
					if ($val > $max) {
						$resource = $key;
						$max = $val;
						$cost = $value;
					}
				}
			}

			if ( $max != 0 ) {
				$msg = 'As far as I can see *'.$resource.' ('.$cost.')* was the most consumed resource in Fortis for the *month of '.$month.'*'.PHP_EOL;
			} else {
				$msg = 'All resources have not been billed'.PHP_EOL;
			}
			
		} else {
			$msg = 'Can\'t take information about. Install Cloud-Fortis plugin first';
		}
	} else {
		return;
	}
	$botman->reply($msg);
});



function getApi($query, $subquery='') {
	global $config;
	//$url = 'http://'.$config['ip'].':'.$config['port'].'/htvcenter/base/index.php?plugin=chatbot&cmd='.$query.$subquery;
	$url = 'http://'.$config['ip'].'/htvcenter/base/index.php?plugin=chatbot&cmd='.$query.$subquery;
	$username = $config['htaccess']['login'];
	$password = $config['htaccess']['password'];

	$context = stream_context_create(array(
    	'http' => array(
        	'header'  => "Authorization: Basic " . base64_encode("$username:$password")
    	)
	));
	
	$data = file_get_contents($url, false, $context);
	$data = json_decode($data);
	return $data;
}

function humanMonth($month) {
	switch ($month) {
		case 'january':
			$month = 'Jan';
			break;

		case 'february':
			$month = 'Feb';
			break;

		case 'march':
			$month = 'Mar';
			break;

		case 'april':
			$month = 'Apr';
			break;

		case 'may':
			$month = 'May';
			break;

		case 'june':
			$month = 'Jun';
			break;

		case 'july':
			$month = 'Jul';
			break;

		case 'august':
			$month = 'Aug';
			break;

		case 'september':
			$month = 'Sep';
			break;

		case 'october':
			$month = 'Oct';
			break;

		case 'november':
			$month = 'Nov';
			break;

		case 'december':
			$month = 'Dec';
			break;

		
		
		default:
			$month = 'wrong';
			break;
	}

	return $month;
}

function getInactiveVMsData() {
	$data = getApi('inactivevms');

	if ($data == null) {
		return false;
	}

	$data = (array) $data;
	
	$count = 0;
	$days = 0;
	$prefix = 'days';

	if ($data != null) {
		foreach ($data as $vm) {
			$day = (int) $vm->days;
			$days = $days + $day;
			$count = $count + 1;
		}
	}
	

	$days = $days/$count;

	if ($days%$count != 0) {
		$res['more'] == true;
	} else {
		$res['more'] = false;
	}

	$res['period'] = $days;

	if ($days > 7) {
		$weeks = $days/7;
		$weeks = round($weeks);
		$prefix = 'weeks';
		$res['period'] = $weeks;
	}

	$res['amount'] = $count;
	$res['prefix'] = $prefix;
	
	return $res;
}


checkalerts();

function checkalerts() {
	$data = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config');
	$config = unserialize($data);
	$loop = Factory::create();
	$botman = BotManFactory::createForRTM([
    	'slack_token' => $config['slack_token'],
	], $loop);

	

	$data = getApi('checkalerts');
	$and = 0;
	$cond = 'none';

	if ($data->memory != 'ok') {
		$and = 1;
		$cond = 'memory';
		$part = $data->memory.'% of memory';
	}

	if ($data->hdd != 'ok') {
		$and = $and + 1;

		if ($and == 2) {
			$cond = 'both';
			$part = $part.' and '.$data->hdd.'% of storage';
		} else {
			$cond = 'storage';
			$part = $data->hdd.'% of storage';
		}
	}

	if ($cond != 'none') {
		$data = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/alerts');
		$alertconfig = unserialize($data);
		$lasttime = $alertconfig[$cond];
		$hour = false;
		if ($lasttime == NULL) {
			$hour = true;
		} else {
			$diff = time() - $lasttime;
			if ($diff >= 3600) {
				$hour = true;
			}
		}

		
		if ($hour == true) {
			$msg = 'Hey, we have used '.$part.', so I believe we will need to do some clean up.'.PHP_EOL.PHP_EOL;
			$inactive = getInactiveVMsData();
			if ($inactive != false) {
				$more = '';
				if ($inactive['more'] == true) {
					$more = ' more than';
				}

				$msg .= 'I see that we have '.$inactive['amount'].' virtual machine stopped, They have been stopped for'.$more.' '.$inactive['period'].' '.$inactive['prefix'].'. Should we maybe try to remove those to free up resources?'.PHP_EOL;
			}
			$userid = $botman->getUserIdByName($config['useralert']);
			$botman->justsay($msg, $userid);
			$alertconfig[$cond] = time();
			file_put_contents('/usr/share/htvcenter/plugins/chatbot/thebot/alerts', serialize($alertconfig));
		}
	}	
}

$botman->hears('*training*', function(BotMan $botman) {
	$botman->startConversation(new LearnConversation($botman));
});


$botman->hears('*cpu*||*can you tell me about cpu*', function(BotMan $botman) {
	$botman->startConversation(new CPUConversation($botman));
});

$botman->hears('memory||*can you tell me about memory*', function(BotMan $botman) {
	$botman->startConversation(new MemoryConversation($botman));
});

$botman->hears('*create vm*||*createvm*||*hey create a vm*||create a vm||*vm create*||please create a vm||*can you create a vm*||create virtual machine', function(BotMan $botman) {
	$botman->startConversation(new createVM($botman));
});

$botman->fallback(function(BotMan $bot) {
    	return $bot->reply('Sorry, I can not answer it.');
});

$loop->addPeriodicTimer(3, function() {
    	$data = file_get_contents('/usr/share/htvcenter/plugins/chatbot/thebot/config');
		$config = unserialize($data);

		if ($config['state'] == 'false') {
			$reason = 'Disabled From Hypertask!'.PHP_EOL;
			die($reason);
		}

		checkalerts();
});

$loop->run();