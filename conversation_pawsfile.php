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

class PawsfileConversation extends Conversation
{   

    // Pawsfile basic:

    protected $name;
    protected $type;
    protected $gender;
    protected $breed;
    protected $borndate;
    protected $weight;
    protected $photo;
    protected $nickname;

    // routine:

    protected $monday = false;
    protected $tuesday = false;
    protected $wednesday = false;
    protected $thursday = false;
    protected $friday = false;
    protected $nodays = true;

    // Eat and Play:

    protected $foods;
    protected $non_foods;
    protected $games;
    protected $toys;

    // Official details:

    protected $emmergency_contact;
    protected $emmergency_registration;

    public function askName() {
        $this->ask('Let\'s start with your pet\'s name.', function(Answer $answer) {
            $this->name = $answer->getText();
            $this->askType();
        });
    }

    public function askType()
    {
        $question = Question::create('And is '.$this->name.' a:')
            ->fallback('Unable to detect a pet type')
            ->callbackId('pet_type')
            ->addButtons([
                Button::create('Cat')->value('cat'),    // value 1
                Button::create('Dog')->value('dog'),    // value 2
                Button::create('Bird')->value('bird'),  // value 3
            ]);

        $this->ask($question, function(Answer $answer) {
            $type = $answer->getText();

            if ( ($type == 'dog') || ($type == 'cat') || ($type == 'bird') ) {
                $this->type = $type;
                $this->askGender();
            } else {
                $this->say('Sorry, can\'t detect the type, please, try again');
                $this->askType();
            }
            
        });
    }

    public function askGender()
    {
        $question = Question::create('Male, female or prefer not to say?')
            ->fallback('Unable to detect a pet gender')
            ->callbackId('pet_gender')
            ->addButtons([
                Button::create('Male')->value('male'),
                Button::create('Feemale')->value('feemale'),
                Button::create('Prefer not to say')->value('no gender'),
            ]);

        $this->ask($question, function(Answer $answer) {
            $gender = $answer->getText();

            if ( ($gender == 'male') || ($gender == 'feemale') || ($gender == 'no gender') ) {
                $this->gender = $gender;
                $this->askBreed();
            } else {
                $this->say('Sorry, can\'t detect the gender, please, try again');
                $this->askGender();
            }
            
        });
    }

    public function askBreed() {
        $this->ask('What type of '.$this->type.' is '.$this->name.'?', function(Answer $answer) {
            $this->breed = $answer->getText();
            $this->askBorndate();
        });
    }

    public function askBorndate() {
        $this->ask('And what date was '.$this->name.' the '.$this->breed.' born?', function(Answer $answer) {
            $borndate = $answer->getText();
            $time = strtotime($borndate);
            $this->borndate = date('Y-m-d',$time);
            $this->askWeight();
        });
    }

    public function askWeight() {
        $this->ask('How much does '.$this->name.' weight? It can be approximate.', function(Answer $answer) {
            $this->weight = $answer->getText();
            $this->askPhoto();
        });
    }

    public function askPhoto() {
        $this->ask('Can you show me a photo of '.$this->name.'?', function(Answer $answer) {
            $this->photo = $this->takePhoto();
            $this->askHandle();
        });
    }

    public function askHandle() {
        $this->ask('What would you like '.$this->name.'\'s slack handle to be?', function(Answer $answer) {
            $nickname = $answer->getText();
            $nickname = str_replace('@', '', $nickname);
            $this->nickname = $nickname;
            $this->askConfirm();
        });
    }

    public function askConfirm() {

        $pawsfile = 'Let\'s see... does this look about right?'.PHP_EOL.PHP_EOL;
        $pawsfile .= '*Name:* '.$this->name.PHP_EOL;
        $pawsfile .= '*Type:* '.$this->type.PHP_EOL;
        $pawsfile .= '*Gender:* '.$this->gender.PHP_EOL;
        $pawsfile .= '*Breed:* '.$this->breed.PHP_EOL;
        $pawsfile .= '*Weight:* '.$this->weight.PHP_EOL;
        $pawsfile .= '*Date of Birth:* '.$this->borndate.PHP_EOL;
        $pawsfile .= '*Slack handle (Nickname):* @'.$this->nickname.PHP_EOL;

        $question = Question::create($pawsfile)
            ->fallback(':(')
            ->callbackId('petfile_confirm')
            ->addButtons([
                Button::create('yep that\'s it!')->value('yes'),
                Button::create('Oops I got something wrong')->value('no'),
            ]);

        $this->ask($question, function(Answer $answer) {
            $confirm = $answer->getText();

            if ( ($confirm == 'yes') || ($confirm == 'no') ) {
               if ($confirm == 'yes') {
                    $this->askRoutine();
               } else {
                    //
                    $this->say('Let\'s start again');
                    $this->run();
               }
            } else {
                $this->say('Sorry, can\'t detect the gender, please, try again');
                $this->askGender();
            }
            
        });
    }

