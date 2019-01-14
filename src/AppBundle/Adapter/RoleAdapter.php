<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 30.08.2018
 * Time: 15:17
 */

namespace AppBundle\Adapter;

use AppBundle\Dto\RoleDto;
use AppBundle\Entity\Role;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class RoleAdapter
 * @package AppBundle\Adapter
 */
class RoleAdapter
{
    protected $propertyAccessor;

    /**
     * RoomAdapter constructor.
     */
    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param Role $role
     *
     * @return RoleDto
     */
    public function convertToDto(Role $role)
    {
        $roleDto = new RoleDto();

        foreach ($roleDto as $property => $value) {
            $roleDto->$property = $this->propertyAccessor->getValue($role, $property);
        }

        return $roleDto;
    }

    /**
     * @param RoleDto $roleDto
     * @param Role    $role
     * @return Role
     */
    public function convertToEntity(RoleDto $roleDto, Role $role = null)
    {
        if (empty($role)) {
            $role = new Role();
        }

        foreach ($roleDto as $property => $value) {
            if (!empty($value)  && !is_object($value)) {
                $this->propertyAccessor->setValue($role, $property, $value);
            }
        }

        return $role;
    }

}
