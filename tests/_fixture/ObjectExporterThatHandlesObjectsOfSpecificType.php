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

use function sprintf;

final readonly class ObjectExporterThatHandlesObjectsOfSpecificType implements ObjectExporter
{
    /**
     * @var class-string
     */
    private string $type;

    /**
     * @param class-string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function handles(object $object): bool
    {
        return $object instanceof $this->type;
    }

    public function export(object $object, Exporter $exporter, int $indentation): string
    {
        return sprintf(
            '%s handled by custom exporter',
            $object::class,
        );
    }
}
