<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 04.09.2018
 * Time: 11:53
 */

namespace AppBundle\Service;

use AppBundle\Adapter\ReservationAdapter;
use AppBundle\Dto\ReservationDto;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;
use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Exception\RoomNotFoundException;
use AppBundle\Helper\GetEntitiesAndDtosHelper;
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class ReservationService
 * @package AppBundle\Service
 */
class ReservationService
{
    /** @var EntityManager */
    protected $em;
    /** @var ReservationAdapter */
    protected $reservationAdapter;
    /** @var GetEntitiesAndDtosHelper */
    protected $getEntitiesAndDtosHelper;
    /**
     * ReservationService constructor.
     * @param EntityManager            $em
     * @param ReservationAdapter       $reservationAdapter
     * @param GetEntitiesAndDtosHelper $getEntitiesAndDtosHelper
     */
    public function __construct(EntityManager $em, ReservationAdapter $reservationAdapter, GetEntitiesAndDtosHelper $getEntitiesAndDtosHelper)
    {
        $this->em = $em;
        $this->reservationAdapter = $reservationAdapter;
        $this->getEntitiesAndDtosHelper = $getEntitiesAndDtosHelper;
    }

    /**
     * @param User           $client
     * @param ReservationDto $reservationDto
     * @throws \Doctrine\ORM\OptimisticLockException
     * @return ReservationDto
     */
    public function addReservation(User $client, ReservationDto $reservationDto)
    {
        $userRole = $client->getRoles();
        ValidateUserHelper::checkIfUserHasRole($userRole);
        ValidateUserHelper::checkIfUserHasRoleClient($userRole);

        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($reservationDto->hotel);
        $room = $this->getEntitiesAndDtosHelper->getRoomById($reservationDto->room);

        if (empty($hotel)) {
            throw new HotelNotFoundException('There is no hotel with id: '.$reservationDto->hotel);
        }
        if (empty($room)) {
            throw new RoomNotFoundException('There is no room with id: '.$reservationDto->room);
        }

        $reservation = new Reservation();
        $reservation
            ->setUser($client)
            ->setHotel($hotel)
            ->setRoom($room)
            ->setStartDate($reservationDto->startDate)
            ->setEndDate($reservationDto->endDate);

        $this->em->persist($reservation);
        $this->em->flush();

        return $this->reservationAdapter->convertToDto($reservation);
    }

    /**
     * @param mixed     $hotelId
     * @param \DateTime $startYear
     * @param \DateTime $endYear
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @return mixed
     */
    public function getAnnualEarnings($hotelId, \DateTime $startYear, \DateTime $endYear)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);

        return $this->em->getRepository(Reservation::class)->getAnnualEarnings($hotel, $startYear, $endYear);
    }

    /**
     * @param mixed $hotelId
     * @return float
     */
    public function getReservationsPagesNumber($hotelId)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);

        return $this->em->getRepository(Reservation::class)->getReservationsPagesNumber($hotel);
    }

    /**
     * @param mixed $hotelId
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortReservations($hotelId, $offset, $column = null, $sort = null)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);
        $reservations = $this->em->getRepository(Reservation::class)->paginateAndSortReservations($hotel, $offset, $column, $sort);

        return $this->reservationAdapter->convertToReservationDtos($reservations);
    }
}
