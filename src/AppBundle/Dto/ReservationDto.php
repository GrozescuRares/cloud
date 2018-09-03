<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 10:06
 */

namespace AppBundle\Dto;

/**
 * Class ReservationDto
 * @package AppBundle\Dto
 */
class ReservationDto
{
    public $user;

    public $hotel;

    public $room;
    public $startDate;
    public $endDate;
}
