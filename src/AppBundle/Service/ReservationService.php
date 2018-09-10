<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 04.09.2018
 * Time: 11:53
 */

namespace AppBundle\Service;

use AppBundle\Adapter\ReservationAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Dto\ReservationDto;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\User;
use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Exception\ReservationNotFoundException;
use AppBundle\Exception\RoomNotFoundException;
use AppBundle\Helper\GetEntitiesAndDtosHelper;
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;

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
            ->setEndDate($reservationDto->endDate)
            ->setDays($reservationDto->endDate->diff($reservationDto->startDate)->format('%a'));

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
    public function getReservationsPagesNumberByHotel($hotelId)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);

        return $this->em->getRepository(Reservation::class)->getReservationsPagesNumberByHotel($hotel);
    }

    /**
     * @param mixed $hotelId
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortReservationsByHotel($hotelId, $offset, $column = null, $sort = null)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);
        $reservations = $this->em->getRepository(Reservation::class)->paginateAndSortReservationsByHotel($hotel, $offset, $column, $sort);

        return $this->reservationAdapter->convertToReservationDtos($reservations);
    }

    /**
     * @param array $hotelDtos
     *
     * @return float
     */
    public function getReservationsPagesNumberForAllHotels(array $hotelDtos)
    {
        $hotels = $this->convertToEntities($hotelDtos);

        return $this->em->getRepository(Reservation::class)->getReservationsPagesNumberForAllHotels($hotels);
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
        $hotels = $this->convertToEntities($hotelDtos);
        $reservations = $this->em->getRepository(Reservation::class)->paginateAndSortReservationsForAllHotels($hotels, $offset, $column, $sort);

        return $this->reservationAdapter->convertToReservationDtos($reservations);
    }

    /**
     * @param array $hotels
     * @param mixed $reservationId
     * @throws OptimisticLockException
     */
    public function deleteReservationByOwner(array $hotels, $reservationId)
    {
        $reservation = $this->getEntitiesAndDtosHelper->getReservationById($reservationId);
        $flag = false;
        /** @var HotelDto $hotel */
        foreach ($hotels as $hotel) {
            if ($hotel->hotelId === $reservation->getHotel()->getHotelId()) {
                $flag = true;
                break;
            }
        }

        if (!$flag) {
            throw new ReservationNotFoundException('You have no right to delete the reservation with id: '.$reservationId);
        }

        $this->em->remove($reservation);
        $this->em->flush();
    }

    /**
     * @param mixed $hotelId
     * @param mixed $reservationId
     * @throws OptimisticLockException
     */
    public function deleteReservationByManager($hotelId, $reservationId)
    {
        $reservation = $this->getEntitiesAndDtosHelper->getReservationById($reservationId);

        if ($reservation->getHotel()->getHotelId() !== $hotelId) {
            throw new ReservationNotFoundException('You have no right to delete the reservation with id: '.$reservationId);
        }

        $this->em->remove($reservation);
        $this->em->flush();
    }

    /**
     * @param array $hotelDtos
     *
     * @return array
     */
    private function convertToEntities(array $hotelDtos)
    {
        $hotels = [];
        foreach ($hotelDtos as $hotelDto) {
            $hotels[] = $this->getEntitiesAndDtosHelper->getHotelById($hotelDto->hotelId);
        }

        return $hotels;
    }
}
