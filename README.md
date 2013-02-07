PHP_Exporter
===========

[![Build Status](https://secure.travis-ci.org/whatthejeff/php-exporter.png?branch=master)](https://travis-ci.org/whatthejeff/php-exporter)

[PHPUnit](https://github.com/sebastianbergmann/phpunit/) includes a nifty
utility for visualizing and diffing PHP data types. PHP_Exporter
is simply a stand-alone version of that utility.

## Usage

Exporting:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHP_Exporter\Exporter;

/*
Exception Object &0000000078de0f0d000000002003a261 (
    'message' => ''
    'string' => ''
    'code' => 0
    'file' => '/home/whatthejeff/test.php'
    'line' => 34
    'trace' => Array &0 ()
    'previous' => null
)
*/
echo new Exporter(new \Exception);

```

Diffing:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHP_Exporter\Exporter;

/*
--- Original
+++ New
@@ @@
-Exception Object &000000000e4b5f5a0000000028315dc8 (
+Exception Object &000000000e4b5f5d0000000028315dc8 (
     'message' => ''
     'string' => ''
     'code' => 0
     'file' => '/home/whatthejeff/test.php'
-    'line' => 34
+    'line' => 35
     'trace' => Array &0 ()
     'previous' => null
 )
*/
$exporter = new Exporter(new \Exception);
echo $exporter->diff(new \Exception);

```

## Data Types

Exporting simple types:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHP_Exporter\Exporter;

// 46
echo new Exporter(46);

// 4.0
echo new Exporter(4.0);

// 'hello, world!'
echo new Exporter('hello, world!');

// false
echo new Exporter(false);

// NAN
echo new Exporter(acos(8));

// -INF
echo new Exporter(log(0));

// null
echo new Exporter(null);

// resource(13) of type (stream)
echo new Exporter(fopen('php://stderr', 'w'));

// Binary String: 0x000102030405
echo new Exporter(chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5));
```

Exporting complex types:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHP_Exporter\Exporter;

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
echo new Exporter(array(array(1,2,3), array("",0,FALSE)));

/*
Array &0 (
    'self' => Array &1 (
        'self' => Array &1
    )
)
*/
$array = array();
$array['self'] = &$array;
echo new Exporter($array);

/*
stdClass Object &0000000003a66dcc0000000025e723e2 (
    'self' => stdClass Object &0000000003a66dcc0000000025e723e2
)
*/
$obj = new stdClass();
$obj->self = $obj;
echo new Exporter($obj);
```

Compact exports:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHP_Exporter\Exporter;

// Array ()
$exporter = new Exporter(array());
echo $exporter->shortenedExport();

// Array (...)
$exporter = new Exporter(array(1,2,3,4,5));
echo $exporter->shortenedExport();

// stdClass Object ()
$exporter = new Exporter(new stdClass);
echo $exporter->shortenedExport();

// Exception Object (...)
$exporter = new Exporter(new Exception);
echo $exporter->shortenedExport();

// this\nis\na\nsuper\nlong\nstring\nt...\nspace
$exporter = new Exporter(
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
echo $exporter->shortenedExport();
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