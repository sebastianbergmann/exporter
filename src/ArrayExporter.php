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
 * Exporter for visualizing arrays.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class ArrayExporter extends BaseExporter
{
    /**
     * Returns whether the exporter can export a given value.
     *
     * @param  mixed   $value The value to export.
     * @return boolean
     */
    public function accepts($value)
    {
        return is_array($value);
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
        if (!$processed) {
            $processed = new Context();
        }

        if (($key = $processed->contains($value)) !== false) {
            return 'Array &' . $key;
        }

        $key = $processed->add($value);
        $whitespace = str_repeat(' ', 4 * $indentation);
        $values = '';

        if (count($value) > 0) {
            foreach ($value as $k => $v) {
                $keyExporter = $this->factory->getExporterFor($k);
                $valueExporter = $this->factory->getExporterFor($value[$k]);

                $values .= sprintf(
                  '%s    %s => %s' . "\n",
                  $whitespace,
                  $keyExporter->recursiveExport($k, $indentation),
                  $valueExporter->recursiveExport(
                    $value[$k], $indentation + 1, $processed
                  )
                );
            }

            $values = "\n" . $values . $whitespace;
        }

        return sprintf('Array &%s (%s)', $key, $values);
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
        return sprintf(
          'Array (%s)',
          count($value) > 0 ? '...' : ''
        );
    }
}