    /* ROUTINE */

    public function askRoutine()
    {
        $question = Question::create('I come to the office on (select all that apply):')
            ->fallback('Unable to detect an office day')
            ->callbackId('pet_days')
            ->addButtons([
                Button::create('Monday')->value('monday'),
                Button::create('Tuesday')->value('tuesday'),
                Button::create('Wednesday')->value('wednesday'),
                Button::create('Thursday')->value('thursday'),
                Button::create('Friday')->value('friday'),
            ]);

        $this->ask($question, function(Answer $answer) {
            $day = $answer->getText();

            switch($day) {
                case 'monday':
                    $this->monday = true;
                    $this->nodays = false;
                    $this->askMore();
                    break;

                case 'tuesday':
                    $this->tuesday = true;
                    $this->nodays = false;
                    $this->askMore();
                    break;

                case 'wednesday':
                    $this->wednesday = true;
                    $this->nodays = false;
                    $this->askMore();
                    break;

                case 'thursday':
                    $this->thursday = true;
                    $this->nodays = false;
                    $this->askMore();
                    break;

                case 'friday':
                    $this->friday = true;
                    $this->nodays = false;
                    $this->askMore();
                    break;

                default:
                    $this->say('Can\'t understand, please, try again');
                    $this->askRoutine();
                    break;
            }
            
        });
    }


    public function askMore() {

        $question = Question::create('Do you want to add more days?')
            ->fallback('Unable to detect if more or not')
            ->callbackId('pet_days_more')
            ->addButtons([
                Button::create('Yep, one more day, please')->value('yes'),
                Button::create('No, only this days')->value('save'),
                Button::create('No set any days')->value('delete'),
            ]);

        $this->ask($question, function(Answer $answer) {
            $more = $answer->getText();

            switch($more) {
                case 'yes':
                    $this->askRoutine();
                    break;

                case 'save':
                    $this->askEat();
                    break;

                case 'delete':
                    $this->monday = false;
                    $this->tuesday = false;
                    $this->wednesday = false;
                    $this->thursday = false;
                    $this->friday = false;
                    $this->askEat();
                    break;

                default:
                    $this->askRoutine();
                    break;   
            }
                
        });
    }


    /* EAT AND PLAY: */

    public function askEat()
    {
        $this->ask('What I like to eat? [comma separated food list]', function(Answer $answer) {
            $this->foods = $answer->getText();
            $this->askNonFood();
        });

    }


    public function askNonFood()
    {
        $this->ask('Please don\'t feed me: [comma separated food list]', function(Answer $answer) {
            $this->non_foods = $answer->getText();
            $this->askToys();
        });
    }


    public function askToys()
    {
        $this->ask('My favourite toys are: [comma separated food list]', function(Answer $answer) {
            $this->toys = $answer->getText();
            $this->askGames();
        });
    }

    public function askGames()
    {
        $this->ask('My favourite games are: [comma separated food list]', function(Answer $answer) {
            $this->games = $answer->getText();
            $this->askEmmergency();
        });
    }

    /* OFFICIAL DETAILS */

    public function askEmmergency()
    {
        $this->ask('In an emergency, call:', function(Answer $answer) {
            $this->emmergency_contact = $answer->getText();
            $this->askEmmergencyRegistration();
        });
    }

    public function askEmmergencyRegistration() {
        $this->ask('Council Registration number:', function(Answer $answer) {
            $this->emmergency_registration = $answer->getText();
            $this->askConfirmLast();
        });
    }


    /* CONFIRMATION */

