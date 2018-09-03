<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:31
 */

namespace AppBundle\Dto;

/**
 * Class HotelDto
 * @package AppBundle\Dto
 */
class HotelDto
{
    public $hotelId;
    public $name;
    public $location;
    public $description;

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->name." t";
    }
}
