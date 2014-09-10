<?php
/**
 * Exporter
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */

namespace SebastianBergmann\Exporter;

/**
 * Exporter for visualizing objects.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
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
