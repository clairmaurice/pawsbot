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

class WalkConversation extends Conversation
{   
    protected $location;
    protected $botman;
    protected $time;
    protected $answer;
    protected $slack;
    protected $user;

    public function __construct($botman) {
        
        $this->botman = $botman;
        $this->slack = new slack($GLOBALS['token']);
        $users = $this->slack->users->list();
        foreach ($users['members'] as $user) {
           if ($user['name'] == 'clair' || $user['profile']['display_name'] == 'clair' ) {
            $this->user = $user['id'];
            $this->owner = '@'.$user['profile']['display_name'];
            break;
           }
        }

    }

    public function askWalk() {
        $this->ask('Do you want to invite me for a walk?', function(Answer $answer) {
            // Save result
            $this->answer = $answer->getText();

            if ($this->answer == 'yes') {
                $this->askTIme();
            } else {
                $this->say('Ok :(');
            }
            
        });
    }

    public function askTime()
    {
        $this->ask('Sounds good! What time?', function(Answer $answer) {
            // Save result
            $time = $answer->getText();
            
            $user = $this->slack->users->info(["user" => $answer->getUser()]);
            $user = '@'.$user['user']['profile']['display_name'];
            
            $message = $user.' wants to take me for a walk at '.$time.'. Is that okay?';
            
            $response = $this->slack->chat->postMessage([
                "channel" => $this->user,
                "text" => $message,
                //"username" => "Wrapi Bot",
                "as_user" => true,
                "parse" => "full",
                "link_names" => true,
                "unfurl_links" => false,
                "unfurl_media" => false
            ]);
            
            $list = $this->slack->im->list(['token' => $GLOBALS['token']]);
            foreach ($list['ims'] as $l) {
                if ($l['user'] == $this->user) {
                    $channel = $l['id'];
                    break;
                }
            }

            while (1) {
                sleep(3);
                $response = $this->slack->im->history([
                    'token' => $GLOBALS['token'], //'xoxp-204778000530-401149539955-401517894181-edf0baef639cc38111b2a5690c8e8d4d', //$GLOBALS['token'],
                    'channel' => $channel,
                    'count' => 1,
                    //'unreads' => true,
                ]);

                if ($response["messages"][0]['text'] == 'yes') {
                    $answ = 'yeah that\'d be great, see you then!';
                    $this->say($answ);
                    break;
                } elseif($response["messages"][0]['text'] == 'no') {
                    $answ ='Bummer that time doesn\'t work. Maybe check with '.$this->owner.' what would be better?';
                    $this->say($answ);
                    break;
                } else {
                    continue;
                }

            }
           
        });
    }


    public function run()
    {
        // This will be called immediately
        //$this->say('Hello!');
        $this->askWalk();
    }

   
}