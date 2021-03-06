<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace DAG\Bundle\ResourceBundle\Exception\Driver;

/**
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class InvalidDriverException extends \Exception
{
    public function __construct($driver, $className)
    {
        parent::__construct(sprintf(
            'Driver "%s" is not supported by %s.',
            $driver,
            $className
        ));
    }
}
