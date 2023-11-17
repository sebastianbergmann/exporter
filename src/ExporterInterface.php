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

use SebastianBergmann\RecursionContext\Context;

interface ExporterInterface
{
    /**
     * Exports a value as a string.
     *
     * The output of this method is similar to the output of print_r(), but
     * improved in various aspects:
     *
     *  - NULL is rendered as "null" (instead of "")
     *  - TRUE is rendered as "true" (instead of "1")
     *  - FALSE is rendered as "false" (instead of "")
     *  - Strings are always quoted with single quotes
     *  - Carriage returns and newlines are normalized to \n
     *  - Recursion and repeated rendering is treated properly
     */
    public function export(mixed $value, int $indentation = 0): string;

    public function shortenedRecursiveExport(array &$data, Context $context = null): string;

    /**
     * Exports a value into a single-line string.
     *
     * The output of this method is similar to the output of
     * SebastianBergmann\Exporter\Exporter::export().
     *
     * Newlines are replaced by the visible string '\n'.
     * Contents of arrays and objects (if any) are replaced by '...'.
     */
    public function shortenedExport(mixed $value): string;

    /**
     * Converts an object to an array containing all of its private, protected
     * and public properties.
     */
    public function toArray(mixed $value): array;

    /**
     * Exports nested array items (the "interior" of array or an object
     * converted to array).
     */
    public function exportNestedItems(array $array, int $indentation, Context $processed): string;
}
