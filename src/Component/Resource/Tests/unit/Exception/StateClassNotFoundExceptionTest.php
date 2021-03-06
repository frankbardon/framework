<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAGTest\Component\Resource\Exception;

use Mockery;
use Codeception\TestCase\Test;
use DAG\Component\Resource\Exception\StateClassNotFoundException;

/**
 * State class not found exception tests.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class StateClassNotFoundExceptionTest extends Test
{
    public function _before()
    {
        $this->exception = new StateClassNotFoundException('CLASSNAME');
    }

    public function testExceptionIsStateException()
    {
        $this->assertInstanceOf(
            'DAG\Component\Resource\Exception\StateException',
            $this->exception
        );
    }

    public function testExceptionIsCorrectExceptionType()
    {
        $this->assertInstanceOf(
            'InvalidArgumentException',
            $this->exception
        );
    }

    /**
     * @expectedException DAG\Component\Resource\Exception\StateClassNotFoundException
     */
    public function testExceptionIsThrowable()
    {
        throw $this->exception;
    }

    public function testExceptionDisplaysExpectedClassName()
    {
        $this->assertAttributeContains('CLASSNAME', 'message', $this->exception);
    }
}
