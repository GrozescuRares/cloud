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

    const ROOM_CAPACITIES = [
        'capacity.one' => self::SINGLE_ROOM,
        'capacity.two' => self::DOUBLE_ROOM,
        'capacity.three' => self::TRIPLE_ROOM,
        'capacity.four' => self::QUADRUPLE_ROOM,
        'capacity.five' => self::QUINTUPLE_ROOM,
        'capacity.six' => self::HEXTUPLE_ROOM,
        'capacity.title' => null,
    ];

    const YES = 'Yes';
    const NO = 'No';

    const ALLOWED = [
        true => self::YES,
        false => self::NO,
        "" => "",
    ];
    const IS = 'is';
    const IS_NOT = 'is not';

    const AVAILABILITY = [
        true => self::IS,
        false => self::IS_NOT,
    ];

    const CONVERT = [
        self::YES => true,
        self::NO => false,
        "" => "",
    ];

    const CURRENCY = ' $';
}
