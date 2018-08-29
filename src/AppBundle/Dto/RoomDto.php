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
     * @Assert\NotBlank()
     * @Assert\NotEqualTo("default")
     */
    public $capacity;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="constraints.type.integer"
     * )
     */
    public $price;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"Yes", "No"})
     */
    public $smoking;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"Yes", "No"})
     */
    public $pet;

    /**
     * @Assert\NotBlank()
     * @Assert\NotEqualTo("default")
     */
    public $hotel;
}
