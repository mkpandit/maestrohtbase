<?php
use Mpociot\BotMan\BotMan;

$botman->hears('Hey||Sup||Hi', function (BotMan $bot) {
	$user = $bot->getUserName();
    $bot->reply('Hey '.$user.', Welcome to HTBASE. How is everything?');
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

$botman->hears('Good bye||See you||Have a good*||have a good*', function (BotMan $bot) {
	$user = $bot->getUserName();
	$bot->reply('See you later, '.$user.'!');
});

$botman->hears('Bye||bye', function (BotMan $bot) {
	$user = $bot->getUserName();
	$bot->reply('Hasta la vista baby!');
});

$botman->hears('yo||Yo||you||uo', function (BotMan $bot) {
	$user = $bot->getUserName();
    $bot->reply('Hey Bro!');
});

$botman->hears('How are things?||how are things?||how are the things||how is things', function (BotMan $bot) {
	$user = $bot->getUserName();
    $bot->reply('Doing well, how about you?');
});

$botman->hears('What is the square root of 69?||What is the square-root of 69?||square root of 69?||what is the square root of 69?', function (BotMan $bot) {
    $bot->reply('Its 8.something');
});

$botman->hears('Tell me a joke*||tell me a joke*||crack a joke', function (BotMan $bot) {
    $bot->reply('C++: Get a life man. Hang out and socialize. \n Java: Well, it’s easy for you to say that, you have friends.');
});

$botman->hears('What are you going to do today*||*what are you going to do today*', function (BotMan $bot) {
    $bot->reply('Work and relax after work, I’m not a machine man, I rest too!');
});

$botman->hears('Hows the weather*||*weather*', function (BotMan $bot) {
	$bot->reply('I do not work with weather, ask my brother @forecast. It got all the weather information.');
});

$botman->hears('How ya doing*||*how you doing*', function (BotMan $bot) {
	$bot->reply('Not bad, how about you?');
});

$botman->hears('Whats up*||*whats up*', function (BotMan $bot) {
	$bot->reply('Not much, how about you?');
});

$botman->hears('Bonjour*||*bonjour*', function (BotMan $bot) {
	$bot->reply('Bonjour, allez-vous?');
});

$botman->hears('Did you get any error?||*did you get any error*', function (BotMan $bot) {
	$bot->reply('Nope!');
});

$botman->hears('What can you answer?||*what can you answer*', function (BotMan $bot) {
	$bot->reply('Everything you want!');
});

$botman->hears('Can you start?||can you start an hypertask vm?||Can you restart the virtual machine?', function (BotMan $bot) {
	$bot->reply('Sure, I will take that off your hands!');
});