<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 07.09.2018
 * Time: 09:49
 */

namespace AppBundle\Helper;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;
use AppBundle\Exception\HotelNotFoundException;
use AppBundle\Exception\RoomNotFoundException;
use Doctrine\ORM\EntityManager;

/**
 * Class GetEntitiesAndDtosHelper
 * @package AppBundle\Helper
 */
class GetEntitiesAndDtosHelper
{
    /** @var EntityManager */
    protected $em;
    /** @var HotelAdapter */
    protected $hotelAdapter;

    /**
     * GetEntitiesAndDtosHelper constructor.
     * @param EntityManager $em
     * @param HotelAdapter  $hotelAdapter
     */
    public function __construct(EntityManager $em, HotelAdapter $hotelAdapter)
    {
        $this->em = $em;
        $this->hotelAdapter = $hotelAdapter;
    }

    /**
     * @param int $hotelId
     *
     * @return Hotel|null|object
     */
    public function getHotelById($hotelId)
    {
        $hotel = $this->em->getRepository(Hotel::class)->findOneBy([
            'hotelId' => $hotelId,
        ]);
        if (empty($hotel)) {
            throw new HotelNotFoundException('There is no hotel with id: '.$hotelId);
        }

        return $hotel;
    }

    /**
     * @param mixed $roomId
     * @return Room|null|object
     */
    public function getRoomById($roomId)
    {
        $room = $this->em->getRepository(Room::class)->findOneBy(
            [
                'roomId' => $roomId,
            ]
        );

        if (empty($room)) {
            throw new RoomNotFoundException('There is no room with id: '.$roomId);
        }

        return $room;
    }
}
