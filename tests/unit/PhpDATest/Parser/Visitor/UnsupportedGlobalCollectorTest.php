<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2017 Marco Muths
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace PhpDATest\Parser\Visitor;

use PhpDA\Parser\Visitor\UnsupportedGlobalCollector;

class UnsupportedGlobalCollectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var UnsupportedGlobalCollector */
    protected $fixture;

    /** @var \PhpDA\Entity\Adt | \Mockery\MockInterface */
    protected $adt;

    /** @var \PhpDA\Parser\Filter\NodeNameInterface | \Mockery\MockInterface */
    protected $nodeNameFilter;

    protected function setUp()
    {
        $this->adt = \Mockery::mock('PhpDA\Entity\Adt');
        $this->nodeNameFilter = \Mockery::mock('PhpDA\Parser\Filter\NodeNameInterface');

        $this->fixture = new UnsupportedGlobalCollector;
        $this->fixture->setAdt($this->adt);
        $this->fixture->setNodeNameFilter($this->nodeNameFilter);
    }

    public function testNotCollectingByInvalidNode()
    {
        $node = \Mockery::mock('PhpParser\Node');
        $this->fixture->leaveNode($node);
    }

    public function testCollectingByFilteredToNull()
    {
        $node = \Mockery::mock('PhpParser\Node\Stmt\Global_');
        $this->nodeNameFilter->shouldReceive('filter')->andReturn(null);
        $this->fixture->leaveNode($node);
    }

    public function testCollecting()
    {
        $testcase = $this;
        $attributes = array('foo' => 'bar');
        $node = \Mockery::mock('PhpParser\Node\Stmt\Global_');
        $node->shouldReceive('getAttributes')->andReturn($attributes);
        $this->nodeNameFilter->shouldReceive('filter')->once()->andReturnUsing(
            function ($object) {
                return $object;
            }
        );
        $this->adt->shouldReceive('addUnsupportedStmt')->once()->andReturnUsing(
            function ($object) use ($testcase) {
                /** @var \PhpParser\Node\Name $object */
                $testcase->assertInstanceOf('PhpParser\Node\Name', $object);
                $testcase->assertSame($object->toString(), 'global');
            }
        );

        $this->fixture->leaveNode($node);
    }
}
