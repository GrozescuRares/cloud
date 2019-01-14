<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 16:33
 */

namespace AppBundle\Enum;

use AppBundle\Repository\HotelRepository;
use AppBundle\Repository\ReservationRepository;
use AppBundle\Repository\RoleRepository;
use AppBundle\Repository\RoomRepository;
use AppBundle\Repository\UserRepository;

/**
 * Class RepositoryConfig
 * @package AppBundle\Enum
 */
class RepositoryConfig
{
    const USER_REPOSITORY = UserRepository::class;
    const ROLE_REPOSITORY = RoleRepository::class;
    const HOTEL_REPOSITORY = HotelRepository::class;
    const ROOM_REPOSITORY = RoomRepository::class;
    const RESERVATION_REPOSITORY = ReservationRepository::class;
}
