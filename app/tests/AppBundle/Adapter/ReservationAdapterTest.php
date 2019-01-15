<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 10:20
 */

namespace Tests\AppBundle\Adapter;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Adapter\ReservationAdapter;
use AppBundle\Adapter\RoomAdapter;
use AppBundle\Adapter\UserAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Dto\ReservationDto;
use AppBundle\Dto\RoomDto;
use AppBundle\Dto\UserDto;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;

use PHPUnit\Framework\TestCase;

/**
 * Class ReservationAdapterTest
 * @package Tests\AppBundle\Adapter
 */
class ReservationAdapterTest extends TestCase
{
    /** @var UserAdapter | \PHPUnit_Framework_MockObject_MockObject */
    protected $userAdapterMock;
    /** @var HotelAdapter | \PHPUnit_Framework_MockObject_MockObject */
    protected $hotelAdapterMock;
    /** @var RoomAdapter | \PHPUnit_Framework_MockObject_MockObject */
    protected $roomAdapterMock;
    /** @var ReservationAdapter */
    protected $reservationAdapter;

    /**
     *
     */
    public function setUp()
    {
        $this->userAdapterMock = $this->createMock(UserAdapter::class);
        $this->hotelAdapterMock = $this->createMock(HotelAdapter::class);
        $this->roomAdapterMock = $this->createMock(RoomAdapter::class);
        $this->reservationAdapter = new ReservationAdapter(
            $this->userAdapterMock,
            $this->hotelAdapterMock,
            $this->roomAdapterMock
        );
    }

    /**
     *
     */
    public function testSuccessfullyConvertToDto()
    {
        $userMock = $this->createMock(User::class);
        $hotelMock = $this->createMock(Hotel::class);
        $roomMock = $this->createMock(Room::class);

        $userDtoMock = $this->createMock(UserDto::class);
        $hotelDtoMock = $this->createMock(HotelDto::class);
        $roomDtoMock = $this->createMock(RoomDto::class);

        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($userMock);
        $reservationMock->expects($this->exactly(2))
            ->method('getHotel')
            ->willReturn($hotelMock);
        $reservationMock->expects($this->exactly(2))
            ->method('getRoom')
            ->willReturn($roomMock);
        $reservationMock->expects($this->once())
            ->method('getStartDate')
            ->willReturn('startDate');
        $reservationMock->expects($this->once())
            ->method('getEndDate')
            ->willReturn('endDate');

        $this->userAdapterMock->expects($this->once())
            ->method('convertToDto')
            ->with($userMock)
            ->willReturn($userDtoMock);
        $this->hotelAdapterMock->expects($this->once())
            ->method('convertToDto')
            ->with($hotelMock)
            ->willReturn($hotelDtoMock);
        $this->roomAdapterMock->expects($this->once())
            ->method('convertToDto')
            ->with($roomMock)
            ->willReturn($roomDtoMock);

        $reservationDto = $this->reservationAdapter->convertToDto($reservationMock);

        $this->assertInstanceOf(ReservationDto::class, $reservationDto);
        $this->assertEquals($userDtoMock, $reservationDto->user);
        $this->assertEquals($hotelDtoMock, $reservationDto->hotel);
        $this->assertEquals($roomDtoMock, $reservationDto->room);
        $this->assertEquals('startDate', $reservationDto->startDate);
        $this->assertEquals('endDate', $reservationDto->endDate);
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithExistingReservation()
    {
        $date = new \DateTime();
        $date1 = new \DateTime();

        $reservationDtoMock = $this->createMock(ReservationDto::class);
        $reservationDtoMock->user = $this->createMock(UserDto::class);
        $reservationDtoMock->hotel = $this->createMock(HotelDto::class);
        $reservationDtoMock->room = $this->createMock(RoomDto::class);
        $reservationDtoMock->startDate = $date;

        $reservation = new Reservation();
        $reservation->setHotel($this->createMock(Hotel::class))->setEndDate($date1);


        $reservation = $this->reservationAdapter->convertToEntity($reservationDtoMock, $reservation);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals(null, $reservation->getUser());
        $this->assertEquals($this->createMock(Hotel::class), $reservation->getHotel());
        $this->assertEquals(null, $reservation->getRoom());
        $this->assertEquals($date, $reservation->getStartDate());
        $this->assertEquals($date1, $reservation->getEndDate());
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithNoExistingReservation()
    {
        $date = new \DateTime();
        $date1 = new \DateTime();

        $reservationDtoMock = $this->createMock(ReservationDto::class);
        $reservationDtoMock->user = $this->createMock(UserDto::class);
        $reservationDtoMock->hotel = $this->createMock(HotelDto::class);
        $reservationDtoMock->room = $this->createMock(RoomDto::class);
        $reservationDtoMock->startDate = $date;
        $reservationDtoMock->endDate = $date1;

        $reservation = $this->reservationAdapter->convertToEntity($reservationDtoMock);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals(null, $reservation->getUser());
        $this->assertEquals(null, $reservation->getHotel());
        $this->assertEquals(null, $reservation->getRoom());
        $this->assertEquals($date, $reservation->getStartDate());
        $this->assertEquals($date1, $reservation->getEndDate());
    }
}
