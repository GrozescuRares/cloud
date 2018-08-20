<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 10:22
 */

namespace Tests\AppBundle\Stub;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;

/**
 * Class GetRepositroyNonValid
 * @package Tests\AppBundle\Stub
 */
class GetRepositroyNonValid
{
    /**
     * @return User
     */
    public function findOneBy()
    {
        return null;
    }

    /**
     * @return Role
     */
    public function find()
    {
        return new Role();
    }
}
