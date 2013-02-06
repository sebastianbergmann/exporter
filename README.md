PHP_Exporter
===========

[![Build Status](https://secure.travis-ci.org/whatthejeff/php-exporter.png?branch=master)](https://travis-ci.org/whatthejeff/php-exporter)

[PHPUnit](https://github.com/sebastianbergmann/phpunit/) includes a nifty
utility for generating human-readable strings based on PHP values. PHP_Exporter
is simply a stand-alone version of that utility.

## Usage

Exporting simple types:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$exporter = new PHP_Exporter\Exporter;

// 46
echo $exporter->export(46);

// 4.0
echo $exporter->export(4.0);

// 'hello, world!'
echo $exporter->export('hello, world!');

// false
echo $exporter->export(false);

// NAN
echo $exporter->export(acos(8));

// -INF
echo $exporter->export(log(0));

// null
echo $exporter->export(null);

// resource(13) of type (stream)
echo $exporter->export(fopen('php://stderr', 'w'));

// Binary String: 0x000102030405
echo $exporter->export(chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5));
```

Exporting complex types:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$exporter = new PHP_Exporter\Exporter;

/*
Array &0 (
    0 => Array &1 (
        0 => 1
        1 => 2
        2 => 3
    )
    1 => Array &2 (
        0 => ''
        1 => 0
        2 => false
    )
)
*/
echo $exporter->export(array(array(1,2,3), array("",0,FALSE)));

/*
Array &0 (
    'self' => Array &1 (
        'self' => Array &1
    )
)
*/
$array = array();
$array['self'] = &$array;
echo $exporter->export($array);

/*
stdClass Object &0000000003a66dcc0000000025e723e2 (
    'self' => stdClass Object &0000000003a66dcc0000000025e723e2
)
*/
$obj = new stdClass();
$obj->self = $obj;
echo $exporter->export($obj);
```

Compact exports:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$exporter = new PHP_Exporter\Exporter;

// Array ()
echo $exporter->shortenedExport(array());

// Array (...)
echo $exporter->shortenedExport(array(1,2,3,4,5));

// stdClass Object ()
echo $exporter->shortenedExport(new stdClass);

// Exception Object (...)
echo $exporter->shortenedExport(new Exception);

// this\nis\na\nsuper\nlong\nstring\nt...\nspace
echo $exporter->shortenedExport(
<<<LONG_STRING
this
is
a
super
long
string
that
wraps
a
lot
and
eats
up
a
lot
of
space
LONG_STRING
);
```

## Requirements

PHP_Exporter works with PHP 5.3.3 or later.

## Installation

The recommended way to install PHP_Exporter is [through
composer](http://getcomposer.org). Just create a `composer.json` file and
run the `php composer.phar install` command to install it:

    {
        "require": {
            "php-exporter/php-exporter": "1.0.*@dev"
        }
    }

## Tests

To run the test suite, you need [composer](http://getcomposer.org).

    $ php composer.phar install --dev
    $ vendor/bin/phpunit

## Acknowledgements

This utility was adapted from the
[PHPUnit](https://github.com/sebastianbergmann/phpunit/) project. A special
thanks goes to the following people for their contributions:

 * [sebastianbergmann](https://github.com/sebastianbergmann)
 * [edorian](https://github.com/edorian)
 * [LawnGnome](https://github.com/LawnGnome)
 * [bschussek](https://github.com/bschussek)

## License

PHP_Exporter is licensed under the [BSD 3-Clause license](LICENSE).