<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:07
 */

namespace AppBundle\Enum;

/**
 * Class RoomConfig
 * @package AppBundle\Enum
 */
class RoomConfig
{
    const SINGLE_ROOM = 1;
    const DOUBLE_ROOM = 2;
    const TRIPLE_ROOM = 3;
    const QUADRUPLE_ROOM = 4;
    const QUINTUPLE_ROOM = 5;
    const HEXTUPLE_ROOM = 6;
    const ROOM_CAPACITY = 'Room capacity';

    const ROOM_CAPACITIES = [
        self::SINGLE_ROOM => self::SINGLE_ROOM,
        self::DOUBLE_ROOM => self::DOUBLE_ROOM,
        self::TRIPLE_ROOM => self::TRIPLE_ROOM,
        self::QUADRUPLE_ROOM => self::QUADRUPLE_ROOM,
        self::QUINTUPLE_ROOM => self::QUINTUPLE_ROOM,
        self::HEXTUPLE_ROOM => self::HEXTUPLE_ROOM,
        self::ROOM_CAPACITY => null,
    ];
}
