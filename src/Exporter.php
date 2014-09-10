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
 * A nifty utility for visualizing PHP variables.
 *
 * <code>
 * <?php
 * use SebastianBergmann\Exporter\Exporter;
 *
 * $exporter = new Exporter;
 * print $exporter->export(new Exception);
 * </code>
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class Exporter extends BaseExporter
{
    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory = null)
    {
        if (!$factory) {
            $factory = new Factory();

            $factory->register(new BasicExporter($factory));
            $factory->register(new StringExporter($factory));
            $factory->register(new ArrayExporter($factory));
            $factory->register(new ObjectExporter($factory));
            $factory->register(new SplObjectStorageExporter($factory));
        }

        parent::__construct($factory);
    }

    /**
     * Gets the current factory.
     *
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
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
    protected function recursiveExport(&$value, $indentation, $processed = null)
    {
        $exporter = $this->factory->getExporterFor($value);

        return $exporter->recursiveExport($value, $indentation, $processed);
    }

    /**
     * Exports a value into a single-line string.
     *
     * @param  mixed  $value
     * @return string
     * @see    SebastianBergmann\Exporter\Exporter::export
     */
    public function shortenedExport($value)
    {
        $exporter = $this->factory->getExporterFor($value);

        return $exporter->shortenedExport($value);
    }

    /**
     * Converts a PHP value to an array.
     *
     * @param  mixed $value
     * @return array
     */
    public function toArray($value)
    {
        $exporter = $this->factory->getExporterFor($value);

        return $exporter->toArray($value);
    }
}
