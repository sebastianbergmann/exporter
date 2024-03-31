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

final class ObjectExporterChain implements ObjectExporter
{
    /**
     * @psalm-var non-empty-list<ObjectExporter>
     */
    private array $exporter;

    /**
     * @psalm-param non-empty-list<ObjectExporter> $exporter
     */
    public function __construct(array $exporter)
    {
        $this->exporter = $exporter;
    }

    public function handles(object $object): bool
    {
        foreach ($this->exporter as $exporter) {
            if ($exporter->handles($object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function export(object $object, Exporter $exporter, int $indentation): string
    {
        foreach ($this->exporter as $objectExporter) {
            if ($objectExporter->handles($object)) {
                return $objectExporter->export($object, $exporter, $indentation);
            }
        }

        throw new ObjectNotSupportedException;
    }
}
