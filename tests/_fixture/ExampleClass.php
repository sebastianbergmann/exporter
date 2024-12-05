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
 * Helper to test export of enums.
 */
class ExampleClass
{
    /**
     * @phpstan-ignore property.onlyWritten
     */
    private string $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}
