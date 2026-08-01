[![Latest Stable Version](https://poser.pugx.org/sebastian/exporter/v)](https://packagist.org/packages/sebastian/exporter)
[![CI Status](https://github.com/sebastianbergmann/exporter/workflows/CI/badge.svg)](https://github.com/sebastianbergmann/exporter/actions)
[![codecov](https://codecov.io/gh/sebastianbergmann/exporter/branch/main/graph/badge.svg)](https://codecov.io/gh/sebastianbergmann/exporter)

# sebastian/exporter

This component provides the functionality to export PHP variables for visualization.

## Installation

You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

```
composer require sebastian/exporter
```

If you only need this library during development, for instance to run your project's test suite, then you should add it as a development-time dependency:

```
composer require --dev sebastian/exporter
```

## Usage

Exporting:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\Exporter;

$exporter = new Exporter;

/*
Exception Object #4 (
    'message' => '',
    'string' => '',
    'code' => 0,
    'file' => '/home/sebastianbergmann/test.php',
    'line' => 34,
    'previous' => null,
)
*/

print $exporter->export(new Exception);
```

## Data Types

Exporting simple types:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\Exporter;

$exporter = new Exporter;

// 46
print $exporter->export(46);

// 4.0
print $exporter->export(4.0);

// 'hello, world!'
print $exporter->export('hello, world!');

// false
print $exporter->export(false);

// NAN
print $exporter->export(acos(8));

// -INF
print $exporter->export(log(0));

// null
print $exporter->export(null);

// resource(13) of type (stream)
print $exporter->export(fopen('php://stderr', 'w'));

// Binary String: 0x000102030405
print $exporter->export(chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5));
```

Exporting complex types:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\Exporter;

$exporter = new Exporter;

/*
Array &0 [
    0 => Array &1 [
        0 => 1,
        1 => 2,
        2 => 3,
    ],
    1 => Array &2 [
        0 => '',
        1 => 0,
        2 => false,
    ],
]
*/

print $exporter->export([[1, 2, 3], ['', 0, false]]);

/*
Array &0 [
    'self' => Array &1 [
        'self' => Array &1,
    ],
]
*/

$array         = [];
$array['self'] = &$array;

print $exporter->export($array);

/*
stdClass Object #4 (
    'self' => stdClass Object #4,
)
*/

$obj       = new stdClass;
$obj->self = $obj;

print $exporter->export($obj);
```

Compact exports:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\Exporter;

$exporter = new Exporter;

// []
print $exporter->shortenedExport([]);

// [...]
print $exporter->shortenedExport([1, 2, 3, 4, 5]);

// stdClass Object ()
print $exporter->shortenedExport(new stdClass);

// Exception Object (...)
print $exporter->shortenedExport(new Exception);

// 'this\nis\na\nsuper\nlong\nstr...nspace'
print $exporter->shortenedExport(
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

## Customizing How Objects Are Exported

By default, an object is exported property by property. Implement the `ObjectExporter` interface to control how objects of a type are represented:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\ExportContext;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\Exporter\ObjectExporter;

final class MoneyExporter implements ObjectExporter
{
    public function handles(object $object): bool
    {
        return $object instanceof Money;
    }

    public function export(object $object, Exporter $exporter, int $indentation, ExportContext $context): string
    {
        assert($object instanceof Money);

        return sprintf('Money (%d %s)', $object->amount(), $object->currency());
    }
}
```

Object exporters are registered by passing an `ObjectExporterChain` to `Exporter`'s constructor. They are consulted, in the order in which they are composed into the chain, before the default representation of an object is used. The first object exporter whose `handles()` method returns `true` is asked for the representation:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\Exporter\ObjectExporterChain;

$exporter = new Exporter(
    objectExporter: new ObjectExporterChain([new MoneyExporter]),
);

// Money (1999 EUR)
print $exporter->export(new Money(1999, 'EUR'));

/*
stdClass Object #6 (
    'net' => Money (1999 EUR),
    'gross' => Money (2379 EUR),
)
*/

$price        = new stdClass;
$price->net   = new Money(1999, 'EUR');
$price->gross = new Money(2379, 'EUR');

print $exporter->export($price);
```

Enums are objects and are therefore passed to the object exporters as well. When no object exporter handles an object, the default representation is used for it.

### Repeated Occurrences

When the same object occurs more than once, the default representation is only used for the first occurrence; subsequent occurrences are replaced with a reference to the object:

```php
/*
Array &0 [
    0 => Money Object #8 (
        'amount' => 1999,
        'currency' => 'EUR',
    ),
    1 => Money Object #8,
]
*/

$money = new Money(1999, 'EUR');

print (new Exporter)->export([$money, $money]);
```

An object exporter is responsible for the entire representation of the object it handles. Every occurrence of such an object is therefore exported by it:

```php
/*
Array &0 [
    0 => Money (1999 EUR),
    1 => Money (1999 EUR),
]
*/

print $exporter->export([$money, $money]);
```

The exception is an object that is (indirectly) nested in itself: it cannot be exported this way without recursing infinitely and is replaced with a reference to the object.

### Exporting Nested Values

An object exporter may use the `Exporter` it is given to export values that are nested in the object it handles. The `ExportContext` it is given must be passed on when it does. Otherwise, the export of these nested values starts over with an empty context and, for instance, assigns references to arrays that are already in use elsewhere in the same export:

```php
<?php declare(strict_types=1);
use SebastianBergmann\Exporter\ExportContext;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\Exporter\ObjectExporter;

final class BasketExporter implements ObjectExporter
{
    public function handles(object $object): bool
    {
        return $object instanceof Basket;
    }

    public function export(object $object, Exporter $exporter, int $indentation, ExportContext $context): string
    {
        assert($object instanceof Basket);

        return 'Basket ' . $exporter->export($object->items(), $indentation, $context);
    }
}
```

```php
/*
Array &0 [
    'basket' => Basket Array &1 [
        0 => Money (1999 EUR),
    ],
]
*/

$exporter = new Exporter(
    objectExporter: new ObjectExporterChain([new BasketExporter, new MoneyExporter]),
);

print $exporter->export(['basket' => new Basket([new Money(1999, 'EUR')])]);
```

Object exporters are not consulted by `Exporter::shortenedExport()` and `Exporter::shortenedRecursiveExport()`, which deliberately elide the contents of objects.
