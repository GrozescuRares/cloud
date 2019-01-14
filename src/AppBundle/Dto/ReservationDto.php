<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 10:06
 */

namespace AppBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReservationDto
 */
class ReservationDto
{
    public $reservationId;
    public $user;
    /**
     * @Assert\NotBlank(message="constraints.blank-hotel")
     */
    public $hotel;
    /**
     * @Assert\NotBlank(message="constraints.blank-room")
     */
    public $room;
    /**
     * @Assert\NotBlank(message="constraints.blank-data")
     * @Assert\DateTime()
     */
    public $startDate;
    /**
     * @Assert\NotBlank(message="constraints.blank-data")
     * @Assert\DateTime()
     */
    public $endDate;
    public $days;
    public $deletedAt;
}
