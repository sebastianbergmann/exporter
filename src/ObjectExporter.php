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

    public function export(object $object, Exporter $exporter, int $indentation): string;
}
