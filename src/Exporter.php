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

use SebastianBergmann\RecursionContext\Context;

/**
 * A nifty utility for visualizing PHP variables.
 *
 * <code>
 * <?php declare(strict_types=1);
 * use SebastianBergmann\Exporter\Exporter;
 *
 * $exporter = new Exporter;
 * print $exporter->export(new Exception);
 * </code>
 */
final class Exporter
{
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
     */
    public function export($value, int $indentation = 0): string
    {
        return $this->recursiveExport($value, $indentation);
    }

    public function shortenedRecursiveExport(&$data, Context $context = null): string
    {
        $result   = [];
        $exporter = new self;

        if (!$context) {
            $context = new Context;
        }

        $array = $data;
        $context->add($data);

        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                if ($context->contains($data[$key]) !== false) {
                    $result[] = '*RECURSION*';
                } else {
                    $result[] = \sprintf(
                        'array(%s)',
                        $this->shortenedRecursiveExport($data[$key], $context)
                    );
                }
            } else {
                $result[] = $exporter->shortenedExport($value);
            }
        }

        return \implode(', ', $result);
    }

    /**
     * Exports a value into a single-line string
     *
     * The output of this method is similar to the output of
     * SebastianBergmann\Exporter\Exporter::export().
     *
     * Newlines are replaced by the visible string '\n'.
     * Contents of arrays and objects (if any) are replaced by '...'.
     *
     * @see \SebastianBergmann\Exporter\Exporter::export
     */
    public function shortenedExport($value): string
    {
        if (\is_string($value)) {
            $string = \str_replace("\n", '', $this->export($value));

            if (\mb_strlen($string) > 40) {
                $string = \mb_substr($string, 0, 30) . '...' . \mb_substr($string, -7);
            }

            return $string;
        }

        if (\is_object($value)) {
            return \sprintf(
                '%s Object (%s)',
                \get_class($value),
                \count($this->toArray($value)) > 0 ? '...' : ''
            );
        }

        if (\is_array($value)) {
            return \sprintf(
                'Array (%s)',
                \count($value) > 0 ? '...' : ''
            );
        }

        return $this->export($value);
    }

    /**
     * Converts an object to an array containing all of its private, protected
     * and public properties.
     */
    public function toArray($value): array
    {
        if (!\is_object($value)) {
            return (array) $value;
        }

        $array = [];

        foreach ((array) $value as $key => $val) {
            // Exception traces commonly reference hundreds to thousands of
            // objects currently loaded in memory. Including them in the result
            // has a severe negative performance impact.
            if ("\0Error\0trace" === $key || "\0Exception\0trace" === $key) {
                continue;
            }

            // properties are transformed to keys in the following way:
            // private   $property => "\0Classname\0property"
            // protected $property => "\0*\0property"
            // public    $property => "property"
            if (\preg_match('/^\0.+\0(.+)$/', (string) $key, $matches)) {
                $key = $matches[1];
            }

            // See https://github.com/php/php-src/commit/5721132
            if ($key === "\0gcdata") {
                continue;
            }

            $array[$key] = $val;
        }

        // Some internal classes like SplObjectStorage don't work with the
        // above (fast) mechanism nor with reflection in Zend.
        // Format the output similarly to print_r() in this case
        if ($value instanceof \SplObjectStorage) {
            // However, the fast method does work in HHVM, and exposes the
            // internal implementation. Hide it again.
            if (\property_exists(\SplObjectStorage::class, '__storage')) {
                unset($array['__storage']);
            } elseif (\property_exists(\SplObjectStorage::class, 'storage')) {
                unset($array['storage']);
            }

            if (\property_exists(\SplObjectStorage::class, '__key')) {
                unset($array['__key']);
            }

            foreach ($value as $key => $val) {
                $array[\spl_object_hash($val)] = [
                    'obj' => $val,
                    'inf' => $value->getInfo(),
                ];
            }
        }

        return $array;
    }

    /**
     * @param mixed $value
     */
    private function recursiveExport(&$value, int $indentation, Context $processed = null): string
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (\is_float($value) && (float) ((int) $value) === $value) {
            return "$value.0";
        }

        if (\is_resource($value)) {
            return \sprintf(
                'resource(%d) of type (%s)',
                (int) $value,
                \get_resource_type($value)
            );
        }

        if (\is_string($value)) {
            // Match for most non printable chars somewhat taking multibyte chars into account
            if (\preg_match('/[^\x09-\x0d\x1b\x20-\xff]/', $value)) {
                return 'Binary String: 0x' . \bin2hex($value);
            }

            return "'" .
            \str_replace(
                '<lf>',
                "\n",
                \str_replace(
                    ["\r\n", "\n\r", "\r", "\n"],
                    ['\r\n<lf>', '\n\r<lf>', '\r<lf>', '\n<lf>'],
                    $value
                )
            ) .
            "'";
        }

        $whitespace = \str_repeat(' ', 4 * $indentation);

        if (!$processed) {
            $processed = new Context;
        }

        if (\is_array($value)) {
            if (($key = $processed->contains($value)) !== false) {
                return 'Array &' . $key;
            }

            $array  = $value;
            $key    = $processed->add($value);
            $values = '';

            if (\is_array($array) && !empty($array)) {
                foreach ($array as $k => $v) {
                    $values .= \sprintf(
                        '%s    %s => %s' . "\n",
                        $whitespace,
                        $this->recursiveExport($k, $indentation),
                        $this->recursiveExport($value[$k], $indentation + 1, $processed)
                    );
                }

                $values = "\n" . $values . $whitespace;
            }

            return \sprintf('Array &%s (%s)', $key, $values);
        }

        if (\is_object($value)) {
            $class = \get_class($value);

            if ($hash = $processed->contains($value)) {
                return \sprintf('%s Object &%s', $class, $hash);
            }

            $hash   = $processed->add($value);
            $values = '';
            $array  = $this->toArray($value);

            if (\count($array) > 0) {
                foreach ($array as $k => $v) {
                    $values .= \sprintf(
                        '%s    %s => %s' . "\n",
                        $whitespace,
                        $this->recursiveExport($k, $indentation),
                        $this->recursiveExport($v, $indentation + 1, $processed)
                    );
                }

                $values = "\n" . $values . $whitespace;
            }

            return \sprintf('%s Object &%s (%s)', $class, $hash, $values);
        }

        return \var_export($value, true);
    }
}
