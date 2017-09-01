<?php
require 'vendor/autoload.php';

use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Question;
use Mpociot\BotMan\Conversation;
use Mpociot\BotMan\Button;

class LearnConversation extends Conversation
{
    private $question = null;
    private $answer = null;
    public $parameters;
    protected $bot;

    public function __construct($botman) {
        $params = [
                    'username' => 'maestro',
                    'icon_url' => 'https://avatars.slack-edge.com/2016-12-29/120882697809_1e723ad86ff82fb50b96_72.png',
                ];

        $this->parameters = $params;
        $this->bot = $botman;
    }
    
    public function askQuestion() {
        $this->ask('Ok! I am ready to learn new phrases. On what message or pattern I should to have reaction?', function (Answer $answer) {    
            $this->question = strtolower($answer->getText());
            if ($this->question != null) {
                $this->say('Got it.', $this->parameters);
                $this->askAnswer();
            } else {
                $this->say('Sorry, something is wrong with your question, try to teach me again!', $this->parameters);
            } 
        });
    }

    public function askAnswer() {
        $this->ask('And what answer should to be on this?', function (Answer $answer) {
			$this->answer = strtolower($answer->getText());
            if ($this->answer != null) {
                $this->say('Understood!', $this->parameters);
                $this->remember($this->question, $this->answer);
            } else {
                $this->say('Sorry, something is wrong with the answer, try to teach me again!', $this->parameters);
            }
        });
    }

   
   public function remember($question, $answer) {
        if ($question != null && $answer != null) {
            
            $talks = $this->bot->driverStorage()->get();
            $talks['talks'][] = ['question' => $question, 'answer' => $answer];
            $this->bot->driverStorage()->save([
                 'talks' => $talks['talks'],
            ]);
            $this->say('Now it all in my memory! Thank you for the lesson.', $this->parameters);
            $talks = $this->bot->driverStorage()->get();
            var_dump($talks);
        } else {
            $this->say('Try to teach me later, something wrong with your question or answer!', $this->parameters);
        }
   }

    
    public function run()
    {
        $this->askQuestion();
    }
}


