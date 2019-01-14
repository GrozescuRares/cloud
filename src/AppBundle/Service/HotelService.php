<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 22.08.2018
 * Time: 12:15
 */

namespace AppBundle\Service;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;
use AppBundle\Enum\ReservationConfig;
use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Exception\InvalidDateException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Helper\GetEntitiesAndDtosHelper;
use AppBundle\Helper\ValidateUserHelper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;

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
    /** @var GetEntitiesAndDtosHelper */
    protected $getEntitiesAndDtosHelper;

    /**
     * HotelService constructor.
     *
     * @param EntityManager            $em
     * @param HotelAdapter             $hotelAdapter
     * @param GetEntitiesAndDtosHelper $getEntitiesAndDtosHelper
     */
    public function __construct(EntityManager $em, HotelAdapter $hotelAdapter, GetEntitiesAndDtosHelper $getEntitiesAndDtosHelper)
    {
        $this->em = $em;
        $this->hotelAdapter = $hotelAdapter;
        $this->getEntitiesAndDtosHelper = $getEntitiesAndDtosHelper;
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
        $nowDate = new \DateTime('now');
        $nowDate = $nowDate->format('Y-m-d');

        if ($startDate > $endDate) {
            throw new InvalidDateException('Invalid period');
        }

        if ($startDate->format('Y-m-d') < $nowDate) {
            throw new InvalidDateException('Invalid period');
        }

        $bookedRoomsInPeriod = $this->em->getRepository(Room::class)->getBookedRooms($startDate, $endDate);
        $hotels = $this->em->getRepository(Hotel::class)->findAll();
        $freeHotels = [];

        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            if (count($hotel->getRooms()) === 0) {
                continue;
            }

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
     * @param User  $loggedUser
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @return array
     */
    public function paginateAndSortHotels(User $loggedUser, $offset, $column, $sort)
    {
        ValidateUserHelper::checkIfUserHasRoleAndIsOwner($loggedUser);
        $hotels = $this->em->getRepository(Hotel::class)->paginateAndSortHotels($loggedUser, $offset, $column, $sort);

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

    /**
     * @param HotelDto $hotelDto
     *
     * @throws OptimisticLockException
     */
    public function updateHotel(HotelDto $hotelDto)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelDto->hotelId);
        $hotel = $this->hotelAdapter->convertToEntity($hotelDto, $hotel);

        $this->em->persist($hotel);
        $this->em->flush();
    }

    /**
     * @param User  $loggedUser
     * @param mixed $hotelId
     *
     * @return \AppBundle\Dto\HotelDto|null
     */
    public function getHotelDtoByIdAndOwner(User $loggedUser, $hotelId)
    {
        ValidateUserHelper::checkIfUserHasRoleAndIsOwner($loggedUser);

        $hotel = $this->em->getRepository(Hotel::class)->findOneBy([
            'hotelId' => $hotelId,
            'owner' => $loggedUser,
        ]);

        if (empty($hotel)) {
            throw new HotelNotFoundException('There is no hotel with id: '.$hotelId.' and owner: '.$loggedUser->getFirstName());
        }

        return $this->hotelAdapter->convertToDto($hotel);
    }
}
