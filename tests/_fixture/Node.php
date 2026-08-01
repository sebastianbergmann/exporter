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

/*
 * Helper to test export of objects that are nested in objects.
 */
final class Node
{
    /**
     * @var array<mixed>
     */
    public array $children;

    /**
     * @param array<mixed> $children
     */
    public function __construct(array $children = [])
    {
        $this->children = $children;
    }
}
