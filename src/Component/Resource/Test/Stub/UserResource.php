<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Component\Resource\Test\Stub;

use DAG\Component\Resource\Model\User;
use DAG\Component\Resource\Model\UserInterface;

/**
 * User resource stub.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class UserResource extends User
{
    public function getId()
    {
        // Do nothing.
    }

    public function setUsername($username)
    {
        // Do nothing.
    }

    public function getRoles()
    {
        // Do nothing.
    }

    public function getPassword()
    {
        // Do nothing.
    }

    public function getSalt()
    {
        // Do nothing.
    }

    public function getUsername()
    {
        // Do nothing.
    }

    public function eraseCredentials()
    {
        // Do nothing.
    }

    public function serialize()
    {
        // Do nothing.
    }

    public function unserialize($serialized)
    {
        // Do nothing.
    }
}
