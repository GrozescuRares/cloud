<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 10:07
 */

namespace AppBundle\Adapter;

use AppBundle\Dto\ReservationDto;
use AppBundle\Entity\Reservation;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class ReservationAdapter
 * @package AppBundle\Adapter
 */
class ReservationAdapter
{
    protected $propertyAccessor;
    protected $userAdapter;
    protected $hotelAdapter;
    protected $roomAdapter;

    /**
     * ReservationAdapter constructor.
     * @param UserAdapter  $userAdapter
     * @param HotelAdapter $hotelAdapter
     * @param RoomAdapter  $roomAdapter
     */
    public function __construct(UserAdapter $userAdapter, HotelAdapter $hotelAdapter, RoomAdapter $roomAdapter)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->userAdapter = $userAdapter;
        $this->hotelAdapter = $hotelAdapter;
        $this->roomAdapter = $roomAdapter;
    }

    /**
     * @param Reservation $reservation
     */
    public function convertToDto(Reservation $reservation)
    {
        $reservationDto = new ReservationDto();

        foreach ($reservation as $property => $value) {
            $reservationDto->$property = $this->propertyAccessor->getValue($reservation, $property);
        }

        $reservationDto->user = $this->userAdapter->convertToDto($reservationDto->user);
        $reservationDto->room = $this->userAdapter->convertToDto($reservationDto->room);
        $reservationDto->hotel = $this->userAdapter->convertToDto($reservationDto->hotel);
    }

    /**
     * @param ReservationDto   $reservationDto
     * @param Reservation|null $reservation
     * @return Reservation
     */
    public function convertToEntity(ReservationDto $reservationDto, Reservation $reservation = null)
    {
        if (empty($reservation)) {
            $reservation = new Reservation();
        }

        foreach ($reservationDto as $property => $value) {
            if (!empty($value) && !is_object($value)) {
                $this->propertyAccessor->setValue($reservation, $reservationDto, $property);
            }
        }

        return $reservation;
    }
}
