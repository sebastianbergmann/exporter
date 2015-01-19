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
 * Exporter for visualizing objects.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class ObjectExporter extends BaseExporter
{
    /**
     * Returns whether the exporter can export a given value.
     *
     * @param  mixed   $value The value to export.
     * @return boolean
     */
    public function accepts($value)
    {
        return is_object($value);
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

        $class = get_class($value);

        if ($hash = $processed->contains($value)) {
            return sprintf('%s Object &%s', $class, $hash);
        }

        $hash = $processed->add($value);
        $whitespace = str_repeat(' ', 4 * $indentation);
        $values = '';

        $array = $this->toArray($value);

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                $keyExporter = $this->factory->getExporterFor($k);
                $valueExporter = $this->factory->getExporterFor($v);

                $values .= sprintf(
                  '%s    %s => %s' . "\n",
                  $whitespace,
                  $keyExporter->recursiveExport($k, $indentation),
                  $valueExporter->recursiveExport($v, $indentation + 1, $processed)
                );
            }

            $values = "\n" . $values . $whitespace;
        }

        return sprintf('%s Object &%s (%s)', $class, $hash, $values);
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
          '%s Object (%s)',
          get_class($value),
          count($this->toArray($value)) > 0 ? '...' : ''
        );
    }

    /**
     * Converts a PHP value to an array.
     *
     * @param  mixed $value
     * @return array
     */
    public function toArray($value)
    {
        $array = array();

        foreach ((array) $value as $key => $val) {
            // properties are transformed to keys in the following way:

            // private   $property => "\0Classname\0property"
            // protected $property => "\0*\0property"
            // public    $property => "property"

            if (preg_match('/^\0.+\0(.+)$/', $key, $matches)) {
                $key = $matches[1];
            }

            // See https://github.com/php/php-src/commit/5721132
            if ($key === "\0gcdata") {
                continue;
            }

            $array[$key] = $val;
        }

        return $array;
    }
}