    public function askConfirmLast() {

        $pawsfile = 'Let\'s see... does this look about right?'.PHP_EOL.PHP_EOL;
        $days = '';
        
        if ($this->nodays == false) {
            
            if ($this->monday) {
                
                if (!empty($days)) {
                   $days .= ', ';
                }

                $days .= 'monday';
            } 

            if ($this->tuesday) {

                if (!empty($days)) {
                   $days .= ', ';
                }

                $days .= 'tuesday';
            }

            if ($this->wednesday) {

                if (!empty($days)) {
                   $days .= ', ';
                }

                $days .= 'wednesday';
            }

            if ($this->thursday) {
                
                if (!empty($days)) {
                   $days .= ', ';
                }

                $days .= 'thursday';
            }

            if ($this->friday) {

                if (!empty($days)) {
                   $days .= ', ';
                }

                $days .= 'friday';
            }

        } else {
            $days = 'Not setted days';
        }

        $pawsfile .= '*ROUTINE*:'.PHP_EOL.PHP_EOL;
        $pawsfile .= '*I come to the office on:* '.$days.PHP_EOL;

        $pawsfile .= PHP_EOL.'*EAT AND PLAY*:'.PHP_EOL.PHP_EOL;

        $pawsfile .= '*I love to eat:* '.$this->foods.PHP_EOL;
        $pawsfile .= '*Please don\'t feed me:* '.$this->breed.PHP_EOL;
        $pawsfile .= '*My favourite toys are:* '.$this->weight.PHP_EOL;
        $pawsfile .= '*My favourite games are:* '.$this->borndate.PHP_EOL;
        
        $pawsfile .= PHP_EOL.'*OFFICIAL DETAILS*:'.PHP_EOL.PHP_EOL;

        $pawsfile .= '*In an emergency, call:* '.$this->emmergency_contact.PHP_EOL;
        $pawsfile .= '*Council Registration number:* '.$this->emmergency_registration.PHP_EOL;


        $question = Question::create($pawsfile)
            ->fallback(':(')
            ->callbackId('petfile_confirm_last')
            ->addButtons([
                Button::create('yep that\'s it!')->value('yes'),
                Button::create('Oops I got something wrong')->value('no'),
            ]);

        $this->ask($question, function(Answer $answer) {
            $confirm = $answer->getText();

            if ( ($confirm == 'yes') || ($confirm == 'no') ) {
               if ($confirm == 'yes') {
                    $this->end();
               } else {
                    $this->say('Let\'s to add another data');
                    $this->askRoutine();
               }
            } else {
                $this->say('Sorry, can\'t detect the gender, please, try again');
                $this->askGender();
            }
            
        });
    }

    public function end() {
        
        //save to the database;
        $db = new Database();

        switch ($this->type) {
            case 'cat':
                $type = 1;
                break;

            case 'dog':
                $type = 2;
                break;

            case 'bird':
                $type = 3;
                break;
            
            default:
                $type = 0;
                break;
        }


        switch ($this->gender) {
            case 'male':
                $gender = 1;
                break;

            case 'feemale':
                $gender = 2;
                break;
            
            default:
                $gender = 0;
                break;
        }

        $query = 'INSERT INTO pawsfiles (`app_id`,`name`, `type_id`, `gender`, `breed`, `borndate`, `weight`, `photo`, `nickname`) VALUES ( '.$GLOBALS["app_id"].',"'.$this->name.'", '.$type.', '.$gender.', "'.$this->breed.'", "'.$this->borndate.'", "'.$this->weight.'", "'.$this->photo.'", "'.$this->nickname.'")';

        $id = $db->saveAndGetId($query);
        
        $monday = (int) $this->monday;
        $tuesday = (int) $this->tuesday;
        $wednesday = (int) $this->wednesday;
        $thursday = (int) $this->thursday;
        $friday = (int) $this->friday;
        $nodays = (int) $this->nodays;

        $query = 'INSERT INTO routines (`pet_id`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `no_days`) VALUES ("'.$id.'", '.$monday.', '.$tuesday.', '.$wednesday.', '.$thursday.', '.$friday.', '.$nodays.')';

        $db->execute($query);

        $query = 'INSERT INTO details (`pet_id`, `emmergency_contact`, `emmergency_registration`) VALUES ("'.$id.'", "'.$this->emmergency_contact.'", "'.$this->emmergency_registration.'")';

        $db->execute($query);

        $query = 'INSERT INTO eatplay (`pet_id`, `foods`, `non_foods`, `games`, `toys`) VALUES ("'.$id.'", "'.$this->foods.'", "'.$this->non_foods.'", "'.$this->games.'", "'.$this->toys.'")';

        var_dump($query);

        $db->execute($query);

        $this->say('All done! If you want to see '.$this->name.'\'s pawfile in the future just DM me.');
        $GLOBALS['in_conversation'] = false;
    }
    
    public function run()
    {   
        $GLOBALS['in_conversation'] = true;
        $this->askName();
    }

    public function takePhoto() {
        sleep(5);
        $this->slack = new slack($GLOBALS['oauth_token']);
        $files = $this->slack->files->list(['count' => 1]);
        $file = $files["files"][0]['url_private'];
        return $file;
    }

   
}