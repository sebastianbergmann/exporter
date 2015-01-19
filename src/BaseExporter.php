<?php
/*
 * This file is part of the Exporter package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Exporter;

/**
 * Abstract base class for visualizing PHP variables.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
abstract class BaseExporter
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Returns whether the exporter can export a given value.
     *
     * @param  mixed   $value The value to export.
     * @return boolean
     */
    public function accepts($value)
    {
        return true;
    }

    /**
     * Exports a value as a string.
     *
     * @param  mixed   $value
     * @param  integer $indentation The indentation level of the 2nd+ line
     * @return string
     */
    public function export($value, $indentation = 0)
    {
        return $this->recursiveExport($value, $indentation);
    }

    /**
     * Recursively exports a value as a string.
     *
     * @param  mixed                              $value       The value to export
     * @param  integer                            $indentation The indentation level of the 2nd+ line
     * @param  SebastianBergmann\Exporter\Context $processed   Contains all objects and arrays that have previously been rendered
     * @return string
     * @see    SebastianBergmann\Exporter\Exporter::export
     */
    abstract protected function recursiveExport(&$value, $indentation, $processed = null);

    /**
     * Exports a value into a single-line string.
     *
     * @param  mixed  $value
     * @return string
     * @see    SebastianBergmann\Exporter\Exporter::export
     */
    public function shortenedExport($value)
    {
        return $this->export($value);
    }

    /**
     * Converts a PHP value to an array.
     *
     * @param  mixed $value
     * @return array
     */
    public function toArray($value)
    {
        return (array) $value;
    }
}
