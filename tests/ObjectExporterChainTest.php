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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ObjectExporterChain::class)]
#[UsesClass(Exporter::class)]
#[Small]
final class ObjectExporterChainTest extends TestCase
{
    public function testHandlesObjectWhenAtLeastOneComposedObjectExporterHandlesIt(): void
    {
        $chain = new ObjectExporterChain(
            [
                new ObjectExporterThatHandlesNoObject,
                new ObjectExporterThatHandlesEveryObject,
            ],
        );

        $this->assertTrue($chain->handles(new stdClass));
    }

    public function testDoesNotHandleObjectWhenNoComposedObjectExporterHandlesIt(): void
    {
        $chain = new ObjectExporterChain(
            [
                new ObjectExporterThatHandlesNoObject,
            ],
        );

        $this->assertFalse($chain->handles(new stdClass));
    }

    public function testDelegatesExportingToFirstComposedObjectExporterThatHandlesObject(): void
    {
        $chain = new ObjectExporterChain(
            [
                new ObjectExporterThatHandlesNoObject,
                new ObjectExporterThatHandlesObjectsOfSpecificType(stdClass::class),
                new ObjectExporterThatHandlesEveryObject,
            ],
        );

        $this->assertSame(
            'stdClass handled by custom exporter',
            $chain->export(new stdClass, new Exporter, 0),
        );

        $this->assertSame(
            ExampleClass::class . ' (indentation: 0)',
            $chain->export(new ExampleClass('foo'), new Exporter, 0),
        );
    }

    public function testCannotExportObjectWhenNoComposedObjectExporterHandlesIt(): void
    {
        $chain = new ObjectExporterChain(
            [
                new ObjectExporterThatHandlesNoObject,
            ],
        );

        $this->expectException(ObjectNotSupportedException::class);

        $chain->export(new stdClass, new Exporter, 0);
    }
}
