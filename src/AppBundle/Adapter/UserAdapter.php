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

    /**
     * UserAdapter constructor.
     */
    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
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
            $this->propertyAccessor->setValue($user, $property, $value);
        }

        return $user;
    }
}