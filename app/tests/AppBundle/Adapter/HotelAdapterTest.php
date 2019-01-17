<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:36
 */

namespace Tests\AppBundle\Adapter;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Entity\Hotel;

use PHPUnit\Framework\TestCase;

/**
 * Class HotelAdapterTest
 * @package Tests\AppBundle\Adapter
 */
class HotelAdapterTest extends TestCase
{
    /** @var hotelAdapter */
    protected $hotelAdapter;

    /**
     *
     */
    public function setUp()
    {
        $this->hotelAdapter = new HotelAdapter();
    }

    /**
     *
     */
    public function testSuccessfullyConvertToDto()
    {
        $hotelMock = $this->createMock(Hotel::class);
        $hotelMock->expects($this->once())
            ->method('getName')
            ->willReturn('name');
        $hotelMock->expects($this->once())
            ->method('getLocation')
            ->willReturn('location');
        $hotelMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $hotelDto = $this->hotelAdapter->convertToDto($hotelMock);

        $this->assertEquals('name', $hotelDto->name);
        $this->assertEquals('location', $hotelDto->location);
        $this->assertEquals('description', $hotelDto->description);
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithNoExistinghotel()
    {
        $hotelDtoMock = $this->createMock(HotelDto::class);
        $hotelDtoMock->name = 'name';
        $hotelDtoMock->location = 'location';

        $hotel = $this->hotelAdapter->convertToEntity($hotelDtoMock);

        $this->assertEquals('name', $hotel->getName());
        $this->assertEquals('location', $hotel->getLocation());
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithExistinghotel()
    {
        $hotelMock = new Hotel();
        $hotelMock->setLocation('location')->setDescription('description');

        $hotelDtoMock = $this->createMock(hotelDto::class);
        $hotelDtoMock->location = 'newLocation';

        $hotel = $this->hotelAdapter->convertToEntity($hotelDtoMock, $hotelMock);

        $this->assertEquals('newLocation', $hotel->getLocation());
        $this->assertEquals('description', $hotel->getDescription());
    }
}
