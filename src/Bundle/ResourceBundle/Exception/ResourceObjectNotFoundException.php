<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\ResourceBundle\Exception;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Resource object not found exception.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class ResourceObjectNotFoundException extends ServiceNotFoundException
{
}
