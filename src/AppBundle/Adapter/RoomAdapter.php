<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 09:40
 */

namespace AppBundle\Adapter;

use AppBundle\Dto\RoomDto;
use AppBundle\Entity\Room;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class RoomAdapter
 * @package AppBundle\Adapter
 */
class RoomAdapter
{
    protected $propertyAccessor;

    /**
     * RoomAdapter constructor.
     */
    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param Room $room
     *
     * @return RoomDto
     */
    public function convertToDto(Room $room)
    {
        $roomDto = new RoomDto();

        foreach ($roomDto as $property => $value) {
                $roomDto->$property = $this->propertyAccessor->getValue($room, $property);
        }

        return $roomDto;
    }

    /**
     * @param RoomDto $roomDto
     * @param Room    $room
     * @return Room
     */
    public function convertToEntity(RoomDto $roomDto, Room $room = null)
    {
        if (empty($room)) {
            $room = new Room();
        }

        foreach ($roomDto as $property => $value) {
            if ((!empty($value) || $value === false) && !is_object($value)) {
                $this->propertyAccessor->setValue($room, $property, $value);
            }
        }

        return $room;
    }

    /**
     * @param array $rooms
     * @return array
     */
    public function convertToCustomArray(array $rooms)
    {
        $result = [];
        /** @var Room $room */
        foreach ($rooms as $room) {
            $result[$room->__toString()] = (string) $room->getRoomId();
        }

        return $result;
    }

    /**
     * @param array $rooms
     * @return array
     */
    public function convertToRoomDtos(array $rooms)
    {
        $result = [];
        /** @var Room $room */
        foreach ($rooms as $room) {
            $result[] = $this->convertToDto($room);
        }

        return $result;
    }
}
