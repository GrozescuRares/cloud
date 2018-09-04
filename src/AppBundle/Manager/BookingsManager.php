<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 04.09.2018
 * Time: 11:11
 */

namespace AppBundle\Manager;

use AppBundle\Dto\ReservationDto;
use AppBundle\Entity\User;
use AppBundle\Service\HotelService;
use AppBundle\Service\ReservationService;
use AppBundle\Service\RoomService;

/**
 * Class BookingsManager
 * @package AppBundle\Manager
 */
class BookingsManager
{
    /** @var HotelService */
    protected $hotelService;
    /** @var RoomService */
    protected $roomService;
    /** @var ReservationService */
    protected $reservationService;

    /**
     * BookingsManager constructor.
     *
     * @param HotelService       $hotelService
     * @param RoomService        $roomService
     * @param ReservationService $reservationService
     */
    public function __construct(HotelService $hotelService, RoomService $roomService, ReservationService $reservationService)
    {
        $this->hotelService = $hotelService;
        $this->roomService = $roomService;
        $this->reservationService = $reservationService;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    public function getFreeHotels(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->hotelService->getAvailableHotels($startDate, $endDate);
    }

    /**
     * @param mixed     $hotelId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    public function getFreeRooms($hotelId, \DateTime $startDate, \DateTime $endDate)
    {
        return $this->roomService->getAvailableRooms($hotelId, $startDate, $endDate);
    }

    /**
     * @param User           $client
     * @param ReservationDto $reservationDto
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addReservation(User $client, ReservationDto $reservationDto)
    {
        $this->reservationService->addReservation($client, $reservationDto);
    }
}
