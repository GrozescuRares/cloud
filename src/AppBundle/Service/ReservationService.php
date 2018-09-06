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
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;

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

    /**
     * ReservationService constructor.
     * @param EntityManager      $em
     * @param ReservationAdapter $reservationAdapter
     */
    public function __construct(EntityManager $em, ReservationAdapter $reservationAdapter)
    {
        $this->em = $em;
        $this->reservationAdapter = $reservationAdapter;
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

        $hotel = $this->getHotelById($reservationDto->hotel);
        $room = $this->getRoomById($reservationDto->room);

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
     * @param mixed $hotelId
     * @return Hotel|null|object
     */
    private function getHotelById($hotelId)
    {
        return $this->em->getRepository(Hotel::class)->findOneBy(
            [
                'hotelId' => $hotelId,
            ]
        );
    }

    /**
     * @param mixed $roomId
     * @return Room|null|object
     */
    private function getRoomById($roomId)
    {
        return $this->em->getRepository(Room::class)->findOneBy(
            [
                'roomId' => $roomId,
            ]
        );
    }
}
