<?php
require 'vendor/autoload.php';

use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Question;
use Mpociot\BotMan\Conversation;

class RemoveVMConversation extends Conversation
{
    private $reply = null;
    public $parameters;
    public $vmname;
    private $config;

    public function __construct($vmname, $config) {
        $params = [
                    'username' => 'hypertask',
                    'icon_url' => 'https://avatars.slack-edge.com/2016-12-29/120882697809_1e723ad86ff82fb50b96_72.png',
                ];

        $this->parameters = $params;
        $this->vmname = $vmname;
        $this->config = $config;
    }
    
    public function question() {
        return $this->ask('are you sure you want to remove vm '.$this->vmname.'?',
            function (Answer $answer) {
                
                $this->reply = strtolower($answer->getText());

                if ( $this->reply == 'yes' || $this->reply == 'yep' || $this->reply == 'sure' || $this->reply == 'of course' || $this->reply == 'yes please' || $this->reply == 'yes, please') {
                    
                    $data = $this->getApi('removevm', '&netname='.$this->vmname);
                    
                    if ($data->done == 'true') {
                            $msg = 'The virtual machine has been removed'.PHP_EOL;
                    } else {
                            $msg = 'Something was wrong, while I tried to remove the VM, sorry!';
                    }
                    
                    $this->say($msg, $this->parameters);
                    
                } 

                if ( $this->reply == 'no' || $this->reply == 'nah' || $this->reply == 'no please' || $this->reply == 'no, please') {
                    
                    $msg = 'Ok, I will not remove the vm, don\'t worry!';
                    
                    $this->say($msg, $this->parameters);
                    
                } 
            });
    }

   
    /**
     * Start the conversation.
     */
    public function run()
    {
        $this->question();
    }


    function getApi($query, $subquery='') {
       
        $url = 'http://'.$this->config['ip'].':'.$this->config['port'].'/htvcenter/base/index.php?plugin=chatbot&cmd='.$query.$subquery;
        $username = $this->config['htaccess']['login'];
        $password = $this->config['htaccess']['password'];

        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$username:$password")
            )
        ));
        
        $data = file_get_contents($url, false, $context);
        $data = json_decode($data);
        return $data;
    }
}


