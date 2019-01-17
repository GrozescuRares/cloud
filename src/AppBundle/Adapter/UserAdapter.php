<?php

namespace AppBundle\Adapter;

use AppBundle\Dto\UserDto;
use AppBundle\Entity\User;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class UserAdapter
 * @package AppBundle\Adapter
 */
class UserAdapter
{
    protected $propertyAccessor;
    /** @var RoleAdapter */
    protected $roleAdapter;

    /**
     * UserAdapter constructor.
     * @param RoleAdapter $roleAdapter
     */
    public function __construct(RoleAdapter $roleAdapter)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->roleAdapter = $roleAdapter;
    }

    /**
     * @param User $user
     *
     * @return UserDto
     */
    public function convertToDto(User $user)
    {
        $userDto = new UserDto();

        foreach ($userDto as $property => $value) {
            $userDto->$property = $this->propertyAccessor->getValue($user, $property);
        }

        $userDto->role = $this->roleAdapter->convertToDto($userDto->role);

        return $userDto;
    }

    /**
     * @param UserDto   $userDto
     * @param User|null $user
     *
     * @return User
     */
    public function convertToEntity(UserDto $userDto, User $user = null)
    {
        if (empty($user)) {
            $user = new User();
        }

        foreach ($userDto as $property => $value) {
            if (!empty($value) && !is_object($value)) {
                $this->propertyAccessor->setValue($user, $property, $value);
            }
        }

        return $user;
    }

    /**
     * @param array $users
     *
     * @return array
     */
    public function convertCollectionToDto($users)
    {
        $usersDto = [];
        foreach ($users as $user) {
            $usersDto[] = $this->convertToDto($user);
        }

        return $usersDto;
    }
}
