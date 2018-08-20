<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 10:49
 */

namespace Tests\AppBundle\Stub;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;

/**
 * Class GetRepositoryUserExpiredTokenStub
 * @package Tests\AppBundle\Stub
 */
class GetRepositoryUserExpiredTokenStub
{
    /**
     * @return User
     */
    public function findOneBy()
    {
        $user = new User();

        return $user->setExpirationDate($this->generateTokenExpirationTime())
                    ->setEmail('grozescu@grozescu');
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
        $dateTime->modify('-2 minutes');

        return $dateTime;
    }
}
