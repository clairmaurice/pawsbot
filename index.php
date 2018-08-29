<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'conversation.php';
require_once 'conversation_walk.php';
require_once 'conversation_pawsfile.php';
require_once 'conversation_get_pawsfile.php';
require_once 'conversation_export_pawsfile.php';
require_once 'buttons.php';
require_once 'database.php';
//require_once 'database.php';

use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Messages\Message;
use React\EventLoop\Factory;
use Mpociot\BotMan\Cache\RedisCache;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

$loop = Factory::create();
$botman = BotManFactory::createForRTM([
    'slack_token' => $config['slack_token'],
], $loop);

$db = new Database();
$GLOBALS['botman'] = $botman;
$GLOBALS['token'] = $config['slack_token'];
$GLOBALS['oauth_token'] = $config['slack_oauth_token'];
$GLOBALS['app_id'] = $db->getAppId($config['slack_token']);
$GLOBALS['in_conversation'] = false;

$botman->hears('Hello', function($bot) {
    $bot->startConversation(new HelloConversation($bot));
});

$botman->hears('walk', function($bot) {
    $bot->startConversation(new WalkConversation($bot));
});

$botman->hears('pawsfile', function($bot) {
    $bot->startConversation(new PawsfileConversation());
});

$botman->hears('show pawsfiles', function($bot) {
    $bot->startConversation(new GetPawsfileConversation());
});

$botman->hears('export pawsfiles', function($bot) {
    $bot->startConversation(new ExportPawsfileConversation());
});

$botman->hears('Help', function($bot) {
	$bot->reply('Hello, I know this commands: '.PHP_EOL.PHP_EOL.'    *Hello* - this command will help to set up an info about me, to set walking time and my location.'.PHP_EOL.'    *clear* - this command will reset all the info.'.PHP_EOL.PHP_EOL.'Enjoy!');
});

$botman->fallback(function(BotMan $bot) {
	if ($GLOBALS['in_conversation'] == false) {
		$user_id = $bot->getUserId();
		$name = $bot->getUserName($user_id);
    	$bot->startConversation(new ButtonsAction($name));
	} 
});


$loop->run();



