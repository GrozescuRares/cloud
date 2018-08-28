<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 11:12
 */

namespace AppBundle\Dto;

use AppBundle\Entity\Role;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserDto
 * @package AppBundle\Dto
 */
class UserDto
{
    /**
     * @var string $username
     *
     * @Assert\NotBlank(groups={"register", "edit-my-account", "edit-user"}, message="constraints.username")
     * @Assert\Length(min=5, groups={"register", "edit-my-account", "edit-user"})
     */
    public $username;
    /**
     * @var Role $role
     *
     * @Assert\NotBlank(groups={"register", "edit-my-account", "edit-user"}, message="constraints.role")
     */
    public $role;

    public $firstName;
    public $lastName;
    public $email;
    public $dateOfBirth;
    public $expirationDate;
}
