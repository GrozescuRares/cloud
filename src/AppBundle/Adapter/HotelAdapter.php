<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:32
 */

namespace AppBundle\Adapter;

use AppBundle\Dto\HotelDto;
use AppBundle\Entity\Hotel;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class HotelAdapter
 * @package AppBundle\Adapter
 */
class HotelAdapter
{
    protected $propertyAccessor;

    /**
     * RoomAdapter constructor.
     */
    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param Hotel $hotel
     *
     * @return HotelDto
     */
    public function convertToDto(Hotel $hotel)
    {
        $hotelDto = new HotelDto();

        foreach ($hotelDto as $property => $value) {
            $hotelDto->$property = $this->propertyAccessor->getValue($hotel, $property);
        }

        return $hotelDto;
    }

    /**
     * @param HotelDto $hotelDto
     * @param Hotel    $hotel
     * @return Hotel
     */
    public function convertToEntity(HotelDto $hotelDto, Hotel $hotel = null)
    {
        if (empty($hotel)) {
            $hotel = new Hotel();
        }

        foreach ($hotelDto as $property => $value) {
            if ((!empty($value) || $value === false) && !is_object($value)) {
                $this->propertyAccessor->setValue($hotel, $property, $value);
            }
        }

        return $hotel;
    }

    /**
     * @param array $hotels
     * @return array
     */
    public function convertHotelsToHotelDtos($hotels)
    {
        $hotelDtos = [];
        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $hotelDtos[$hotel->getName()] = $this->convertToDto($hotel);
        }

        return $hotelDtos;
    }
}
