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

use SebastianBergmann\RecursionContext\Context as RecursionContext;
use SplObjectStorage;

/**
 * Carries the state that is shared by all steps of a single export operation.
 *
 * An implementation of ObjectExporter that exports values nested in the object
 * it handles must pass the instance of this class it is given to the Exporter
 * it delegates to. Otherwise, the export of these nested values starts over
 * with an empty context and, for instance, assigns references to arrays that
 * are already in use elsewhere in the same export.
 */
final class ExportContext
{
    private RecursionContext $recursionContext;

    /**
     * @var SplObjectStorage<object, null>
     */
    private SplObjectStorage $exportedByObjectExporter;

    public function __construct()
    {
        $this->recursionContext         = new RecursionContext;
        $this->exportedByObjectExporter = new SplObjectStorage;
    }

    /**
     * @template T of array|object
     *
     * @param T $value
     *
     * @param-out T $value
     */
    public function add(array|object &$value): int
    {
        return $this->recursionContext->add($value);
    }

    /**
     * @template T of array|object
     *
     * @param T $value
     *
     * @param-out T $value
     */
    public function contains(array|object &$value): false|int
    {
        return $this->recursionContext->contains($value);
    }

    /**
     * Whether the export of an object by an ObjectExporter is in progress.
     *
     * This is the case when the object is (indirectly) nested in itself.
     */
    public function isBeingExportedByObjectExporter(object $object): bool
    {
        return $this->exportedByObjectExporter->offsetExists($object);
    }

    public function beginExportByObjectExporter(object $object): void
    {
        $this->exportedByObjectExporter->offsetSet($object);
    }

    public function endExportByObjectExporter(object $object): void
    {
        $this->exportedByObjectExporter->offsetUnset($object);
    }
}
