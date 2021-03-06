<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAGTest\Component\Prototype\Model;

use Mockery;
use DAG\Component\Prototype\Exception\PrototypeNotFoundException;

/**
 * Prototype not found exception tests.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class PrototypeNotFoundExceptionTest extends \Codeception\TestCase\Test
{
    public function testExceptionMessageContainsPrototypeNameIfString()
    {
        $exception = new PrototypeNotFoundException('FIELD');
        $this->assertContains('FIELD', $exception->getMessage());
    }

    public function testExceptionMessageContainsPrototypeIdIfInteger()
    {
        $exception = new PrototypeNotFoundException(1);
        $this->assertContains('1', $exception->getMessage());
    }
}
