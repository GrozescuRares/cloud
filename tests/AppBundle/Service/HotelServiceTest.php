<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 08:45
 */

namespace Tests\AppBundle\Service;

use AppBundle\Adapter\HotelAdapter;
use AppBundle\Dto\HotelDto;
use AppBundle\Entity\Hotel;
use AppBundle\Enum\EntityConfig;
use AppBundle\Enum\RepositoryConfig;
use AppBundle\Enum\UserConfig;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Helper\GetEntitiesAndDtosHelper;
use AppBundle\Service\HotelService;
use AppBundle\Entity\User;

/**
 * Class HotelServiceTest
 * @package Tests\AppBundle\Service
 */
class HotelServiceTest extends EntityManagerMock
{
    /** @var HotelService | \PHPUnit_Framework_MockObject_MockObject */
    protected $hotelService;
    /** @var HotelAdapter | \PHPUnit_Framework_MockObject_MockObject */
    protected $hotelAdapterMock;
    /** @var GetEntitiesAndDtosHelper | \PHPUnit_Framework_MockObject_MockObject */
    protected $getEntitiesAndDtosHelperMock;

    /**
     * HotelServiceTest constructor.
     * @param array  $repositories
     * @param mixed  $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(
        array $repositories = [EntityConfig::HOTEL => RepositoryConfig::HOTEL_REPOSITORY],
        $name = null,
        array $data = [],
        $dataName = ''
    ) {
        parent::__construct($repositories, $name, $data, $dataName);
    }

    /**
     * Sets up data
     */
    public function setUp()
    {
        parent::setUp();

        $this->hotelAdapterMock = $this->createMock(HotelAdapter::class);
        $this->getEntitiesAndDtosHelperMock = $this->createMock(GetEntitiesAndDtosHelper::class);
        $this->hotelService = new HotelService($this->emMock, $this->hotelAdapterMock, $this->getEntitiesAndDtosHelperMock);
    }

    /**
     * Tests successfully getHotelsByOwner
     */
    public function testSuccessfullyGetHotelsByOwner()
    {
        $hotel1 = $this->createMock(Hotel::class);
        $hotel2 = $this->createMock(Hotel::class);
        $ownerMock = $this->createMock(User::class);

        $ownerMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);
        $ownerMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(100);

        $hotel1->expects($this->once())
            ->method('getName')
            ->willReturn('Hilton');

        $hotel2->expects($this->once())
            ->method('getName')
            ->willReturn('Ramada');

        $this->repositoriesMocks[EntityConfig::HOTEL]->expects($this->once())
            ->method('findBy')
            ->with(
                [
                    'owner' => 100,
                ]
            )
            ->willReturn([$hotel1, $hotel2]);

        $this->assertEquals(
            ['Hilton' => $hotel1, 'Ramada' => $hotel2],
            $this->hotelService->getHotelsByOwner($ownerMock)
        );
    }

    /**
     * Tests getHotelsByOwner when owner has no hotels
     */
    public function testGetHotelsByOwnerWhenOwnerHasNoHotels()
    {
        $ownerMock = $this->createMock(User::class);

        $ownerMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);
        $ownerMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(100);

        $this->repositoriesMocks[EntityConfig::HOTEL]->expects($this->once())
            ->method('findBy')
            ->with(
                [
                    'owner' => 100,
                ]
            )
            ->willReturn([]);

        $this->assertEquals([], $this->hotelService->getHotelsByOwner($ownerMock));
    }

    /**
     * @test
     */
    public function testGetHotelsByOwnerWhenIsCalledWithAUserWithNoRoles()
    {
        $this->expectException(NoRoleException::class);

        $userMock = $this->createMock(User::class);

        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->hotelService->getHotelsByOwner($userMock);
    }

    /**
     * @test
     */
    public function testSuccessfullyGetOwnerHotelsDto()
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);
        $userMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(183);

        $hotelMock1 = $this->createMock(Hotel::class);
        $hotelMock2 = $this->createMock(Hotel::class);
        $hotelMocks = [$hotelMock1, $hotelMock2];

        $this->repositoriesMocks[EntityConfig::HOTEL]->expects($this->once())
            ->method('findBy')
            ->with([
                'owner' => 183,
            ])
            ->willReturn($hotelMocks);

        $hotelDtoMock1 = $this->createMock(HotelDto::class);
        $hotelDtoMock2 = $this->createMock(HotelDto::class);

        $this->hotelAdapterMock->expects($this->once())
            ->method('convertHotelsToHotelDtos')
            ->with($hotelMocks)
            ->willReturn(['name1' => $hotelDtoMock1, 'name2' => $hotelDtoMock2]);

        $result = $this->hotelService->getOwnerHotelsDto($userMock);

        $this->assertEquals($hotelDtoMock1, $result['name1']);
        $this->assertEquals($hotelDtoMock2, $result['name2']);
    }

    /**
     * @test
     */
    public function testGetOwnerHotelsDtoByUserWithNoRoles()
    {
        $this->expectException(NoRoleException::class);
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->hotelService->getOwnerHotelsDto($userMock);
    }

    /**
     * @test
     */
    public function testGetOwnerHotelsDtoByUserWithNoHighRoles()
    {
        $this->expectException(InappropriateUserRoleException::class);
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->hotelService->getOwnerHotelsDto($userMock);
    }
}
