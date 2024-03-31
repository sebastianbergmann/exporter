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
use function chr;
use function fclose;
use function fopen;
use function implode;
use function mb_internal_encoding;
use function mb_language;
use function preg_replace;
use function range;
use Error;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\Context;
use SplObjectStorage;
use stdClass;

#[CoversClass(Exporter::class)]
#[UsesClass(ObjectExporterChain::class)]
#[Small]
final class ExporterTest extends TestCase
{
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
        $storage->attach($obj2);

        $resource = fopen('php://memory', 'r');
        fclose($resource);

        return [
            'null'                   => [null, 'null'],
            'boolean true'           => [true, 'true'],
            'boolean false'          => [false, 'false'],
            'int 1'                  => [1, '1'],
            'float 1.0'              => [1.0, '1.0'],
            'float 1.2'              => [1.2, '1.2'],
            'float 1 / 3'            => [1 / 3, '0.3333333333333333'],
            'float 1 - 2 / 3'        => [1 - 2 / 3, '0.33333333333333337'],
            'float 5.5E+123'         => [5.5E+123, '5.5E+123'],
            'float 5.5E-123'         => [5.5E-123, '5.5E-123'],
            'float NAN'              => [NAN, 'NAN'],
            'float INF'              => [INF, 'INF'],
            'float -INF'             => [-INF, '-INF'],
            'stream'                 => [fopen('php://memory', 'r'), 'resource(%d) of type (stream)'],
            'stream (closed)'        => [$resource, 'resource (closed)'],
            'numeric string'         => ['1', "'1'"],
            'multidimensional array' => [[[1, 2, 3], [3, 4, 5]],
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
EOF
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
EOF
            ],
            'empty stdclass'     => [new stdClass, 'stdClass Object #%d ()'],
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
EOF
            ],
            'empty array'      => [[], 'Array &%d []'],
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
EOF
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
EOF
            ],
            [
                chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5),
                'Binary String: 0x000102030405',
            ],
            [
                implode('', array_map('chr', range(0x0E, 0x1F))),
                'Binary String: 0x0e0f101112131415161718191a1b1c1d1e1f',
            ],
            [
                chr(0x00) . chr(0x09),
                'Binary String: 0x0009',
            ],
            [
                '',
                "''",
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
EOF
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
EOF
            ],
            'enum' => [
                ExampleEnum::Value,
                'SebastianBergmann\Exporter\ExampleEnum Enum #%d (Value)',
            ],
            'backed enum (string)' => [
                ExampleStringBackedEnum::Value,
                'SebastianBergmann\Exporter\ExampleStringBackedEnum Enum #%d (Value, \'value\')',
            ],
            'backed enum (integer)' => [
                ExampleIntegerBackedEnum::Value,
                'SebastianBergmann\Exporter\ExampleIntegerBackedEnum Enum #%d (Value, 0)',
            ],
        ];
    }

    public static function shortenedExportProvider(): array
    {
        $obj      = new stdClass;
        $obj->foo = 'bar';

        $array = [
            'foo' => 'bar',
        ];

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
            'multilinestring'       => ["this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext", "'this\\nis\\na\\nvery\\nvery\\nvery...\\rtext'"],
            'empty stdClass'        => [new stdClass, 'stdClass Object ()'],
            'not empty stdClass'    => [$obj, 'stdClass Object (...)'],
            'empty array'           => [[], '[]'],
            'not empty array'       => [$array, '[...]'],
            'enum'                  => [ExampleEnum::Value, 'SebastianBergmann\Exporter\ExampleEnum Enum (Value)'],
            'backed enum (string)'  => [ExampleStringBackedEnum::Value, 'SebastianBergmann\Exporter\ExampleStringBackedEnum Enum (Value, \'value\')'],
            'backen enum (integer)' => [ExampleIntegerBackedEnum::Value, 'SebastianBergmann\Exporter\ExampleIntegerBackedEnum Enum (Value, 0)'],
        ];
    }

    public static function provideNonBinaryMultibyteStrings(): array
    {
        return [
            [implode('', array_map('chr', range(0x09, 0x0D))), 9],
            [implode('', array_map('chr', range(0x20, 0x7F))), 96],
            [implode('', array_map('chr', range(0x80, 0xFF))), 128],
        ];
    }

    public static function shortenedRecursiveExportProvider(): array
    {
        return [
            'null'                   => [[null], 'null'],
            'boolean true'           => [[true], 'true'],
            'boolean false'          => [[false], 'false'],
            'int 1'                  => [[1], '1'],
            'float 1.0'              => [[1.0], '1.0'],
            'float 1.2'              => [[1.2], '1.2'],
            'numeric string'         => [['1'], "'1'"],
            'with numeric array key' => [[2 => 1], '1'],
            'with assoc array key'   => [['foo' => 'bar'], '\'bar\''],
            'multidimensional array' => [[[1, 2, 3], [3, 4, 5]], '[1, 2, 3], [3, 4, 5]'],
            'object'                 => [[new stdClass], 'stdClass Object ()'],
        ];
    }

    #[DataProvider('exportProvider')]
    public function testExport($value, $expected): void
    {
        $this->assertStringMatchesFormat(
            $expected,
            $this->trimNewline((new Exporter)->export($value)),
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

    #[DataProvider('shortenedExportProvider')]
    public function testShortenedExport($value, $expected): void
    {
        $this->assertSame(
            $expected,
            $this->trimNewline((new Exporter)->shortenedExport($value)),
        );
    }

    #[RequiresPhpExtension('mbstring')]
    public function testShortenedExportForMultibyteCharacters(): void
    {
        $oldMbLanguage = mb_language();
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
    public function testNonBinaryStringExport($value, $expectedLength): void
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

    #[DataProvider('shortenedRecursiveExportProvider')]
    public function testShortenedRecursiveExport(array $value, string $expected): void
    {
        $this->assertEquals($expected, (new Exporter)->shortenedRecursiveExport($value));
    }

    public function testShortenedRecursiveOccurredRecursion(): void
    {
        $recursiveValue = [1];
        $context        = new Context;

        /* @noinspection UnusedFunctionResultInspection */
        $context->add($recursiveValue);

        $value = [$recursiveValue];

        $this->assertEquals('*RECURSION*', (new Exporter)->shortenedRecursiveExport($value, $context));
    }

    public function testExportOfObjectsCanBeCustomized(): void
    {
        $objectExporter = $this->createStub(ObjectExporter::class);
        $objectExporter->method('handles')->willReturn(true);
        $objectExporter->method('export')->willReturn('custom object export');

        $exporter = new Exporter(new ObjectExporterChain([$objectExporter]));

        $this->assertStringMatchesFormat(
            <<<'EOT'
Array &0 [
    0 => stdClass Object #%d (custom object export),
    1 => stdClass Object #%d (custom object export),
]
EOT
            ,
            $exporter->export([new stdClass, new stdClass]),
        );

    }

    private function trimNewline(string $string): string
    {
        return preg_replace('/[ ]*\n/', "\n", $string);
    }
}
