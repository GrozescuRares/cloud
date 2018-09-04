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
use AppBundle\Entity\Room;
use AppBundle\Entity\User;
use AppBundle\Exception\NoRoleException;
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
        ValidateUserHelper::checkIfUserIsOwnerOrManager($owner);

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
        ValidateUserHelper::checkIfUserIsOwnerOrManager($owner);

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
    public function getAvailableHotels(\DateTime $startDate, \DateTime $endDate)
    {
        if ($startDate > $endDate) {
            return [];
        }

        $bookedRoomsInPeriod = $this->em->getRepository(Room::class)->getBookedRooms($startDate, $endDate);
        $hotels = $this->em->getRepository(Hotel::class)->getHotelsWithReservations();
        $freeHotels = [];

        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $bookedInHotel = 0;
            /** @var Room $room */
            foreach ($bookedRoomsInPeriod as $room) {
                if ($room->getHotel()->getHotelId() === $hotel->getHotelId()) {
                    $bookedInHotel++;
                }
            }

            if ($bookedInHotel === 0 || $bookedInHotel < count($hotel->getRooms())) {
                $freeHotels[$hotel->getName()] = (string) $hotel->getHotelId();
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

    /**
     * @param mixed $hotelId
     *
     * @return \AppBundle\Dto\HotelDto|null
     */
    public function getHotelDtoById($hotelId)
    {
        $hotel = $this->em->getRepository(Hotel::class)->findOneBy([
            'hotelId' => $hotelId,
        ]);

        if (empty($hotel)) {
            return null;
        }

        return $this->hotelAdapter->convertToDto($hotel);
    }

    /**
     * @param User  $loggedUser
     * @param mixed $offset
     * @return array
     */
    public function getFirstHotels(User $loggedUser, $offset)
    {
        ValidateUserHelper::checkIfUserHasRoleAndIsOwner($loggedUser);
        $hotels = $this->em->getRepository(Hotel::class)->paginateAndSortHotels($loggedUser, $offset);

        return $this->hotelAdapter->convertHotelsToHotelDtos($hotels);
    }

    /**
     * @param User $loggedUser
     * @return float
     */
    public function getHotelsPageNumber(User $loggedUser)
    {
        ValidateUserHelper::checkIfUserHasRoleAndIsOwner($loggedUser);

        return $this->em->getRepository(Hotel::class)->getHotelsPagesNumber($loggedUser);
    }
}
