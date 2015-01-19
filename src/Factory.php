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
 * Factory of exporters for visualizing PHP variables.
 *
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class Factory
{
    /**
     * @var BaseExporter[]
     */
    private $exporters = array();

    /**
     * Returns the correct exporter for exporting a given value.
     *
     * @param  mixed        $value The value to export.
     * @return BaseExporter
     */
    public function getExporterFor($value)
    {
        foreach ($this->exporters as $exporter) {
            if ($exporter->accepts($value)) {
                return $exporter;
            }
        }
    }

    /**
     * Registers a new exporter.
     *
     * @param BaseExporter $exporter The registered exporter
     */
    public function register(BaseExporter $exporter)
    {
        array_unshift($this->exporters, $exporter);
    }

    /**
     * Unregisters an exporter.
     *
     * This exporter will no longer be returned by getExporterFor().
     *
     * @param BaseExporter $exporter The unregistered exporter
     */
    public function unregister(BaseExporter $exporter)
    {
        foreach ($this->exporters as $key => $_exporter) {
            if ($exporter === $_exporter) {
                unset($this->exporters[$key]);
            }
        }
    }
}
