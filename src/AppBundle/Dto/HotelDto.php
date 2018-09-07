<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:31
 */

namespace AppBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class HotelDto
 * @package AppBundle\Dto
 */
class HotelDto
{
    public $hotelId;
    /**
     * @Assert\NotBlank(message="constraints.blank-hotel-name")
     * @Assert\Type("string")
     */
    public $name;
    /**
     * @Assert\NotBlank(message="constraints.blank-location")
     * @Assert\Type("string")
     */
    public $location;
    /**
     * @Assert\NotBlank(message="constraints.blank-description")
     * @Assert\Type("string")
     */
    public $description;
    public $employees;
    /**
     * @Assert\NotBlank(message="constraints.blank-facilities")
     * @Assert\Type("string")
     */
    public $facilities;
}
