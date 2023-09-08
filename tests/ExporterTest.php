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
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\Context;
use SplObjectStorage;
use stdClass;

#[CoversClass(Exporter::class)]
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
        $storage->foo = $obj2;

        $resource = fopen('php://memory', 'r');
        fclose($resource);

        return [
            'export null'                   => [null, 'null'],
            'export boolean true'           => [true, 'true'],
            'export boolean false'          => [false, 'false'],
            'export int 1'                  => [1, '1'],
            'export float 1.0'              => [1.0, '1.0'],
            'export float 1.2'              => [1.2, '1.2'],
            'export float 1 / 3'            => [1 / 3, '0.3333333333333333'],
            'export float 1 - 2 / 3'        => [1 - 2 / 3, '0.33333333333333337'],
            'export float 5.5E+123'         => [5.5E+123, '5.5E+123'],
            'export float 5.5E-123'         => [5.5E-123, '5.5E-123'],
            'export float NAN'              => [NAN, 'NAN'],
            'export float INF'              => [INF, 'INF'],
            'export float -INF'             => [-INF, '-INF'],
            'export stream'                 => [fopen('php://memory', 'r'), 'resource(%d) of type (stream)'],
            'export stream (closed)'        => [$resource, 'resource (closed)'],
            'export numeric string'         => ['1', "'1'"],
            'export multidimensional array' => [[[1, 2, 3], [3, 4, 5]],
                <<<'EOF'
Array &0 (
    0 => Array &1 (
        0 => 1
        1 => 2
        2 => 3
    )
    1 => Array &2 (
        0 => 3
        1 => 4
        2 => 5
    )
)
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
            'export empty stdclass'     => [new stdClass, 'stdClass Object #%d ()'],
            'export non empty stdclass' => [$obj,
                <<<'EOF'
stdClass Object #%d (
    'null' => null
    'boolean' => true
    'integer' => 1
    'double' => 1.2
    'string' => '1'
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
text'
    'object' => stdClass Object #%d (
        'foo' => 'bar'
    )
    'objectagain' => stdClass Object #%d
    'array' => Array &%d (
        'foo' => 'bar'
    )
    'self' => stdClass Object #%d
)
EOF
            ],
            'export empty array'      => [[], 'Array &%d ()'],
            'export splObjectStorage' => [$storage,
                <<<'EOF'
SplObjectStorage Object #%d (
    'foo' => stdClass Object #%d (
        'foo' => 'bar'
    )
    'Object #%d' => Array &0 (
        'obj' => stdClass Object #%d
        'inf' => null
    )
)
EOF
            ],
            'export stdClass with numeric properties' => [$obj3,
                <<<'EOF'
stdClass Object #%d (
    0 => 1
    1 => 2
    2 => 'Test\r\n
'
    3 => 4
    4 => 5
    5 => 6
    6 => 7
    7 => 8
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
            'export Exception without trace' => [
                new Exception('The exception message', 42),
                <<<'EOF'
Exception Object #%d (
    'message' => 'The exception message'
    'string' => ''
    'code' => 42
    'file' => '%s/tests/ExporterTest.php'
    'line' => %d
    'previous' => null
)
EOF
            ],
            'export Error without trace' => [
                new Error('The exception message', 42),
                <<<'EOF'
Error Object #%d (
    'message' => 'The exception message'
    'string' => ''
    'code' => 42
    'file' => '%s/tests/ExporterTest.php'
    'line' => %d
    'previous' => null
)
EOF
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
            'shortened export null'            => [null, 'null'],
            'shortened export boolean true'    => [true, 'true'],
            'shortened export integer 1'       => [1, '1'],
            'shortened export float 1.0'       => [1.0, '1.0'],
            'shortened export float 1.2'       => [1.2, '1.2'],
            'shortened export float 1 / 3'     => [1 / 3, '0.3333333333333333'],
            'shortened export float 1 - 2 / 3' => [1 - 2 / 3, '0.33333333333333337'],
            'shortened export numeric string'  => ['1', "'1'"],
            // \n\r and \r is converted to \n
            'shortened export multilinestring'    => ["this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext", "'this\\nis\\na\\nvery\\nvery\\nvery...\\rtext'"],
            'shortened export empty stdClass'     => [new stdClass, 'stdClass Object ()'],
            'shortened export not empty stdClass' => [$obj, 'stdClass Object (...)'],
            'shortened export empty array'        => [[], 'Array ()'],
            'shortened export not empty array'    => [$array, 'Array (...)'],
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
            'export null'                   => [[null], 'null'],
            'export boolean true'           => [[true], 'true'],
            'export boolean false'          => [[false], 'false'],
            'export int 1'                  => [[1], '1'],
            'export float 1.0'              => [[1.0], '1.0'],
            'export float 1.2'              => [[1.2], '1.2'],
            'export numeric string'         => [['1'], "'1'"],
            'export with numeric array key' => [[2 => 1], '1'],
            'export with assoc array key'   => [['foo' => 'bar'], '\'bar\''],
            'export multidimensional array' => [[[1, 2, 3], [3, 4, 5]], 'array(1, 2, 3), array(3, 4, 5)'],
            'export object'                 => [[new stdClass], 'stdClass Object ()'],
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
Array &%d (
    0 => 0
    'null' => null
    'boolean' => true
    'integer' => 1
    'double' => 1.2
    'string' => '1'
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
text'
    'object' => stdClass Object #%d (
        'foo' => 'bar'
    )
    'objectagain' => stdClass Object #%d
    'array' => Array &%d (
        'foo' => 'bar'
    )
    'self' => Array &%d (
        0 => 0
        'null' => null
        'boolean' => true
        'integer' => 1
        'double' => 1.2
        'string' => '1'
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
text'
        'object' => stdClass Object #%d
        'objectagain' => stdClass Object #%d
        'array' => Array &%d (
            'foo' => 'bar'
        )
        'self' => Array &%d
    )
)
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

    public function testEnumExport(): void
    {
        // FIXME: Merge test into testExport once we drop support for PHP 8.0
        $this->assertStringMatchesFormat(
            'SebastianBergmann\Exporter\ExampleEnum Enum #%d (Value)',
            $this->trimNewline((new Exporter)->export(ExampleEnum::Value)),
        );
    }

    public function testStringBackedEnumExport(): void
    {
        // FIXME: Merge test into testExport once we drop support for PHP 8.0
        $this->assertStringMatchesFormat(
            'SebastianBergmann\Exporter\ExampleStringBackedEnum Enum #%d (Value, \'value\')',
            $this->trimNewline((new Exporter)->export(ExampleStringBackedEnum::Value)),
        );
    }

    public function testIntegerBackedEnumExport(): void
    {
        // FIXME: Merge test into testExport once we drop support for PHP 8.0
        $this->assertStringMatchesFormat(
            'SebastianBergmann\Exporter\ExampleIntegerBackedEnum Enum #%d (Value, 0)',
            $this->trimNewline((new Exporter)->export(ExampleIntegerBackedEnum::Value)),
        );
    }

    public function testEnumShortenedExport(): void
    {
        // FIXME: Merge test into testExport once we drop support for PHP 8.0
        $this->assertStringMatchesFormat(
            'SebastianBergmann\Exporter\ExampleEnum Enum (Value)',
            $this->trimNewline((new Exporter)->shortenedExport(ExampleEnum::Value)),
        );
    }

    public function testStringBackedEnumShortenedExport(): void
    {
        // FIXME: Merge test into testExport once we drop support for PHP 8.0
        $this->assertStringMatchesFormat(
            'SebastianBergmann\Exporter\ExampleStringBackedEnum Enum (Value, \'value\')',
            $this->trimNewline((new Exporter)->shortenedExport(ExampleStringBackedEnum::Value)),
        );
    }

    public function testIntegerBackedEnumShortenedExport(): void
    {
        // FIXME: Merge test into testExport once we drop support for PHP 8.0
        $this->assertStringMatchesFormat(
            'SebastianBergmann\Exporter\ExampleIntegerBackedEnum Enum (Value, 0)',
            $this->trimNewline((new Exporter)->shortenedExport(ExampleIntegerBackedEnum::Value)),
        );
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

    private function trimNewline(string $string): string
    {
        return preg_replace('/[ ]*\n/', "\n", $string);
    }
}
