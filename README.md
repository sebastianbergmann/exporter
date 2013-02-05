PHPExporter
===========

PHPExporter is a utility–adapted from [PHPUnit]
(https://github.com/sebastianbergmann/phpunit/–for getting textual
representations of PHP values.

```php
<?php
require_once __DIR__.'/../vendor/autoload.php';

use PHPExporter\Exporter;

// 46
echo Exporter::export(46);

// 4.0
echo Exporter::export(4.0);

// 'hello, world!'
echo Exporter::export('hello, world!');

// false
echo Exporter::export(false);

// NAN
echo Exporter::export(acos(8));

// -INF
echo Exporter::export(log(0));

// null
echo Exporter::export(null);

// resource(13) of type (stream)
echo Exporter::export(fopen('php://stderr', 'w'));

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
echo Exporter::export(array(array(1,2,3), array("",0,FALSE)));

/*
Array &0 (
    'self' => Array &1 (
        'self' => Array &1
    )
)
*/
$array = array();
$array['self'] = &$array;
echo Exporter::export($array);

/*
stdClass Object &0000000003a66dcc0000000025e723e2 (
    'self' => stdClass Object &0000000003a66dcc0000000025e723e2
)
*/
$obj = new stdClass();
$obj->self = $obj;
echo Exporter::export($obj);
```

## Requirements

PHPExporter works with PHP 5.3.3 or later.

## Installation

The recommended way to install PHPExporter is [through
composer](http://getcomposer.org). Just create a `composer.json` file and
run the `php composer.phar install` command to install it:

    {
        "require": {
            "phpexporter/phpexporter": "1.0.*@dev"
        }
    }

## Tests

To run the test suite, you need [composer](http://getcomposer.org).

    $ php composer.phar install --dev
    $ phpunit

## Acknowledgements

This utility was adapted from the
[PHPUnit](https://github.com/sebastianbergmann/phpunit/) project. A special
thanks goes to the following people for their contributions:

 * [sebastianbergmann](https://github.com/sebastianbergmann)
 * [edorian](https://github.com/edorian)
 * [LawnGnome](https://github.com/LawnGnome)
 * [bschussek](https://github.com/bschussek)

## License

PHPExporter is licensed under the BSD 3-Clause license.