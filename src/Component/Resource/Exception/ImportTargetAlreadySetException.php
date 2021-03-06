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
use DAG\Component\Resource\Model\ImportTargetInterface;
use DAG\Component\Resource\Model\ImportSubjectInterface;

/**
 * Import target already set exception.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class ImportTargetAlreadySetException extends RuntimeException implements ResourceException
{
    /**
     * Constructor.
     *
     * @param ImportTargetInterface $target
     * @param ImportSubjectInterface $subject
     */
    public function __construct(ImportTargetInterface $target, ImportSubjectInterface $subject)
    {
        $targetClass = get_class($target);
        $subjectClass = get_class($subject);

        $this->message = sprintf(
            'Import target for class "%s" has already been set. Import target class "%s" may not be set as its target.',
            $subjectClass,
            $targetClass
        );
    }
}
