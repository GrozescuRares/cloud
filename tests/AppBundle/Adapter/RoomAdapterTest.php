<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 09:52
 */

namespace Tests\AppBundle\Adapter;

use AppBundle\Adapter\RoomAdapter;
use AppBundle\Dto\RoomDto;
use AppBundle\Entity\Room;
use PHPUnit\Framework\TestCase;

/**
 * Class RoomAdapterTest
 * @package Tests\AppBundle\Adapter
 */
class RoomAdapterTest extends TestCase
{
    /** @var RoomAdapter */
    protected $roomAdapter;

    /**
     *
     */
    public function setUp()
    {
        $this->roomAdapter = new RoomAdapter();
    }

    /**
     *
     */
    public function testSuccessfullyConvertToDto()
    {
        $roomMock = $this->createMock(Room::class);
        $roomMock->expects($this->once())
            ->method('getCapacity')
            ->willReturn(2);
        $roomMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(100);
        $roomMock->expects($this->once())
            ->method('isSmoking')
            ->willReturn(true);
        $roomMock->expects($this->once())
            ->method('isPet')
            ->willReturn(true);

        $roomDto = $this->roomAdapter->convertToDto($roomMock);

        $this->assertEquals(2, $roomDto->capacity);
        $this->assertEquals(100, $roomDto->price);
        $this->assertEquals(true, $roomDto->smoking);
        $this->assertEquals(true, $roomDto->pet);
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithNoExistingRoom()
    {
        $roomDtoMock = $this->createMock(RoomDto::class);
        $roomDtoMock->capacity = 4;
        $roomDtoMock->price = 200;
        $roomDtoMock->smoking = false;
        $roomDtoMock->pet = false;

        $room = $this->roomAdapter->convertToEntity($roomDtoMock);

        $this->assertEquals(4, $room->getCapacity());
        $this->assertEquals(200, $room->getPrice());
        $this->assertEquals(false, $room->isSmoking());
        $this->assertEquals(false, $room->isPet());
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithExistingRoom()
    {
        $roomMock = new Room();
        $roomMock->setCapacity(2)->setPet(true)->setPrice(100)->setSmoking(true);

        $roomDtoMock = $this->createMock(RoomDto::class);
        $roomDtoMock->capacity = 4;
        $roomDtoMock->price = 200;
        $roomDtoMock->smoking = false;
        $roomDtoMock->pet = null;

        $room = $this->roomAdapter->convertToEntity($roomDtoMock, $roomMock);

        $this->assertEquals(4, $room->getCapacity());
        $this->assertEquals(200, $room->getPrice());
        $this->assertEquals(false, $room->isSmoking());
        $this->assertEquals(true, $room->isPet());
    }
}
