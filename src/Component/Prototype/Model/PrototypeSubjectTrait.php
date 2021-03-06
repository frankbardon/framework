<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Component\Prototype\Model;

/**
 * Prototype subject trait.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
trait PrototypeSubjectTrait
{
    /**
     * Prototype.
     *
     * @var PrototypeInterface
     */
    protected $prototype;


    /**
     * {@inheritdoc}
     */
    public function getPrototype()
    {
        return $this->prototype;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrototype(PrototypeInterface $prototype = null)
    {
        $this->prototype = $prototype;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function getDescription()
    {
        return $this->getPrototype()->getParsedDescription($this);
    }
}
