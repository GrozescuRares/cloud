<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 14:29
 */

namespace AppBundle\Service;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Adapter\RoomAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Dto\RoomDto;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Room;

use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Helper\GetEntitiesAndDtosHelper;
use Doctrine\ORM\EntityManager;

/**
 * Class RoomService
 * @package AppBundle\Service
 */
class RoomService
{
    /** @var EntityManager */
    protected $em;
    /** @var RoomAdapter */
    protected $roomAdapter;
    /** @var HotelAdapter  */
    protected $hotelAdapter;
    /** @var GetEntitiesAndDtosHelper */
    protected $getEntitiesAndDtosHelper;

    /**
     * RoomService constructor.
     *
     * @param EntityManager            $em
     * @param RoomAdapter              $roomAdapter
     * @param HotelAdapter             $hotelAdapter
     * @param GetEntitiesAndDtosHelper $getEntitiesAndDtosHelper
     */
    public function __construct(EntityManager $em, RoomAdapter $roomAdapter, HotelAdapter $hotelAdapter, GetEntitiesAndDtosHelper $getEntitiesAndDtosHelper)
    {
        $this->em = $em;
        $this->roomAdapter = $roomAdapter;
        $this->hotelAdapter = $hotelAdapter;
        $this->getEntitiesAndDtosHelper = $getEntitiesAndDtosHelper;
    }

    /**
     * @param RoomDto $roomDto
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addRoom(RoomDto $roomDto)
    {
        $room = $this->roomAdapter->convertToEntity($roomDto);
        $hotel = $this->getHotelFromDto($roomDto->hotel);
        $room->setHotel($hotel);

        $this->em->persist($room);
        $this->em->flush();
    }

    /**
     * @param mixed     $hotelId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    public function getAvailableRooms($hotelId, \DateTime $startDate, \DateTime $endDate)
    {
        if ($startDate > $endDate) {
            return [];
        }

        $roomRepository = $this->em->getRepository(Room::class);
        $bookedRoomsInPeriod = $roomRepository->getBookedRooms($startDate, $endDate, $hotelId);
        $reservedRooms = $roomRepository->getHotelRooms($hotelId);
        $freeRooms = array_diff($reservedRooms, $bookedRoomsInPeriod);

        return $this->roomAdapter->convertToCustomArray($freeRooms);
    }

    /**
     * @param mixed $hotelId
     * @param mixed $petFilter
     * @param mixed $smokingFilter
     * @return float
     */
    public function getPagesNumberForRooms($hotelId, $petFilter = null, $smokingFilter = null)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);

        return $this->em->getRepository(Room::class)->getRoomsPagesNumber($hotel, $petFilter, $smokingFilter);
    }

    /**
     * @param mixed $hotelId
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sort
     * @param mixed $petFilter
     * @param mixed $smokingFilter
     * @return array
     */
    public function paginateAndSortRooms($hotelId, $offset, $column = null, $sort = null, $petFilter = null, $smokingFilter = null)
    {
        $hotel = $this->getEntitiesAndDtosHelper->getHotelById($hotelId);
        $rooms = $this->em->getRepository(Room::class)->paginateAndSortRooms($hotel, $offset, $column, $sort, $petFilter, $smokingFilter);

        return $this->roomAdapter->convertToRoomDtos($rooms);
    }

    /**
     * @param HotelDto $hotelDto
     *
     * @return Hotel|null|object
     */
    private function getHotelFromDto(HotelDto $hotelDto)
    {
        return $this->em->getRepository(Hotel::class)->findOneBy([
            'name' => $hotelDto->name,
            'location' => $hotelDto->location,
            'description' => $hotelDto->description,
        ]);
    }
}
