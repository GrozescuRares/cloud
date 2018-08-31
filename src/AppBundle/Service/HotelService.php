<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 12:15
 */

namespace AppBundle\Service;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\User;
use AppBundle\Exception\NoRoleException;
use AppBundle\Helper\ValidateReservationHelper;
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;

/**
 * Class HotelService
 * @package AppBundle\Service
 */
class HotelService
{
    /** @var EntityManager */
    protected $em;
    /** @var HotelAdapter */
    protected $hotelAdapter;

    /**
     * HotelService constructor.
     *
     * @param EntityManager $em
     * @param HotelAdapter  $hotelAdapter
     */
    public function __construct(EntityManager $em, HotelAdapter $hotelAdapter)
    {
        $this->em = $em;
        $this->hotelAdapter = $hotelAdapter;
    }

    /**
     * Returns an array of hotels that have the owner_id equal
     * with $owner's id. The elements will look like 'hotel_name' => hotel entity
     *
     * @param User $owner
     *
     * @return array
     */
    public function getHotelsByOwner(User $owner)
    {
        if (empty($owner->getRoles())) {
            throw new NoRoleException('This user has no roles');
        }

        $hotels = $this->em->getRepository(Hotel::class)->findBy(
            [
                'owner' => $owner->getUserId(),
            ]
        );

        $result = [];

        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $result[$hotel->getName()] = $hotel;
        }

        return $result;
    }

    /**
     * @param User $owner
     *
     * @return array
     */
    public function getOwnerHotelsDto(User $owner)
    {
        $userRole = $owner->getRoles();
        ValidateUserHelper::checkIfUserHasRole($userRole);
        ValidateUserHelper::checkIfUserHasRoleOwner($userRole);

        $hotels = $this->em->getRepository(Hotel::class)->findBy(
            [
                'owner' => $owner->getUserId(),
            ]
        );

        return $this->hotelAdapter->convertHotelsToHotelDtos($hotels);
    }

    /**
     * Return an array of hotelsDto that have free rooms
     * in the interval [$startDate, $endDate] of time.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getAvailableHotelsDto(\DateTime $startDate, \DateTime $endDate)
    {
        $hotels = $this->em->getRepository(Hotel::class)->findAll();
        $reservations = $this->em->getRepository(Reservation::class)->findAll();
        $bookedRooms = [];
        $freeHotels = [];
        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            foreach ($reservations as $reservation) {
                if ($reservation->getHotel() === $hotel && ValidateReservationHelper::checkIdDatesAreValid($startDate, $endDate, $reservation->getStartDate(), $reservation->getEndDate())) {
                    $bookedRooms[$reservation->getRoom()->getRoomId()] = $reservation->getRoom();
                }
            }

            if (count($bookedRooms) !== count($hotel->getRooms())) {
                $freeHotels[$hotel->getName()] = $this->hotelAdapter->convertToDto($hotel);
            }
        }

        return $freeHotels;
    }

    /**
     * @param int $hotelId
     *
     * @return Hotel|null|object
     */
    public function getHotelById($hotelId)
    {
        return $this->em->getRepository(Hotel::class)->findOneBy([
            'hotelId' => $hotelId,
        ]);
    }
}
