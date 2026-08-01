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

final readonly class ObjectExporterThatReturnsGivenRepresentation implements ObjectExporter
{
    private string $representation;

    public function __construct(string $representation)
    {
        $this->representation = $representation;
    }

    public function handles(object $object): bool
    {
        return true;
    }

    public function export(object $object, Exporter $exporter, int $indentation, ExportContext $context): string
    {
        return $this->representation;
    }
}
