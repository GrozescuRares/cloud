<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 10:21
 */

namespace Tests\AppBundle\Stub;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;

/**
 * Class UserRepositoryStub
 * @package Tests\AppBundle\Stub
 */
class GetRepositoryValidStub
{
    /**
     * @return User
     */
    public function findOneBy()
    {
        $user = new User();

        return $user->setExpirationDate($this->generateTokenExpirationTime());
    }

    /**
     * @return Role
     */
    public function find()
    {
        return new Role();
    }

    private function generateTokenExpirationTime()
    {
        $dateTime = new \DateTime();
        $dateTime->modify('+2 minutes');

        return $dateTime;
    }
}
