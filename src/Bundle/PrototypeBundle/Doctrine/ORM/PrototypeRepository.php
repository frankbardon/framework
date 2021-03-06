<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\PrototypeBundle\Doctrine\ORM;

use DAG\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use DAG\Component\Prototype\Repository\PrototypeRepositoryInterface;

/**
 * Prototype repository.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class PrototypeRepository extends EntityRepository implements PrototypeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPrototypes()
    {
        return $this->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrototype($prototypeId)
    {
        return $this->find($prototypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrototypeByName($prototypeName)
    {
        return $this->findOneBy(array('name' => $prototypeName));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'prototype';
    }
}
