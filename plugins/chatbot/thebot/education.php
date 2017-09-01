<?php
use Mpociot\BotMan\BotMan;

$botman->hears("*learn*||*teach*", function (BotMan $bot) {
	$msg = 'I am ready to learn. Just write to me something similar with this example for education:'.PHP_EOL;
	$msg .= 'Do you have a cat? : yes, I have';
	$bot->reply($msg);
});

$botman->hears('{question} : {sanswer}', function (BotMan $bot, $question, $answer) {
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
		$msg = 'I know nothing! Start teaching me. Type learn to start ... ';
	}

    $bot->reply($msg);
});

$botman->hears("call me {name}", function (BotMan $bot, $name) {
    $bot->userStorage()->save([
        'name' => $name
    ]);

    $bot->reply('I will call you '.$name);
});

$botman->hears("who am I*", function (BotMan $bot) {
    $user = $bot->userStorage()->get();
    if ($user->has('name')) {
        $bot->reply('You are '.$user->get('name'));
    } else {
        $bot->reply('I do not know you yet.');
    }
});

$botman->hears("Save my birthday {date}", function(BotMan $bot, $date){
	$bot->userStorage()->save(['birthdate' => $date]);
	$bot->reply('Your birth date saved!');
});

$botman->hears("Get birth date of {userReference}", function(BotMan $bot, $userReference){
	$user = $bot->userStorage()->get();
	$name = $user->get('name');
	$date = $user->get('birthdate');
	$bot->reply(sprintf('Birth date of %s is %s', $name, $date));
});

$botman->hears("*weather {toronto}*", function(BotMan $bot, $city){
	$bot->reply("@forecast ".$city);
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

