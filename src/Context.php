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
 * A context containing previously rendered arrays and objects when recursively
 * exporting a value.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Adam Harvey <aharvey@php.net>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class Context {
    /**
     * Previously seen arrays.
     *
     * @var array[] $arrays
     */
    protected $arrays = array();

    /**
     * Previously seen objects.
     *
     * @var SplObjectStorage $objects
     */
    protected $objects;

    /** Initialises the context. */
    public function __construct()
    {
        $this->arrays = array();
        $this->objects = new \SplObjectStorage;
    }

    /**
     * Adds a value to the export context.
     *
     * @param mixed $value The value to add.
     * @return mixed The ID of the stored value, either as a string or integer.
     * @throws SebastianBergmann\Exporter\Exception Thrown if $value is not an array or object.
     */
    public function add(&$value)
    {
        if (is_array($value)) {
            return $this->addArray($value);
        }

        else if (is_object($value)) {
            return $this->addObject($value);
        }

        throw new Exception(
          'Only arrays and objects are supported'
        );
    }

    /**
     * Checks if the given value exists within the context.
     *
     * @param mixed $value The value to check.
     * @return mixed The string or integer ID of the stored value if it has
     *               already been seen, or boolean false if the value is not
     *               stored.
     * @throws SebastianBergmann\Exporter\Exception Thrown if $value is not an array or object.
     */
    public function contains(&$value)
    {
        if (is_array($value)) {
            return $this->containsArray($value);
        }

        else if (is_object($value)) {
            return $this->containsObject($value);
        }

        throw new Exception(
          'Only arrays and objects are supported'
        );
    }

    /**
     * Stores an array within the context.
     *
     * @param array $value The value to store.
     * @return integer The internal ID of the array.
     */
    protected function addArray(array &$value)
    {
        if (($key = $this->containsArray($value)) !== FALSE) {
            return $key;
        }

        $this->arrays[] = &$value;

        return count($this->arrays) - 1;
    }

    /**
     * Stores an object within the context.
     *
     * @param object $value
     * @return string The ID of the object.
     */
    protected function addObject($value)
    {
        if (!$this->objects->contains($value)) {
            $this->objects->attach($value);
        }

        return spl_object_hash($value);
    }

    /**
     * Checks if the given array exists within the context.
     *
     * @param array $value The array to check.
     * @return mixed The integer ID of the array if it exists, or boolean false
     *               otherwise.
     */
    protected function containsArray(array &$value)
    {
        $keys = array_keys($this->arrays, $value, TRUE);
        $gen = '_Exporter_Key_'.hash('sha512', microtime(TRUE));

        foreach ($keys as $key) {
            $this->arrays[$key][$gen] = $gen;

            if (isset($value[$gen]) && $value[$gen] === $gen) {
                unset($this->arrays[$key][$gen]);
                return $key;
            }

            unset($this->arrays[$key][$gen]);
        }

        return FALSE;
    }

    /**
     * Checks if the given object exists within the context.
     *
     * @param object $value The object to check.
     * @return mixed The string ID of the object if it exists, or boolean false
     *               otherwise.
     */
    protected function containsObject($value)
    {
        if ($this->objects->contains($value)) {
            return spl_object_hash($value);
        }

        return FALSE;
    }
}
