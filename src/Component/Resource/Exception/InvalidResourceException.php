<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Component\Resource\Exception;

use InvalidArgumentException;

/**
 * Invalid resource exception.
 *
 * Thrown when invalid resource class is provided to a builder.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class InvalidResourceException extends InvalidArgumentException implements ResourceException
{
    const MESSAGE_TEMPLATE = 'Expecting resource to be an instance of "%s", got %s.';

    /**
     * Constructor.
     *
     * @param string $expected
     * @param mixed $resource
     */
    public function __construct($expected, $resource)
    {
        $type = is_object($resource) ? get_class($resource) : gettype($resource);

        $this->message = sprintf(self::MESSAGE_TEMPLATE, $expected, $type);
    }
}
