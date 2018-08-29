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
use AppBundle\Enum\RoomConfig;
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
        $room->setSmoking(RoomConfig::CHOICE[$roomDto->smoking]);
        $room->setPet(RoomConfig::CHOICE[$roomDto->pet]);

        $this->em->persist($room);
        $this->em->flush();
    }

    /**
     * @param HotelDto $hotelDto
     *
     * @return Hotel|null|object
     */
    public function getHotelFromDto(HotelDto $hotelDto)
    {
        return $this->em->getRepository(Hotel::class)->findOneBy([
            'name' => $hotelDto->name,
            'location' => $hotelDto->location,
            'description' => $hotelDto->description,
        ]);
    }
}
