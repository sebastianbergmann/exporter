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
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */

namespace SebastianBergmann\Exporter;

/**
 * @package    Exporter
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       https://github.com/sebastianbergmann/exporter
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    protected function setUp()
    {
        $this->factory = new Factory();
    }

    public function valuesProvider()
    {
        return array(
            array(true),
            array(false),
            array(null),
            array('string'),
            array(1),
            array(1.5),
            array(fopen('php://memory', 'r')),
            array(array(1,2,3)),
            array(new \stdClass())
        );
    }

    private function getExporter()
    {
        return $this->getMockBuilder('SebastianBergmann\\Exporter\\BaseExporter')
                    ->disableOriginalConstructor()
                    ->setMethods(array('accepts'))
                    ->getMockForAbstractClass();
    }

    /**
     * @covers       SebastianBergmann\Exporter\Factory::getExporterFor
     * @dataProvider valuesProvider
     */
    public function testFactoryIsInitiallyEmpty($value)
    {
        $this->assertNull($this->factory->getExporterFor($value));
    }

    /**
     * @covers       SebastianBergmann\Exporter\Factory::register
     * @uses         SebastianBergmann\Exporter\Factory::getExporterFor
     * @depends      testFactoryIsInitiallyEmpty
     * @dataProvider valuesProvider
     */
    public function testRegister($value)
    {
        $exporter1 = $this->getExporter();
        $exporter1->expects($this->once())
                  ->method('accepts')
                  ->with($value)
                  ->willReturn(true);

        $this->factory->register($exporter1);
        $this->assertEquals($exporter1, $this->factory->getExporterFor($value));
    }

    /**
     * @covers       SebastianBergmann\Exporter\Factory::unregister
     * @uses         SebastianBergmann\Exporter\Factory::register
     * @uses         SebastianBergmann\Exporter\Factory::getExporterFor
     * @depends      testRegister
     * @dataProvider valuesProvider
     */
    public function testUnregister($value)
    {
        $exporter1 = $this->getExporter();

        $exporter1->expects($this->never())
                  ->method('accepts');

        $this->factory->register($exporter1);
        $this->factory->unregister($exporter1);

        $this->assertNull($this->factory->getExporterFor($value));
    }

    /**
     * @covers       SebastianBergmann\Exporter\Factory::getExporterFor
     * @uses         SebastianBergmann\Exporter\Factory::unregister
     * @uses         SebastianBergmann\Exporter\Factory::register
     * @depends      testRegister
     * @depends      testUnregister
     * @dataProvider valuesProvider
     */
    public function testFactory($value)
    {
        $exporter1 = $this->getExporter();
        $exporter1->expects($this->exactly(3))
                  ->method('accepts')
                  ->with($value)
                  ->willReturn(true);
        $this->factory->register($exporter1);

        // Gets `$exporter1` because its `accepts()` method returns `true`.
        $this->assertEquals($exporter1, $this->factory->getExporterFor($value));

        $exporter2 = $this->getExporter();
        $exporter2->expects($this->exactly(2))
                  ->method('accepts')
                  ->with($value)
                  ->willReturn(false);
        $this->factory->register($exporter2);

        // Gets `$exporter1` again because `$exporter2`'s `accepts()` method
        // returns `false`.
        $this->assertEquals($exporter1, $this->factory->getExporterFor($value));

        $exporter3 = $this->getExporter();
        $exporter3->expects($this->once())
                  ->method('accepts')
                  ->with($value)
                  ->willReturn(true);
        $this->factory->register($exporter3);

        // Gets `$exporter3` because it gets tested first and its `accepts()`
        // method returns `true`.
        $this->assertEquals($exporter3, $this->factory->getExporterFor($value));

        $this->factory->unregister($exporter3);

        // Gets `$exporter1` again because `$exporter3` was removed and
        // `$exporter2`'s `accepts()` method returns `false`.
        $this->assertEquals($exporter1, $this->factory->getExporterFor($value));
    }
}
