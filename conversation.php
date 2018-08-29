<?php
require 'vendor/autoload.php';

use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Conversation;
use Mpociot\BotMan\Messages\Message;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Button;
use Mpociot\BotMan\Question;
use React\EventLoop\Factory;
use wrapi\slack\slack;



class HelloConversation extends Conversation
{   
    protected $location;
    protected $time;
    protected $answer;
    protected $slack;
    protected $botman;
    protected $owner;
    protected $user;

    public function __construct($botman) {
        
        $this->botman = $botman;
        $this->slack = new slack($GLOBALS['token']);
        $users = $this->slack->users->list();
        foreach ($users['members'] as $user) {
           if ($user['name'] == 'sergei' || $user['profile']['display_name'] == 'sergei' ) {
            $this->user = $user['id'];
            $this->owner = '@'.$user['profile']['display_name'];
            break;
           }
        }

    }

    public function askTell() {
        $this->ask('Would you like me to tell everyone I’m here?', function(Answer $answer) {
            // Save result
            $this->answer = $answer->getText();

            if ($this->answer == 'yes') {
                $this->askLocation();
            } else {
                $this->say('Ok! Let\'s to be quiet in this case');
            }
            
        });
    }

    public function askLocation()
    {
        $this->ask('Okay, where am I located today? (e.g. under desk on level3)', function(Answer $answer) {
            // Save result
            $this->location = $answer->getText();
            $this->say('Great, thanks!');
            $this->tellAll();
               
        });
    }


    public function tellAll() {
        $greetrand = [
            'Here today!',
            'Hello to all.',
            'Hey, mates!',
            'Hi, me is here.',
        ];
        $rand = random_int(0, 3);
        
        $mes = $greetrand[$rand].' If anyone\'s having a ruff day today, I\'m with '.$this->owner.' in '.$this->location.'. Open for tummy rubs between 9-5pm.';

        $response = $this->slack->channels->join([
            'token' => $GLOBALS['token'],
            'name' => '#general',
        ]);

       
        $response = $this->slack->chat->postMessage([
            "channel" => "#general",
            "text" => $mes,
            //"username" => "Wrapi Bot",
            "as_user" => true,
            "parse" => "full",
            "link_names" => true,
            "unfurl_links" => false,
            "unfurl_media" => false
        ]);

        //$this->say($mes);
    }

    public function run()
    {
        // This will be called immediately
        $this->say('Hello!');
        $this->askTell();
    }

   
}