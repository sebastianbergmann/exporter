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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ObjectExporterChain::class)]
#[Small]
final class ObjectExporterChainTest extends TestCase
{
    public function testCanBeQueriedWhetherChainedExporterHandlesAnObject(): void
    {
        $firstExporter = $this->createStub(ObjectExporter::class);
        $firstExporter->method('handles')->willReturn(false);

        $secondExporter = $this->createStub(ObjectExporter::class);
        $secondExporter->method('handles')->willReturn(true);

        $chain = new ObjectExporterChain([$firstExporter]);
        $this->assertFalse($chain->handles(new stdClass));

        $chain = new ObjectExporterChain([$firstExporter, $secondExporter]);
        $this->assertTrue($chain->handles(new stdClass));
    }

    public function testDelegatesExportingToFirstExporterThatHandlesAnObject(): void
    {
        $firstExporter = $this->createStub(ObjectExporter::class);
        $firstExporter->method('handles')->willReturn(false);
        $firstExporter->method('export')->willThrowException(new ObjectNotSupportedException);

        $secondExporter = $this->createStub(ObjectExporter::class);
        $secondExporter->method('handles')->willReturn(true);
        $secondExporter->method('export')->willReturn('string');

        $chain = new ObjectExporterChain([$firstExporter, $secondExporter]);

        $this->assertSame('string', $chain->export(new stdClass));
    }

    public function testCannotExportObjectWhenNoExporterHandlesIt(): void
    {
        $firstExporter = $this->createStub(ObjectExporter::class);
        $firstExporter->method('handles')->willReturn(false);

        $chain = new ObjectExporterChain([$firstExporter]);

        $this->expectException(ObjectNotSupportedException::class);

        $this->assertSame('string', $chain->export(new stdClass));
    }
}
