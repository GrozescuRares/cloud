<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 30.08.2018
 * Time: 17:33
 */

namespace AppBundle\Service;

use AppBundle\Adapter\RoleAdapter;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Enum\UserConfig;
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;

/**
 * Class RoleService
 * @package AppBundle\Service
 */
class RoleService
{

    protected $roleAdapter;
    protected $em;

    /**
     * RoleService constructor.
     * @param EntityManager $em
     * @param RoleAdapter   $roleAdapter
     */
    public function __construct(EntityManager $em, RoleAdapter $roleAdapter)
    {
        $this->em = $em;
        $this->roleAdapter = $roleAdapter;
    }

    /**
     * Returns an array of roles that contains every roles except
     * $user's role, ROLE_CLIENT, and the roles that are higher in hierarchy.
     * The elements of the array will look like 'role_description' => role entity
     *
     *  Example: 1. For a user with ROLE_OWNER, the function will return
     *              an array containing all the roles except ROLE_OWNER
     *              and ROLE_CLIENT.
     *           2. For a user with ROLE_MANAGER the function will return
     *              an array containing all the roles except ROLE_OWNER,
     *              ROLE_MANAGER and ROLE_CLIENT.
     *
     * @param User $user
     *
     * @return array
     */
    public function getUserCreationalRoleDtos(User $user)
    {
        $userRole = ValidateUserHelper::checkIfUserHasRole($user->getRoles());

        $roles = $this->em->getRepository(Role::class)->findAll();
        $result = [];

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleDescription = $role->getDescription();

            if (!($roleDescription === UserConfig::ROLE_CLIENT || $roleDescription === $userRole)) {
                $result[$roleDescription] = $this->roleAdapter->convertToDto($role);
            }
        }

        if ($userRole === UserConfig::ROLE_MANAGER) {
            unset($result[UserConfig::ROLE_OWNER]);
        }

        return $result;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserCreationalRoles(User $user)
    {
        $userRole = ValidateUserHelper::checkIfUserHasRole($user->getRoles());

        $roles = $this->em->getRepository(Role::class)->findAll();
        $result = [];

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleDescription = $role->getDescription();

            if (!($roleDescription === UserConfig::ROLE_CLIENT || $roleDescription === $userRole)) {
                $result[$roleDescription] = $role;
            }
        }

        if ($userRole === UserConfig::ROLE_MANAGER) {
            unset($result[UserConfig::ROLE_OWNER]);
        }

        return $result;
    }
}
