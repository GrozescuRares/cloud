<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 16:32
 */

namespace AppBundle\Enum;

use AppBundle\Entity\Hotel;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\Role;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;

/**
 * Class EntityConfig
 * @package AppBundle\Enum
 */
class EntityConfig
{
    const USER = User::class;
    const ROLE = Role::class;
    const HOTEL = Hotel::class;
    const ROOM = Room::class;
    const RESERVATION = Reservation::class;
}
