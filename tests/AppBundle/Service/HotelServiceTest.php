<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 08:45
 */

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Hotel;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Repository\HotelRepository;
use AppBundle\Service\HotelService;
use AppBundle\Entity\User;

/**
 * Class HotelServiceTest
 * @package Tests\AppBundle\Service
 */
class HotelServiceTest extends EntityManagerMock
{
    const FIRST_ENTITY = Hotel::class;
    const FIRST_ENTITY_REPOSITORY = HotelRepository::class;

    /** @var HotelServiceTest | \PHPUnit_Framework_MockObject_MockObject */
    protected $hotelService;

    /**
     * HotelServiceTest constructor.
     * @param array  $repositories
     * @param mixed  $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(
        array $repositories = [self::FIRST_ENTITY => self::FIRST_ENTITY_REPOSITORY],
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

        $this->hotelService = new HotelService($this->emMock);
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
            ->willReturn(['ROLE_OWNER']);
        $ownerMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(100);

        $hotel1->expects($this->once())
            ->method('getName')
            ->willReturn('Hilton');

        $hotel2->expects($this->once())
            ->method('getName')
            ->willReturn('Ramada');

        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
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
            ->willReturn(['ROLE_OWNER']);
        $ownerMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(100);

        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
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
     *
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
}
