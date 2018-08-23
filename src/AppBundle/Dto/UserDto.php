<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 11:12
 */

namespace AppBundle\Dto;

use AppBundle\Entity\Role;

/**
 * Class UserDto
 * @package AppBundle\Dto
 */
class UserDto
{
    /**
     * @var string $username
     */
    public $username;
    /**
     * @var Role $role
     */
    public $role;
}
