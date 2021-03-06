<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Model;

use Mockery;
use DAG\Component\Field\Exception\InvalidFieldTypeException;

/**
 * Invalid field type exception tests.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class InvalidFieldTypeExceptionTest extends \Codeception\TestCase\Test
{
    public function testExceptionMessageContainsFieldNameIfString()
    {
        $exception = new InvalidFieldTypeException('FIELD');
        $this->assertContains('FIELD', $exception->getMessage());
    }
}
