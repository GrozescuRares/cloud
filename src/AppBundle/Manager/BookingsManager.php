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
use AppBundle\Helper\MailHelper;
use AppBundle\Helper\MailInterface;
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
    /** @var MailHelper */
    protected $mailHelper;

    /**
     * BookingsManager constructor.
     *
     * @param HotelService       $hotelService
     * @param RoomService        $roomService
     * @param ReservationService $reservationService
     * @param MailInterface      $mailHelper
     */
    public function __construct(
        HotelService $hotelService,
        RoomService $roomService,
        ReservationService $reservationService,
        MailInterface $mailHelper
    ) {
        $this->hotelService = $hotelService;
        $this->roomService = $roomService;
        $this->reservationService = $reservationService;
        $this->mailHelper = $mailHelper;
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
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function addReservation(User $client, ReservationDto $reservationDto)
    {
        $reservationDto = $this->reservationService->addReservation($client, $reservationDto);
        $this->mailHelper->sendEmail(
            $client->getEmail(),
            'Booking Confirmation',
            [
                'name' => $client->getLastName().' '.$client->getFirstName(),
                'hotel' => $reservationDto->hotel->name,
                'startDate' => $reservationDto->startDate,
                'location' => $reservationDto->hotel->location,
                'room' => $reservationDto->room->roomId,
            ],
            'emails/booking.html.twig'
        );
    }

    /**
     * @param mixed $hotelId
     * @return float
     */
    public function getReservationsPagesNumber($hotelId)
    {
        return $this->reservationService->getReservationsPagesNumber($hotelId);
    }

    /**
     * @param mixed      $hotelId
     * @param mixed      $offset
     * @param mixed|null $column
     * @param mixed|null $sort
     * @return array
     */
    public function paginateAndSortReservations($hotelId, $offset, $column = null, $sort = null)
    {
        return $this->reservationService->paginateAndSortReservations($hotelId, $offset, $sort);
    }
}
