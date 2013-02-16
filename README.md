Exporter
========

[![Build Status](https://secure.travis-ci.org/sebastianbergmann/exporter.png?branch=master)](https://travis-ci.org/sebastianbergmann/exporter)

This component provides the functionality to export PHP variables for visualization.

## Usage

Exporting:

```php
<?php
use SebastianBergmann\Exporter\Exporter;

/*
Exception Object &0000000078de0f0d000000002003a261 (
    'message' => ''
    'string' => ''
    'code' => 0
    'file' => '/home/sebastianbergmann/test.php'
    'line' => 34
    'trace' => Array &0 ()
    'previous' => null
)
*/

print new Exporter(new Exception);
```

## Data Types

Exporting simple types:

```php
<?php
use SebastianBergmann\Exporter\Exporter;

// 46
print new Exporter(46);

// 4.0
print new Exporter(4.0);

// 'hello, world!'
print new Exporter('hello, world!');

// false
print new Exporter(false);

// NAN
print new Exporter(acos(8));

// -INF
print new Exporter(log(0));

// null
print new Exporter(null);

// resource(13) of type (stream)
print new Exporter(fopen('php://stderr', 'w'));

// Binary String: 0x000102030405
print new Exporter(chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5));
```

Exporting complex types:

```php
<?php
use SebastianBergmann\Exporter\Exporter;

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

print new Exporter(array(array(1,2,3), array("",0,FALSE)));

/*
Array &0 (
    'self' => Array &1 (
        'self' => Array &1
    )
)
*/

$array = array();
$array['self'] = &$array;
print new Exporter($array);

/*
stdClass Object &0000000003a66dcc0000000025e723e2 (
    'self' => stdClass Object &0000000003a66dcc0000000025e723e2
)
*/

$obj = new stdClass();
$obj->self = $obj;
print new Exporter($obj);
```

Compact exports:

```php
<?php
use SebastianBergmann\Exporter\Exporter;

// Array ()
$exporter = new Exporter(array());
print $exporter->shortenedExport();

// Array (...)
$exporter = new Exporter(array(1,2,3,4,5));
print $exporter->shortenedExport();

// stdClass Object ()
$exporter = new Exporter(new stdClass);
print $exporter->shortenedExport();

// Exception Object (...)
$exporter = new Exporter(new Exception);
print $exporter->shortenedExport();

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
print $exporter->shortenedExport();
```

## Installation

There are two supported ways of installing Exporter.

You can use the [PEAR Installer](http://pear.php.net/manual/en/guide.users.commandline.cli.php) or [Composer](http://getcomposer.org/) to download and install Exporter as well as its dependencies.

### PEAR Installer

The following two commands (which you may have to run as `root`) are all that is required to install Exporter using the PEAR Installer:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/Exporter

### Composer

To add Exporter as a local, per-project dependency to your project, simply add a dependency on `sebastian/exporter` to your project's `composer.json` file. Here is a minimal example of a `composer.json` file that just defines a dependency on Exporter 1.0:

    {
        "require": {
            "sebastian/exporter": "1.0.*"
        }
    }
