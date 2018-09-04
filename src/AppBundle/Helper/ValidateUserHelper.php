<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:46
 */

namespace AppBundle\Helper;

use AppBundle\Entity\User;
use AppBundle\Enum\UserConfig;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;

/**
 * Class ValidateUser
 * @package AppBundle\Helper
 */
class ValidateUserHelper
{
    /**
     * @param mixed $userRole
     * @return mixed
     */
    public static function checkIfUserHasRole($userRole)
    {
        if (empty($userRole)) {
            throw new NoRoleException('This user has no role.');
        }

        return $userRole[0];
    }

    /**
     * @param mixed $userRole
     * @return mixed
     */
    public static function checkIfUserHasHighRole($userRole)
    {
        if (array_search($userRole[0], UserConfig::HIGH_ROLES) === false) {
            throw new InappropriateUserRoleException('This user has no high role.');
        }

        return $userRole[0];
    }

    /**
     * @param mixed $userRole
     * @return mixed
     */
    public static function checkIfUserHasRoleOwner($userRole)
    {
        if ($userRole[0] !== UserConfig::ROLE_OWNER) {
            throw new InappropriateUserRoleException('This user is not an owner.');
        }

        return $userRole[0];
    }

    /**
     * @param User $loggedUser
     *
     * @return mixed
     */
    public static function checkIfUserIsOwnerOrManager(User $loggedUser)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);

        return ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);
    }
}
