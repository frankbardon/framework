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

use RuntimeException;

/**
 * Import failed exception.
 *
 * Thrown when importer can not run properly.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class ImportFailedException extends RuntimeException implements ResourceException
{
}
