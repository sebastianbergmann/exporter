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

use function assert;
use function sprintf;

final readonly class ObjectExporterThatExportsNestedValues implements ObjectExporter
{
    public function handles(object $object): bool
    {
        return $object instanceof Node;
    }

    public function export(object $object, Exporter $exporter, int $indentation, ExportContext $context): string
    {
        assert($object instanceof Node);

        return sprintf(
            'Node(%s)',
            $exporter->export($object->children, $indentation, $context),
        );
    }
}
