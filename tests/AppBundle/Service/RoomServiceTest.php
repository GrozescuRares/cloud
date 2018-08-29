<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 16:31
 */

namespace Tests\AppBundle\Service;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Adapter\RoomAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Dto\RoomDto;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Room;
use AppBundle\Enum\EntityConfig;
use AppBundle\Enum\RepositoryConfig;
use AppBundle\Service\RoomService;

/**
 * Class RoomServiceTest
 * @package Tests\AppBundle\Service
 */
class RoomServiceTest extends EntityManagerMock
{
    /** @var RoomService */
    protected $roomService;
    /** @var RoomAdapter| \PHPUnit_Framework_MockObject_MockObject */
    protected $roomAdapterMock;
    /** @var HotelAdapter| \PHPUnit_Framework_MockObject_MockObject */
    protected $hotelAdapterMock;

    /**
     * RoomServiceTest constructor.
     * @param array  $repositories
     * @param mixed  $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(
        array $repositories = [EntityConfig::HOTEL => RepositoryConfig::HOTEL_REPOSITORY, EntityConfig::ROOM => RepositoryConfig::ROOM_REPOSITORY],
        $name = null,
        array $data = [],
        $dataName = ''
    ) {
        parent::__construct($repositories, $name, $data, $dataName);
    }

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->roomAdapterMock = $this->createMock(RoomAdapter::class);
        $this->hotelAdapterMock = $this->createMock(HotelAdapter::class);
        $this->roomService = new RoomService($this->emMock, $this->roomAdapterMock, $this->hotelAdapterMock);
    }

    /**
     *
     */
    public function testSuccessfullyAddRoom()
    {
        $hotelDtoMock = $this->createMock(HotelDto::class);
        $hotelDtoMock->name = 'name';
        $hotelDtoMock->location = 'location';
        $hotelDtoMock->description = 'description';

        $hotelMock = $this->createMock(Hotel::class);

        $roomDtoMock = $this->createMock(RoomDto::class);
        $roomDtoMock->capacity = 2;
        $roomDtoMock->price = 100;
        $roomDtoMock->smocking = true;
        $roomDtoMock->pet = false;
        $roomDtoMock->hotel = $hotelDtoMock;

        $roomMock = $this->createMock(Room::class);
        $roomMock->expects($this->once())
            ->method('setHotel')
            ->with($hotelMock);

        $this->roomAdapterMock->expects($this->once())
            ->method('convertToEntity')
            ->with($roomDtoMock)
            ->willReturn($roomMock);

        $this->repositoriesMocks[EntityConfig::HOTEL]->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => 'name',
                'location' => 'location',
                'description' => 'description',
            ])
            ->willReturn($hotelMock);

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($roomMock);
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->roomService->addRoom($roomDtoMock);
    }
}
