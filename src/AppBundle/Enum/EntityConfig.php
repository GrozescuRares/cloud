<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 16:32
 */

namespace AppBundle\Enum;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;

/**
 * Class EntityConfig
 * @package AppBundle\Enum
 */
class EntityConfig
{
    const USER = User::class;
    const ROLE = Role::class;
    const HOTEL = Role::class;
}
