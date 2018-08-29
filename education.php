<?php
use Mpociot\BotMan\BotMan;

$botman->hears("*learn*||*teach*", function (BotMan $bot) {
	$msg = 'I am ready for education! Just write to me something similar with this example for education:'.PHP_EOL;
	$msg .= 'query is "Do you have a cat?" and answer is "yes, I have"';
    $bot->reply($msg);
});

$botman->hears("*query is \"{question}\" and answer is \"{sanswer}\"*||*question is \"{question}\" and answer is \"{sanswer}\"*", function (BotMan $bot, $question, $answer) {
		$talks = $bot->driverStorage()->get();
        $bot->driverStorage()->save([
            $question => $answer,
        ]);

        $bot->addPattern($question, 'ownmemory');
        $bot->reply('Now it all in my memory! Thank you for the lesson.');
});

$botman->hears("what do you know*", function (BotMan $bot) {
	$talks = $bot->driverStorage()->get();
	if (count($talks) > 0) {
			$msg = 'I know this additional commands for now:'.PHP_EOL.PHP_EOL;
		foreach ($talks as $question => $answer) {
			$msg .= '*Question*: '.$question.PHP_EOL.'*Answer*: '.$answer.PHP_EOL.PHP_EOL;
		}
	} else {
		$msg = 'I don\'t know nothing!';
	}

    $bot->reply($msg);
});

$botman->hears("*forget*", function (BotMan $bot) {
	$talks = $bot->driverStorage()->get();
	if (count($talks) > 0) {
		foreach ($talks as $question => $answer) {
			$bot->removeMemoryPattern($question, 'ownmemory');
		}
	} 

	$bot->driverStorage()->delete();

	$bot->reply('My memory is clear! I don\'t know any additional commands anymore');
});

