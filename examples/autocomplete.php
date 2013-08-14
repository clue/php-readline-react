<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$readline = new Clue\Readline\React\Readline($loop, 'demo > ');

$readline->setAutocompleteWords(array(
    'hello',
    'world',
    'test',
    'empty',
    'quit',
    'exit'
));

$readline->on('line', function($line) use ($readline) {
    var_dump($line);

    if ($line === 'quit' || $line === 'exit') {
        $readline->pause();
    }
});

$loop->run();
