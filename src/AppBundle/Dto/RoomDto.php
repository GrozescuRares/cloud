<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 09:39
 */

namespace AppBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RoomDto
 * @package AppBundle\Dto
 */
class RoomDto
{
    /**
     * @Assert\NotBlank(message="constraints.choose-capacity")
     * @Assert\NotEqualTo("default", message="constraints.choose-capacity")
     */
    public $capacity;

    /**
     * @Assert\NotBlank(message="constraints.blank-price")
     * @Assert\Regex("/^\d*[1-9]\d*$/", message="constraints.price")
     */
    public $price;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"Yes", "No"}, strict=true)
     */
    public $smoking;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"Yes", "No"}, strict=true)
     */
    public $pet;

    /**
     * @Assert\NotBlank(message="constraints.choose-hotel")
     * @Assert\NotEqualTo("default", message="constraints.choose-hotel")
     */
    public $hotel;
}
