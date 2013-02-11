<?php
/**
 * PHP_Exporter
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHP_Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/whatthejeff/php-exporter
 */

namespace Whatthejeff\PHP\Exporter;

/**
 * A nifty utility for visualizing PHP variables.
 *
 * <code>
 * <?php
 *
 * use Whatthejeff\PHP\Exporter\Exporter;
 *
 * // Basic export
 * $exporter = new Exporter(new Exception);
 * echo $exporter->export();
 *
 * // same as $exporter->export();
 * echo $exporter;
 * </code>
 *
 * @package    PHP_Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/whatthejeff/php-exporter
 */
class Exporter
{
    /**
     * The value to export
     *
     * @var mixed
     */
    private $value;

    /**
     * Constructs a new exporter for a given value.
     *
     * @param mixed $value The value to export
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Exports a value as a string
     *
     * The output of this method is similar to the output of print_r(), but
     * improved in various aspects:
     *
     *  - NULL is rendered as "null" (instead of "")
     *  - TRUE is rendered as "true" (instead of "1")
     *  - FALSE is rendered as "false" (instead of "")
     *  - Strings are always quoted with single quotes
     *  - Carriage returns and newlines are normalized to \n
     *  - Recursion and repeated rendering is treated properly
     *
     * @param  integer $indentation The indentation level of the 2nd+ line
     * @return string
     */
    public function export($indentation = 0)
    {
        return $this->recursiveExport($this->value, $indentation);
    }

    /**
     * Exports a value as a string.
     *
     * @return string
     * @see    Whatthejeff\PHP\Exporter\Exporter::export
     */
    public function __toString()
    {
        return $this->export();
    }

    /**
     * Recursive implementation of export
     *
     * @param  mixed $value The value to export
     * @param  integer $indentation The indentation level of the 2nd+ line
     * @param  Whatthejeff\PHP\Exporter\Context $processed Contains all objects
     *                                                     and arrays that have
     *                                                     previously been
     *                                                     rendered
     * @return string
     * @see    Whatthejeff\PHP\Exporter\Exporter::export
     */
    protected function recursiveExport(&$value, $indentation, $processed = null)
    {
        if ($value === NULL) {
            return 'null';
        }

        if ($value === TRUE) {
            return 'true';
        }

        if ($value === FALSE) {
            return 'false';
        }

        if (is_float($value) && floatval(intval($value)) === $value) {
            return "$value.0";
        }

        if (is_resource($value)) {
            return sprintf(
              'resource(%d) of type (%s)',
              $value,
              get_resource_type($value)
            );
        }

        if (is_string($value)) {
            // Match for most non printable chars somewhat taking multibyte chars into account
            if (preg_match('/[^\x09-\x0d\x20-\xff]/', $value)) {
                return 'Binary String: 0x' . bin2hex($value);
            }

            return "'" .
                   str_replace(array("\r\n", "\n\r", "\r"), array("\n", "\n", "\n"), $value) .
                   "'";
        }

        $whitespace = str_repeat(' ', 4 * $indentation);

        if (!$processed) {
            $processed = new Context;
        }

        if (is_array($value)) {
            if (($key = $processed->contains($value)) !== false) {
                return 'Array &' . $key;
            }

            $key = $processed->add($value);
            $values = '';

            if (count($value) > 0) {
                foreach ($value as $k => $v) {
                    $values .= sprintf(
                      '%s    %s => %s' . "\n",
                      $whitespace,
                      new Exporter($k),
                      $this->recursiveExport($value[$k], $indentation + 1, $processed)
                    );
                }

                $values = "\n" . $values . $whitespace;
            }

            return sprintf('Array &%s (%s)', $key, $values);
        }

        if (is_object($value)) {
            $class = get_class($value);

            if ($hash = $processed->contains($value)) {
                return sprintf('%s Object &%s', $class, $hash);
            }

            $hash = $processed->add($value);
            $values = '';

            $exporter = new Exporter($value);
            $array = $exporter->toArray();

            if (count($array) > 0) {
                foreach ($array as $k => $v) {
                    $values .= sprintf(
                      '%s    %s => %s' . "\n",
                      $whitespace,
                      new Exporter($k),
                      $this->recursiveExport($v, $indentation + 1, $processed)
                    );
                }

                $values = "\n" . $values . $whitespace;
            }

            return sprintf('%s Object &%s (%s)', $class, $hash, $values);
        }

        return var_export($value, true);
    }

    /**
     * Exports a value into a single-line string
     *
     * The output of this method is similar to the output of
     * Whatthejeff\PHP\Exporter\Exporter::export. This method guarantees
     * thought that the result contains now newlines.
     *
     * Newlines are replaced by the visible string '\n'. Contents of arrays
     * and objects (if any) are replaced by '...'.
     *
     * @return string
     * @see    Whatthejeff\PHP\Exporter\Exporter::export
     */
    public function shortenedExport()
    {
        if (is_string($this->value)) {
            $string = $this->export();

            if (strlen($string) > 40) {
                $string = substr($string, 0, 30) . '...' . substr($string, -7);
            }

            return str_replace("\n", '\n', $string);
        }

        if (is_object($this->value)) {
            return sprintf(
              '%s Object (%s)',
              get_class($this->value),
              count($this->toArray()) > 0 ? '...' : ''
            );
        }

        if (is_array($this->value)) {
            return sprintf(
              'Array (%s)',
              count($this->value) > 0 ? '...' : ''
            );
        }

        return $this->export();
    }

    /**
     * Converts an object to an array containing all of its private, protected
     * and public properties.
     *
     * @return array
     */
    public function toArray()
    {
        if (!is_object($this->value)) {
            return (array)$this->value;
        }

        $array = array();

        foreach ((array)$this->value as $key => $value) {
            // properties are transformed to keys in the following way:

            // private   $property => "\0Classname\0property"
            // protected $property => "\0*\0property"
            // public    $property => "property"

            if (preg_match('/^\0.+\0(.+)$/', $key, $matches)) {
                $key = $matches[1];
            }

            $array[$key] = $value;
        }

        // Some internal classes like SplObjectStorage don't work with the
        // above (fast) mechanism nor with reflection
        // Format the output similarly to print_r() in this case
        if ($this->value instanceof \SplObjectStorage) {
            foreach ($this->value as $key => $value) {
                $array[spl_object_hash($value)] = array(
                    'obj' => $value,
                    'inf' => $this->value->getInfo(),
                );
            }
        }

        return $array;
    }
}
