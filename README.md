# clue/readline-react [![Build Status](https://travis-ci.org/clue/php-readline-react.svg?branch=master)](https://travis-ci.org/clue/php-readline-react)

Experimental bindings for the readline extension (ext-readline)

> Note: This project is in early alpha stage! Feel free to report any issues you encounter.

## Quickstart example

Once [installed](#install), you can use the following code to present a prompt in a CLI program:

```php
$readline = new Readline($loop, 'demo > ');

$readline->on('line', function ($line) use ($readline) {
    var_dump($line);

    if ($line === 'quit' || $line === 'exit') {
        $readline->pause();
    }
});
```

See also the [examples](examples).

## Install

The recommended way to install this library is [through composer](packagist://getcomposer.org).
[New to composer?](packagist://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/readline-react": "dev-master"
    }
}
```

## License

MIT

