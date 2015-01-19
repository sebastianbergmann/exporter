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
 * Exporter for visualizing \SplObjectStorage instances.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class SplObjectStorageExporter extends ObjectExporter
{
    /**
     * Returns whether the exporter can export a given value.
     *
     * @param  mixed   $value The value to export.
     * @return boolean
     */
    public function accepts($value)
    {
        return $value instanceof \SplObjectStorage;
    }

    /**
     * Converts a PHP value to an array.
     *
     * @param  mixed $value
     * @return array
     */
    public function toArray($value)
    {
        $array = parent::toArray($value);

        // Remove HHVM internal representations
        if (property_exists('\SplObjectStorage', '__storage')) {
          unset($array['__storage']);
        } elseif (property_exists('\SplObjectStorage', 'storage')) {
          unset($array['storage']);
        }
        if (property_exists('\SplObjectStorage', '__key')) {
          unset($array['__key']);
        }

        // Some internal classes like SplObjectStorage don't work with our
        // normal method of converting objects to arrays.
        // Format the output similarly to print_r() in this case
        foreach ($value as $key => $val) {
            $array[spl_object_hash($val)] = array(
                'obj' => $val,
                'inf' => $value->getInfo(),
            );
        }

        return $array;
    }
}
