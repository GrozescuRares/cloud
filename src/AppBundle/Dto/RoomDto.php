<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 09:39
 */

namespace AppBundle\Dto;

use AppBundle\Enum\RoomConfig;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RoomDto
 * @package AppBundle\Dto
 */
class RoomDto
{
    public $roomId;
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
     * @Assert\Type("bool")
     */
    public $smoking;
    /**
     * @Assert\Type("bool")
     */
    public $pet;

    /**
     * @Assert\NotBlank(message="constraints.choose-hotel")
     * @Assert\NotEqualTo("default", message="constraints.choose-hotel")
     */
    public $hotel;

    /**
     * @return string
     */
    public function __toString()
    {
        return "Id: ".$this->roomId." Capacity: ".$this->capacity." Price: ".$this->price." Smoking: ".RoomConfig::ALLOWED[$this->smoking]." Pet: ".RoomConfig::ALLOWED[$this->pet];
    }
}
