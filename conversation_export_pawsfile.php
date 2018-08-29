<?php
require 'vendor/autoload.php';
require_once 'database.php';

use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Conversation;
use Mpociot\BotMan\Messages\Message;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Button;
use Mpociot\BotMan\Question;
use React\EventLoop\Factory;
use wrapi\slack\slack;

/*
Who's pawfile do you want to see?

[Free text @petslackhandle or pet name]

Okay! [Oops I can't find a pet with that name. Try again?]

Here's @{petname}'s pawfile!
return {petphoto}
@petname is a {petbreed} {pettype} who is {Pet Age}.

You can feed {pronoun} {favourite foods} but don't feed {pronoun} {nonfoods}. @petname's favourite toys are {toys} and he loves to play {games}.

He's usually in the office on {office days IF No set days = any day}.

In an emergency you should call {emergency contact} on {emergency number}
Council Registration number: {registration}
*/

class ExportPawsfileConversation extends Conversation
{   

    public function askType() {

        $question = Question::create('How do you want to export all your petfiles?')
            ->fallback('can\'t take the pawsfiles')
            ->callbackId('export_pawsfiles')
            ->addButtons([
                Button::create('collect the info to CSV\XLS File')->value('xls'),
                Button::create('just show as a message here')->value('message'),
            ]);

        $this->ask($question, function(Answer $answer) {
            if ($answer == 'xls') {
                $this->xls();
            } else {
                $this->textshow();
            }
        });
    }

    public function textshow() {

        $query = 'SELECT * FROM pawsfiles WHERE app_id = "'.$GLOBALS['app_id'].'"';
        $db = new Database();
        $alldata = $db->getAllAssoc($query);
        
        if (is_array($alldata)) {
            foreach ($alldata as $data) {
                
                $query = 'SELECT * FROM details WHERE pet_id = "'.$data['id'].'"';
                $details = $db->getAllAssoc($query);
                if ($details) {
                    $details = $details[0];
                }

                $query = 'SELECT * FROM routines WHERE pet_id = "'.$data['id'].'"';
                $routines = $db->getAllAssoc($query);
                if ($routines) {
                    $routines = $routines[0];
                }

                $query = 'SELECT * FROM types WHERE id = '.$data['type_id'];
                
                $type = $db->getAllAssoc($query);
                if ($type) {
                    $type = $type[0]['type'];
                }

                $query = 'SELECT * FROM eatplay WHERE pet_id = "'.$data['id'].'"';
                $eatplay = $db->getAllAssoc($query);
                if ($eatplay) {
                    $eatplay = $eatplay[0];
                }

                $datetime1 = new DateTime($data['borndate']);
                $datetime2 = new DateTime(date('Y-M-d'));
                $interval = $datetime1->diff($datetime2);
                $age = $interval->format('%y years, %m monthes and %d days old');

                $days = '';
                if ($routines['no_days'] == 1) {
                    $days = 'any days';
                } else {
                    
                    if ($routines['monday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'monday';
                    }

                    if ($routines['tuesday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'tuesday';
                    }

                    if ($routines['wednesday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'wednesday';
                    }

                    if ($routines['thursday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'thursday';
                    }

                    if ($routines['friday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'friday';
                    }
                }

                $say = 'Here\'s *'.$data['name'].'\'s pawfile!*'.PHP_EOL.PHP_EOL;
                $say .= '@'.$data['nickname'].' is a '.$data['breed'].' '.$type.' who is '.$age.'.'.PHP_EOL;
                $say .= 'You can feed it '.$eatplay['foods'].', but *don\'t feed it '.$eatplay['non_foods'].'*.'.PHP_EOL.' @'.$data['nickname'].'\'s favourite toys are '.$eatplay['toys'].' and he loves to play '.$eatplay['games'].'.'.PHP_EOL.PHP_EOL;
                $say .= 'He\'s usually in the office on '.$days.'.'.PHP_EOL.PHP_EOL;

                $say .= 'In an emergency you should call '.$details['emmergency_contact'].PHP_EOL;
                $say .= 'Council Registration number: '.$details['emmergency_registration'].PHP_EOL.PHP_EOL;
                $say .= 'The photo is here: '.$data['photo'].PHP_EOL.PHP_EOL.PHP_EOL;
                $say .= '================================================================='.PHP_EOL.PHP_EOL.PHP_EOL;

                $this->say($say);
            }
        }
    }


     public function xls() {

        $query = 'SELECT * FROM pawsfiles WHERE app_id = "'.$GLOBALS['app_id'].'"';
        $db = new Database();
        $alldata = $db->getAllAssoc($query);
        
        $csv = 'id; name; nickname; type; age; breed; foods; don\'t feed; toys; games; emmergency contact; emmergency registration; office days; photo'.PHP_EOL;

        if (is_array($alldata)) {
            foreach ($alldata as $data) {
                
                $query = 'SELECT * FROM details WHERE pet_id = "'.$data['id'].'"';
                $details = $db->getAllAssoc($query);
                if ($details) {
                    $details = $details[0];
                }

                $query = 'SELECT * FROM routines WHERE pet_id = "'.$data['id'].'"';
                $routines = $db->getAllAssoc($query);
                if ($routines) {
                    $routines = $routines[0];
                }

                $query = 'SELECT * FROM types WHERE id = '.$data['type_id'];
                
                $type = $db->getAllAssoc($query);
                if ($type) {
                    $type = $type[0]['type'];
                }

                $query = 'SELECT * FROM eatplay WHERE pet_id = "'.$data['id'].'"';
                $eatplay = $db->getAllAssoc($query);
                if ($eatplay) {
                    $eatplay = $eatplay[0];
                }

                $datetime1 = new DateTime($data['borndate']);
                $datetime2 = new DateTime(date('Y-M-d'));
                $interval = $datetime1->diff($datetime2);
                $age = $interval->format('%y years, %m monthes and %d days old');

                $days = '';
                if ($routines['no_days'] == 1) {
                    $days = 'any days';
                } else {
                    
                    if ($routines['monday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'monday';
                    }

                    if ($routines['tuesday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'tuesday';
                    }

                    if ($routines['wednesday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'wednesday';
                    }

                    if ($routines['thursday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'thursday';
                    }

                    if ($routines['friday'] == 1) {
                        if ($days != '') {
                            $days .= ', ';
                        }
                        $days .= 'friday';
                    }
                }


                $csv .= $data['id'].'; '.$data['name'].'; '.$data['nickname'].'; '.$type.'; '.$age.'; '.$data['breed'].'; '.$eatplay['foods'].'; '.$eatplay['non_foods'].'; '.$eatplay['toys'].'; '.$eatplay['games'].'; '.$details['emmergency_contact'].'; '.$details['emmergency_registration'].';'.$days.';'.$data['photo'].PHP_EOL;
            }

            $filename = 'files/'.md5($csv).'.csv';
            file_put_contents($filename, $csv);
            $say = 'Here is your file with all the pawsfiles!';
            $this->say($say);
        }
    }


    

    public function run() {   
        $GLOBALS['in_conversation'] = true;
        $this->askType();
    }
   
}