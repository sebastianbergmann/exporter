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
class ContextTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    protected function setUp()
    {
        $this->context = new Context();
    }

    public function failsProvider()
    {
        return array(
            array(true),
            array(false),
            array(null),
            array('string'),
            array(1),
            array(1.5),
            array(fopen('php://memory', 'r'))
        );
    }

    public function valuesProvider()
    {
        $obj2 = new \stdClass();
        $obj2->foo = 'bar';

        $obj3 = (object) array(1,2,"Test\r\n",4,5,6,7,8);

        $obj = new \stdClass();
        //@codingStandardsIgnoreStart
        $obj->null = null;
        //@codingStandardsIgnoreEnd
        $obj->boolean = true;
        $obj->integer = 1;
        $obj->double = 1.2;
        $obj->string = '1';
        $obj->text = "this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext";
        $obj->object = $obj2;
        $obj->objectagain = $obj2;
        $obj->array = array('foo' => 'bar');
        $obj->array2 = array(1,2,3,4,5,6);
        $obj->array3 = array($obj, $obj2, $obj3);
        $obj->self = $obj;

        $storage = new \SplObjectStorage();
        $storage->attach($obj2);
        $storage->foo = $obj2;

        return array(
            array($obj, spl_object_hash($obj)),
            array($obj2, spl_object_hash($obj2)),
            array($obj3, spl_object_hash($obj3)),
            array($storage, spl_object_hash($storage)),
            array($obj->array, 0),
            array($obj->array2, 0),
            array($obj->array3, 0)
        );
    }

    /**
     * @covers       SebastianBergmann\Exporter\Context::add
     * @uses         SebastianBergmann\Exporter\Exception
     * @dataProvider failsProvider
     */
    public function testAddFails($value)
    {
        $this->setExpectedException(
          'SebastianBergmann\\Exporter\\Exception',
          'Only arrays and objects are supported'
        );
        $this->context->add($value);
    }

    /**
     * @covers       SebastianBergmann\Exporter\Context::contains
     * @uses         SebastianBergmann\Exporter\Exception
     * @dataProvider failsProvider
     */
    public function testContainsFails($value)
    {
        $this->setExpectedException(
          'SebastianBergmann\\Exporter\\Exception',
          'Only arrays and objects are supported'
        );
        $this->context->contains($value);
    }

    /**
     * @covers       SebastianBergmann\Exporter\Context::add
     * @dataProvider valuesProvider
     */
    public function testAdd($value, $key)
    {
        $this->assertEquals($key, $this->context->add($value));

        // Test we get the same key on subsequent adds
        $this->assertEquals($key, $this->context->add($value));
    }

    /**
     * @covers       SebastianBergmann\Exporter\Context::contains
     * @uses         SebastianBergmann\Exporter\Context::add
     * @depends      testAdd
     * @dataProvider valuesProvider
     */
    public function testContainsFound($value, $key)
    {
        $this->context->add($value);
        $this->assertEquals($key, $this->context->contains($value));

        // Test we get the same key on subsequent calls
        $this->assertEquals($key, $this->context->contains($value));
    }

    /**
     * @covers       SebastianBergmann\Exporter\Context::contains
     * @dataProvider valuesProvider
     */
    public function testContainsNotFound($value)
    {
        $this->assertFalse($this->context->contains($value));
    }
}
