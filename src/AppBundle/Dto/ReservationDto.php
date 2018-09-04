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
 * @package AppBundle\Dto
 */
class ReservationDto
{
    public $user;
    /**
     * @Assert\NotBlank(message="constraints.blank-hotel")
     * @Assert\Regex("/^\d*[1-9]\d*$/", message="constraints.price")
     */
    public $hotel;
    /**
     * @Assert\NotBlank(message="constraints.blank-room")
     * @Assert\Regex("/^\d*[1-9]\d*$/", message="constraints.price")
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
}
