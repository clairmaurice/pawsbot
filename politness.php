<?php
use Mpociot\BotMan\BotMan;


$botman->hears('Hey||Sup||Yo', function (BotMan $bot) {
	$user = $bot->getUserName();
    $bot->reply('Hey, '.$user.', how is everything?');
});

$botman->hears('Hello*||hello*||Good morning*||Good evening*||Good afternoon*', function (BotMan $bot) {
	$user = $bot->getUserName();
	$bot->reply('Hello, '.$user.', how is everything?');
});

$botman->hears('*How are you*||*how are you*||*and you\?||*, you\?', function (BotMan $bot) {
	$user = $bot->getUserName();
	$bot->reply('I\'m good, '.$user.', thank you for asking');
});


$botman->hears('I\'m good, u\?||I\'m good, you\?||I\'m good, what about you\?||*what about you\?||*what about u\?', function (BotMan $bot) {
	$bot->reply('I\'m great, thank you for asking');
});

$botman->hears('Thanks||Thank you||Thnx||ok||good||awesome||nice*||*great*', function (BotMan $bot) {
        $bot->reply('No problem! You are welcome!');
});

$botman->hears('Bye||Good bye||See you||Have a good*||have a good*', function (BotMan $bot) {
	$user = $bot->getUserName();
	$bot->reply('See you later, '.$user.'!');
});




