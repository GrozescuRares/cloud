<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:46
 */

namespace AppBundle\Helper;

use AppBundle\Enum\UserConfig;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;

/**
 * Class ValidateUser
 * @package AppBundle\Helper
 */
class ValidateUser
{
    /**
     * @param mixed $userRole
     */
    public static function checkIfUserHasRole($userRole)
    {
        if (empty($userRole)) {
            throw new NoRoleException('This user has no role.');
        }
    }

    /**
     * @param mixed $userRole
     */
    public static function checkIfUserHasHighRole($userRole)
    {
        if (array_search($userRole, UserConfig::HIGH_ROLES) === false) {
            throw new InappropriateUserRoleException('This user has no high role.');
        }
    }

    /**
     * @param mixed $userRole
     */
    public static function checkIfUserHasRoleOwner($userRole)
    {
        if ($userRole !== UserConfig::ROLE_OWNER) {
            throw new InappropriateUserRoleException('This user is not an owner.');
        }
    }
}
