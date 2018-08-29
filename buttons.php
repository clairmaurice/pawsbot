<?php
require 'vendor/autoload.php';

use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Question;
use Mpociot\BotMan\Conversation;
use Mpociot\BotMan\Button;

class ButtonsAction extends Conversation
{
    public $username;

    public function __construct($username) {
        $this->username = $username;
    }

    public function askForFirstActions() {
        
        $question = Question::create('Hi *'.$this->username.'!* wags tail I\'m Paws, the slack bot making office pets easy!
I little birdy told me you have a pet you like to - or want to - bring to the office. I can make it easier and fun! Would you like to:')
            ->fallback('Unable to make a photo')
            ->callbackId('create_webcam')
            ->addButtons([
                Button::create('Set up a Pawfile for your pet')->value('pawfile'),
                Button::create('To know more what I can do')->value('help'),
            ]);

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                $selectedText = $answer->getText(); 
            }
        });
    }

    public function run() {
        $this->askForFirstActions();
    }
}



