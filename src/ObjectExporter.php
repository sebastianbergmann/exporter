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

interface ObjectExporter
{
    public function handles(object $object): bool;

    /**
     * Exports an object this object exporter handles.
     *
     * The ExportContext must be passed on to Exporter::export() when values
     * that are nested in the object are exported using $exporter.
     *
     * @throws ObjectNotSupportedException
     */
    public function export(object $object, Exporter $exporter, int $indentation, ExportContext $context): string;
}
