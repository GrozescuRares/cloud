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
use Doctrine\ORM\OptimisticLockException;

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
    public function getReservationsPagesNumberByHotel($hotelId)
    {
        return $this->reservationService->getReservationsPagesNumberByHotel($hotelId);
    }

    /**
     * @param mixed      $hotelId
     * @param mixed      $offset
     * @param mixed|null $column
     * @param mixed|null $sort
     * @return array
     */
    public function paginateAndSortReservationsByHotel($hotelId, $offset, $column = null, $sort = null)
    {
        return $this->reservationService->paginateAndSortReservationsByHotel($hotelId, $offset, $column, $sort);
    }

    /**
     * @param array $hotelDtos
     * @return float
     */
    public function getReservationsPagesNumberForAllHotels(array $hotelDtos)
    {
        return $this->reservationService->getReservationsPagesNumberForAllHotels($hotelDtos);
    }

    /**
     * @param array $hotelDtos
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortReservationsForAllHotels(array $hotelDtos, $offset, $column = null, $sort = null)
    {
        return $this->reservationService->paginateAndSortReservationsForAllHotels($hotelDtos, $offset, $column, $sort);
    }

    /**
     * @param array $hotels
     * @param mixed $reservationId
     * @throws OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function deleteReservationByOwner(array $hotels, $reservationId)
    {
        $deletedReservationDto = $this->reservationService->deleteReservationByOwner($hotels, $reservationId);
        $this->mailHelper->sendEmail(
            $deletedReservationDto->user->email,
            'Canceled Booking',
            [
                'hotel' => $deletedReservationDto->hotel->name,
                'startDate' => $deletedReservationDto->startDate,
                'location' => $deletedReservationDto->hotel->location,
            ],
            'emails/delete-reservation.html.twig'
        );
    }

    /**
     * @param mixed $hotelId
     * @param mixed $reservationId
     * @throws OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function deleteReservationByManager($hotelId, $reservationId)
    {
        $deletedReservationDto = $this->reservationService->deleteReservationByManager($hotelId, $reservationId);
        $this->mailHelper->sendEmail(
            $deletedReservationDto->user->email,
            'Canceled Booking',
            [
                'hotel' => $deletedReservationDto->hotel->name,
                'startDate' => $deletedReservationDto->startDate,
                'location' => $deletedReservationDto->hotel->location,
            ],
            'emails/delete-reservation.html.twig'
        );
    }

    /**
     * @param User $client
     * @return float
     */
    public function getUserReservationsPagesNumber(User $client)
    {
        return $this->reservationService->getUserReservationsPagesNumber($client);
    }

    /**
     * @param User  $client
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortUserReservations(User $client, $offset, $column = null, $sort = null)
    {
        return $this->reservationService->paginateAndSortUserReservations($client, $offset, $column, $sort);
    }

    /**
     * @param User  $client
     * @param mixed $reservationId
     * @throws OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function deleteBooking(User $client, $reservationId)
    {
        $deletedReservationDto = $this->reservationService->deleteBooking($client, $reservationId);
        $this->mailHelper->sendEmail(
            $deletedReservationDto->user->email,
            'Canceled Booking',
            [
                'hotel' => $deletedReservationDto->hotel->name,
                'startDate' => $deletedReservationDto->startDate,
                'location' => $deletedReservationDto->hotel->location,
            ],
            'emails/delete-reservation.html.twig'
        );
    }
}
