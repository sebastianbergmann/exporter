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

/**
 * Can be implemented by an ObjectExporter that wants to provide the
 * representation of the reference that replaces an object it handles when
 * that object is (indirectly) nested in itself.
 */
interface ObjectReferenceExporter
{
    /**
     * Exports a reference to an object that is (indirectly) nested in itself.
     *
     * Returns null when the default representation of the reference is to be
     * used.
     */
    public function exportReference(object $object): ?string;
}
