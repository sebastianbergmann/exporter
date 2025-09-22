<?php declare(strict_types=1);
/*
 * This file is part of sebastian/exporter.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Exporter;

use const INF;
use const NAN;
use function array_map;
use function assert;
use function chr;
use function fclose;
use function fopen;
use function implode;
use function is_resource;
use function is_string;
use function mb_internal_encoding;
use function mb_language;
use function method_exists;
use function preg_replace;
use function range;
use function str_repeat;
use Error;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SebastianBergmann\RecursionContext\Context;
use SplObjectStorage;
use stdClass;

#[CoversClass(Exporter::class)]
#[Small]
final class ExporterTest extends TestCase
{
    /**
     * @return array<list<mixed>>
     */
    public static function exportProvider(): array
    {
        $obj2      = new stdClass;
        $obj2->foo = 'bar';

        $obj3 = (object) [1, 2, "Test\r\n", 4, 5, 6, 7, 8];

        $obj = new stdClass;
        // @codingStandardsIgnoreStart
        $obj->null = null;
        // @codingStandardsIgnoreEnd
        $obj->boolean     = true;
        $obj->integer     = 1;
        $obj->double      = 1.2;
        $obj->string      = '1';
        $obj->text        = "this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext";
        $obj->object      = $obj2;
        $obj->objectagain = $obj2;
        $obj->array       = ['foo' => 'bar'];
        $obj->self        = $obj;

        $storage = new SplObjectStorage;
        $storage->offsetSet($obj2);

        $resource = fopen('php://memory', 'r');

        assert(is_resource($resource));

        fclose($resource);

        $recursiveArray    = [];
        $recursiveArray[0] = &$recursiveArray;

        return [
            'null'                                   => [null, 'null', 0],
            'boolean true'                           => [true, 'true', 0],
            'boolean false'                          => [false, 'false', 0],
            'int 1'                                  => [1, '1', 0],
            'float 1.0'                              => [1.0, '1.0', 0],
            'float 1.2'                              => [1.2, '1.2', 0],
            'float 1 / 3'                            => [1 / 3, '0.3333333333333333', 0],
            'float 1 - 2 / 3'                        => [1 - 2 / 3, '0.33333333333333337', 0],
            'float 5.5E+123'                         => [5.5E+123, '5.5E+123', 0],
            'float 5.5E-123'                         => [5.5E-123, '5.5E-123', 0],
            'float NAN'                              => [NAN, 'NAN', 0],
            'float INF'                              => [INF, 'INF', 0],
            'float -INF'                             => [-INF, '-INF', 0],
            'stream'                                 => [fopen('php://memory', 'r'), 'resource(%d) of type (stream)', 0],
            'stream (closed)'                        => [$resource, 'resource (closed)', 0],
            'numeric string'                         => ['1', "'1'", 0],
            'multidimensional array (indentation=0)' => [[[1, 2, 3], [3, 4, 5]],
                <<<'EOF'
Array &0 [
    0 => Array &1 [
        0 => 1,
        1 => 2,
        2 => 3,
    ],
    1 => Array &2 [
        0 => 3,
        1 => 4,
        2 => 5,
    ],
]
EOF,
                0,
            ],
            'multidimensional array (indentation=1)' => [[[1, 2, 3], [3, 4, 5]],
                <<<'EOF'
Array &0 [
        0 => Array &1 [
            0 => 1,
            1 => 2,
            2 => 3,
        ],
        1 => Array &2 [
            0 => 3,
            1 => 4,
            2 => 5,
        ],
    ]
EOF,
                1,
            ],
            'multidimensional array (indentation=2)' => [[[1, 2, 3], [3, 4, 5]],
                <<<'EOF'
Array &0 [
            0 => Array &1 [
                0 => 1,
                1 => 2,
                2 => 3,
            ],
            1 => Array &2 [
                0 => 3,
                1 => 4,
                2 => 5,
            ],
        ]
EOF,
                2,
            ],
            // \n\r and \r is converted to \n
            'export multiline text' => ["this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext",
                <<<'EOF'
'this\n
is\n
a\n
very\n
very\n
very\n
very\n
very\n
very\r
long\n\r
text'
EOF,
                0,
            ],
            'empty stdclass'     => [new stdClass, 'stdClass Object #%d ()', 0],
            'non empty stdclass' => [$obj,
                <<<'EOF'
stdClass Object #%d (
    'null' => null,
    'boolean' => true,
    'integer' => 1,
    'double' => 1.2,
    'string' => '1',
    'text' => 'this\n
is\n
a\n
very\n
very\n
very\n
very\n
very\n
very\r
long\n\r
text',
    'object' => stdClass Object #%d (
        'foo' => 'bar',
    ),
    'objectagain' => stdClass Object #%d,
    'array' => Array &%d [
        'foo' => 'bar',
    ],
    'self' => stdClass Object #%d,
)
EOF,
                0,
            ],
            'empty array'      => [[], 'Array &%d []', 0],
            'splObjectStorage' => [$storage,
                <<<'EOF'
SplObjectStorage Object #%d (
    'Object #%d' => Array &0 [
        'obj' => stdClass Object #%d (
            'foo' => 'bar',
        ),
        'inf' => null,
    ],
)
EOF,
                0,
            ],
            'stdClass with numeric properties' => [$obj3,
                <<<'EOF'
stdClass Object #%d (
    0 => 1,
    1 => 2,
    2 => 'Test\r\n
',
    3 => 4,
    4 => 5,
    5 => 6,
    6 => 7,
    7 => 8,
)
EOF,
                0,
            ],
            [
                chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5),
                'Binary String: 0x000102030405',
                0,
            ],
            [
                implode('', array_map('chr', range(0x0E, 0x1F))),
                'Binary String: 0x0e0f101112131415161718191a1b1c1d1e1f',
                0,
            ],
            [
                chr(0x00) . chr(0x09),
                'Binary String: 0x0009',
                0,
            ],
            [
                '',
                "''",
                0,
            ],
            'Exception without trace' => [
                new Exception('The exception message', 42),
                <<<'EOF'
Exception Object #%d (
    'message' => 'The exception message',
    'string' => '',
    'code' => 42,
    'file' => '%s/tests/ExporterTest.php',
    'line' => %d,
    'previous' => null,
)
EOF,
                0,
            ],
            'Error without trace' => [
                new Error('The exception message', 42),
                <<<'EOF'
Error Object #%d (
    'message' => 'The exception message',
    'string' => '',
    'code' => 42,
    'file' => '%s/tests/ExporterTest.php',
    'line' => %d,
    'previous' => null,
)
EOF,
                0,
            ],
            'enum' => [
                ExampleEnum::Value,
                'SebastianBergmann\Exporter\ExampleEnum Enum #%d (Value)',
                0,
            ],
            'backed enum (string)' => [
                ExampleStringBackedEnum::Value,
                'SebastianBergmann\Exporter\ExampleStringBackedEnum Enum #%d (Value, \'value\')',
                0,
            ],
            'backed enum (integer)' => [
                ExampleIntegerBackedEnum::Value,
                'SebastianBergmann\Exporter\ExampleIntegerBackedEnum Enum #%d (Value, 0)',
                0,
            ],
            'recursive array' => [
                $recursiveArray,
                <<<'EOF'
Array &0 [
    0 => Array &1 [
        0 => Array &1,
    ],
]
EOF,
                0,
            ],
        ];
    }

    /**
     * @return array<list<mixed>>
     */
    public static function shortenedExportProvider(): array
    {
        $obj      = new stdClass;
        $obj->foo = 'bar';

        $array = [
            'foo' => 'bar',
        ];

        $recursiveArray    = [];
        $recursiveArray[0] = &$recursiveArray;

        return [
            'null'            => [null, 'null'],
            'boolean true'    => [true, 'true'],
            'integer 1'       => [1, '1'],
            'float 1.0'       => [1.0, '1.0'],
            'float 1.2'       => [1.2, '1.2'],
            'float 1 / 3'     => [1 / 3, '0.3333333333333333'],
            'float 1 - 2 / 3' => [1 - 2 / 3, '0.33333333333333337'],
            'numeric string'  => ['1', "'1'"],
            // \n\r and \r is converted to \n
            '38 single-byte characters'                       => [str_repeat('A', 38), '\'' . str_repeat('A', 38) . '\''],
            '39 single-byte characters'                       => [str_repeat('A', 39), '\'' . str_repeat('A', 29) . '...' . str_repeat('A', 6) . '\''],
            '38 multi-byte characters'                        => [str_repeat('🧪', 38), '\'' . str_repeat('🧪', 38) . '\''],
            '39 multi-byte characters'                        => [str_repeat('🧪', 39), '\'' . str_repeat('🧪', 29) . '...' . str_repeat('🧪', 6) . '\''],
            'string longer than custom maximum string length' => [str_repeat('A', 21), '\'' . str_repeat('A', 9) . '...' . str_repeat('A', 6) . '\'', 20],
            'multi-line string'                               => ["this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext", "'this\\nis\\na\\nvery\\nvery\\nvery...\\rtext'"],
            'empty stdClass'                                  => [new stdClass, 'stdClass Object ()'],
            'not empty stdClass'                              => [$obj, 'stdClass Object (...)'],
            'empty array'                                     => [[], '[]'],
            'not empty array'                                 => [$array, '[...]'],
            'enum'                                            => [ExampleEnum::Value, 'SebastianBergmann\Exporter\ExampleEnum Enum (Value)'],
            'backed enum (string)'                            => [ExampleStringBackedEnum::Value, 'SebastianBergmann\Exporter\ExampleStringBackedEnum Enum (Value, \'value\')'],
            'backen enum (integer)'                           => [ExampleIntegerBackedEnum::Value, 'SebastianBergmann\Exporter\ExampleIntegerBackedEnum Enum (Value, 0)'],
            'recursive array'                                 => [$recursiveArray, '[...]', 0],
            'class'                                           => [new ExampleClass('bar'), 'SebastianBergmann\Exporter\ExampleClass Object (...)', 0],
        ];
    }

    /**
     * @return array<list<mixed>>
     */
    public static function provideNonBinaryMultibyteStrings(): array
    {
        return [
            [implode('', array_map('chr', range(0x09, 0x0D))), 9],
            [implode('', array_map('chr', range(0x20, 0x7F))), 96],
            [implode('', array_map('chr', range(0x80, 0xFF))), 128],
        ];
    }

    /**
     * @return array<list<mixed>>
     */
    public static function shortenedRecursiveExportProvider(): array
    {
        $bigArray = [];

        for ($i = 0; $i < 20_000; $i++) {
            $bigArray[] = 'cast(\'foo' . $i . '\' as blob)';
        }

        $array     = [1, 2, 'hello', 'world', true, false];
        $deepArray = [$array, [$array, [$array, [$array, [$array, [$array, [$array, [$array, [$array, [$array]]]]]]]]]];

        return [
            'null'                   => [[null], 'null', 0],
            'boolean true'           => [[true], 'true', 0],
            'boolean false'          => [[false], 'false', 0],
            'int 1'                  => [[1], '1', 0],
            'float 1.0'              => [[1.0], '1.0', 0],
            'float 1.2'              => [[1.2], '1.2', 0],
            'numeric string'         => [['1'], "'1'", 0],
            'with numeric array key' => [[2 => 1], '1', 0],
            'with assoc array key'   => [['foo' => 'bar'], '\'bar\'', 0],
            'multidimensional array' => [[[1, 2, 3], [3, 4, 5]], '[1, 2, 3], [3, 4, 5]', 0],
            'object'                 => [[new stdClass], 'stdClass Object ()', 0],
            'big array'              => [$bigArray, "'cast('foo0' as blob)', 'cast('foo1' as blob)', 'cast('foo2' as blob)', 'cast('foo3' as blob)', 'cast('foo4' as blob)', 'cast('foo5' as blob)', 'cast('foo6' as blob)', 'cast('foo7' as blob)', 'cast('foo8' as blob)', 'cast('foo9' as blob)', 'cast('foo10' as blob)', ...19990 more elements", 10],
            'deep array'             => [$deepArray, "[1, 2, 'hello', 'world', true, false], [[1, 2, 'hello', 'world']], ...69 more elements", 10],
        ];
    }

    #[DataProvider('exportProvider')]
    public function testExport(mixed $value, string $expected, int $indentation): void
    {
        $this->assertStringMatchesFormat(
            $expected,
            $this->trimNewline((new Exporter)->export($value, $indentation)),
        );
    }

    public function testExport2(): void
    {
        $obj      = new stdClass;
        $obj->foo = 'bar';

        $array = [
            0             => 0,
            'null'        => null,
            'boolean'     => true,
            'integer'     => 1,
            'double'      => 1.2,
            'string'      => '1',
            'text'        => "this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext",
            'object'      => $obj,
            'objectagain' => $obj,
            'array'       => ['foo' => 'bar'],
        ];

        $array['self'] = &$array;

        $expected = <<<'EOF'
Array &%d [
    0 => 0,
    'null' => null,
    'boolean' => true,
    'integer' => 1,
    'double' => 1.2,
    'string' => '1',
    'text' => 'this\n
is\n
a\n
very\n
very\n
very\n
very\n
very\n
very\r
long\n\r
text',
    'object' => stdClass Object #%d (
        'foo' => 'bar',
    ),
    'objectagain' => stdClass Object #%d,
    'array' => Array &%d [
        'foo' => 'bar',
    ],
    'self' => Array &%d [
        0 => 0,
        'null' => null,
        'boolean' => true,
        'integer' => 1,
        'double' => 1.2,
        'string' => '1',
        'text' => 'this\n
is\n
a\n
very\n
very\n
very\n
very\n
very\n
very\r
long\n\r
text',
        'object' => stdClass Object #%d,
        'objectagain' => stdClass Object #%d,
        'array' => Array &%d [
            'foo' => 'bar',
        ],
        'self' => Array &%d,
    ],
]
EOF;

        $this->assertStringMatchesFormat(
            $expected,
            $this->trimNewline((new Exporter)->export($array)),
        );
    }

    /**
     * @param positive-int $maxLengthForStrings
     */
    #[DataProvider('shortenedExportProvider')]
    public function testShortenedExport(mixed $value, string $expected, int $maxLengthForStrings = 40): void
    {
        $this->assertSame(
            $expected,
            $this->trimNewline((new Exporter(0, $maxLengthForStrings))->shortenedExport($value)),
        );

        $this->assertSame(
            $expected,
            $this->trimNewline((new Exporter)->shortenedExport($value, $maxLengthForStrings)),
        );
    }

    #[RequiresPhpExtension('mbstring')]
    public function testShortenedExportForMultibyteCharacters(): void
    {
        $oldMbLanguage = mb_language();

        assert(is_string($oldMbLanguage));

        mb_language('Japanese');
        $oldMbInternalEncoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');

        try {
            $this->assertSame(
                "'いろはにほへとちりぬるをわかよたれそつねならむうゐのおくや...しゑひもせす'",
                $this->trimNewline((new Exporter)->shortenedExport('いろはにほへとちりぬるをわかよたれそつねならむうゐのおくやまけふこえてあさきゆめみしゑひもせす')),
            );
        } catch (Exception $e) {
            mb_internal_encoding($oldMbInternalEncoding);
            mb_language($oldMbLanguage);

            throw $e;
        }

        mb_internal_encoding($oldMbInternalEncoding);
        mb_language($oldMbLanguage);
    }

    #[DataProvider('provideNonBinaryMultibyteStrings')]
    public function testNonBinaryStringExport(string $value, int $expectedLength): void
    {
        $this->assertMatchesRegularExpression(
            "~'.{{$expectedLength}}'\$~s",
            (new Exporter)->export($value),
        );
    }

    public function testNonObjectCanBeReturnedAsArray(): void
    {
        $this->assertEquals([true], (new Exporter)->toArray(true));
    }

    public function testIgnoreKeysInValue(): void
    {
        // Find out what the actual use case was with the PHP bug
        $array             = [];
        $array["\0gcdata"] = '';

        $this->assertEquals([], (new Exporter)->toArray((object) $array));
    }

    /**
     * @param array<mixed>     $value
     * @param non-negative-int $limit
     */
    #[DataProvider('shortenedRecursiveExportProvider')]
    public function testShortenedRecursiveExport(array $value, string $expected, int $limit): void
    {
        $this->assertEquals($expected, (new Exporter($limit))->shortenedRecursiveExport($value));
    }

    public function testShortenedRecursiveOccurredRecursion(): void
    {
        $recursiveValue = [1];
        $context        = new Context;

        /* @noinspection UnusedFunctionResultInspection */
        $context->add($recursiveValue);

        $value = [$recursiveValue];

        $this->assertEquals('*RECURSION*', (new Exporter)->shortenedRecursiveExport($value, processed: $context));
    }

    #[RequiresPhp('^8.4')]
    public function testShortenedExportDoesNotInitializeLazyObject(): void
    {
        $reflector = new ReflectionClass(ExampleClass::class);

        assert(method_exists($reflector, 'newLazyProxy'));
        assert(method_exists($reflector, 'isUninitializedLazyObject'));

        $object = $reflector->newLazyProxy(static fn () => new ExampleClass('bar'));

        (new Exporter)->shortenedExport($object, 10);

        $this->assertTrue($reflector->isUninitializedLazyObject($object));
    }

    private function trimNewline(string $string): string
    {
        return (string) preg_replace('/[ ]*\n/', "\n", $string);
    }
}
