<?php
require 'vendor/autoload.php';

use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Question;
use Mpociot\BotMan\Conversation;
use Mpociot\BotMan\Button;

class CPUConversation extends Conversation{
	private $question = null;
 	private $answer = null;
	public $parameters;
	protected $bot;

	public function __construct($botman) {
		$params = ['username' => 'maestro','icon_url' => 'https://ca.slack-edge.com/T0DJD2SMR-U5Z05NUAU-e213c5260456-512',];
		$this->parameters = $params;
		$this->bot = $botman;
	}

	public function askQuestion() {
		return $this->ask('Sure, which server, can you please give me more details? ',
		function (Answer $answer) {
			$this->reply = strtolower($answer->getText());
			if ( $this->reply != "") {
				$server_name = $this->reply;
				$data = getApi('cpuinfo', '&servername='.$server_name);
				if (count($data) == 0) {
					$msg = 'No CPU information found :('.PHP_EOL;
				} else {
					$data = (array) $data;
					foreach ($data as $vm) {
						$msg .= $server_name . ' has got '.$vm->cpu_number . ' CPU(s)'.PHP_EOL;
					}
				}
				$this->say($msg, $this->parameters);
			} else {
				$msg = 'We are still working to get individual CPU information. Please, check back later. Thank you!';
				$this->say($msg, $this->parameters);
			}
		});
	}

	public function run(){
		$this->askQuestion();
	}
}

class createVM extends Conversation{
	private $question = null;
 	private $answer = null;
	public $parameters;
	protected $bot;
	
    public function __construct($botman) {
		$params = [
			'username' => 'maestro',
			'icon_url' => 'https://ca.slack-edge.com/T0DJD2SMR-U5Z05NUAU-e213c5260456-512',
		];
		
		$this->parameters = $params;
		$this->bot = $botman;
	}
	
	public function askQuestion() {
		$this->ask('Sure! Please provide the following information:'.PHP_EOL.'VM Name, VM Size, VNC Password and Host name, separated by comma (",")',
		function (Answer $answer) {
			$this->reply = strtolower($answer->getText());
			if ( $this->reply != "") {
				$temp = explode(',', $this->reply);
				if(count($temp) != 4){
					$msg .= "You supplied bad parameters. Please follow instructions properly. VM creation terminated, sorry :( ".PHP_EOL;
				} else {
					$params = serialize(array('vmname' => trim($temp[0]), 'vmsize' => trim($temp[1]), 'vncpass' => trim($temp[2]), 'hostname' => trim($temp[3]) ) );
					$msg .= $temp[0] . ' virtual machine of '.$temp[1].' size will be created on '.$temp[3].' and the VNC password would be '.$temp[2].PHP_EOL.'Please wait while the VM is created ...'.PHP_EOL;
					//$msg .= $params.PHP_EOL;
					$data = getApi('createvm', '&params='.$params);
					if(count($data) == 0) {
						$msg .= 'No VM was created :('.PHP_EOL;
					} else {
						$data = (array) $data;
						foreach($data as $ab){
							$msg .= $ab.PHP_EOL;
						}
					}
				}
				$this->say($msg, $this->parameters);
			} else {
				$msg = 'VM can not be created without a valid name!'.PHP_EOL;
				$this->say($msg, $this->parameters);
			}
		});
	}
	
	public function run(){
		$this->askQuestion();
	}
}

class MemoryConversation extends Conversation{
	private $question = null;
 	private $answer = null;
	public $parameters;
	protected $bot;

	public function __construct($botman) {
		$params = ['username' => 'maestro','icon_url' => 'https://ca.slack-edge.com/T0DJD2SMR-U5Z05NUAU-e213c5260456-512',];
		$this->parameters = $params;
		$this->bot = $botman;
	}

	public function askQuestion() {
		return $this->ask('Sure, which server, can you please give me the server name? ',
		function (Answer $answer) {
			$this->reply = strtolower($answer->getText());
			if ( $this->reply != "") {
				$server_name = $this->reply;
				$data = getApi('memoryinfo');
				$data = (array) $data;
				if (count($data) == 0) {
					$msg = 'No memory information found :( SORRY'.PHP_EOL;
				} else {
					foreach ($data as $vm) {
						$temp = explode(":",$vm);
						if(strtolower(trim($temp[0])) == strtolower(trim($server_name))){
							$msg .= $server_name . ' has got '.$temp[1] . ' MB of Memory'.PHP_EOL;
						}
					}
				}
				$this->say($msg, $this->parameters);
			} else {
				$msg = 'We are still working to get individual CPU information. Please, check back later. Thank you!';
				$this->say($msg, $this->parameters);
			}
		});
	}

	public function run(){
		$this->askQuestion();
	}
}