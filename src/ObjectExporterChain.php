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

use function array_any;

final readonly class ObjectExporterChain implements ObjectExporter
{
    /**
     * @var non-empty-list<ObjectExporter>
     */
    private array $exporters;

    /**
     * @param non-empty-list<ObjectExporter> $exporters
     */
    public function __construct(array $exporters)
    {
        $this->exporters = $exporters;
    }

    public function handles(object $object): bool
    {
        return array_any(
            $this->exporters,
            static fn (ObjectExporter $exporter) => $exporter->handles($object),
        );
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function export(object $object, Exporter $exporter, int $indentation): string
    {
        foreach ($this->exporters as $objectExporter) {
            if ($objectExporter->handles($object)) {
                return $objectExporter->export($object, $exporter, $indentation);
            }
        }

        throw new ObjectNotSupportedException;
    }
}
