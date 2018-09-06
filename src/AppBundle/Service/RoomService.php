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

    /**
     * RoomService constructor.
     *
     * @param EntityManager $em
     * @param RoomAdapter   $roomAdapter
     * @param HotelAdapter  $hotelAdapter
     */
    public function __construct(EntityManager $em, RoomAdapter $roomAdapter, HotelAdapter $hotelAdapter)
    {
        $this->em = $em;
        $this->roomAdapter = $roomAdapter;
        $this->hotelAdapter = $hotelAdapter;
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
