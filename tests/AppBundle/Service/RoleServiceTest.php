<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 30.08.2018
 * Time: 17:52
 */

namespace Tests\AppBundle\Service;

use AppBundle\Adapter\RoleAdapter;
use AppBundle\Dto\RoleDto;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Enum\EntityConfig;
use AppBundle\Enum\RepositoryConfig;
use AppBundle\Enum\UserConfig;
use AppBundle\Exception\NoRoleException;
use AppBundle\Service\RoleService;

/**
 * Class RoleServiceTest
 * @package Tests\AppBundle\Service
 */
class RoleServiceTest extends EntityManagerMock
{
    /** @var RoleAdapter | \PHPUnit_Framework_MockObject_MockObject */
    protected $roleAdapterMock;
    /** @var RoleService */
    protected $roleService;
    /**
     * HotelServiceTest constructor.
     * @param array  $repositories
     * @param mixed  $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(
        array $repositories = [EntityConfig::ROLE => RepositoryConfig::ROLE_REPOSITORY],
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

        $this->roleAdapterMock = $this->createMock(RoleAdapter::class);
        $this->roleService = new RoleService($this->emMock, $this->roleAdapterMock);
    }

    /**
     *
     */
    public function testGetCreationalRolesWhenLoggedUserHasNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);

        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->roleService->getUserCreationalRoleDtos($loggedUserMock);
    }

    /**
     * tests getUserCrationalRoles when user has role owner
     */
    public function testGetUserCreationalRolesWhenUserHasRoleOwner()
    {
        $userMock = $this->createMock(User::class);
        $roleManagerMock = $this->createMock(Role::class);
        $roleEmployeeMock = $this->createMock(Role::class);
        $roleOwnerMock = $this->createMock(Role::class);
        $roleClientMock = $this->createMock(Role::class);

        $roleManagerMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_MANAGER);

        $roleEmployeeMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_EMPLOYEE);

        $roleOwnerMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_OWNER);

        $roleClientMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_CLIENT);

        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findAll')
            ->willReturn([$roleManagerMock, $roleEmployeeMock, $roleClientMock, $roleOwnerMock]);

        $this->roleAdapterMock->expects($this->exactly(2))
            ->method('convertToDto')
            ->willReturn($this->createMock(RoleDto::class));

        $this->assertEquals(
            [UserConfig::ROLE_MANAGER => $this->createMock(RoleDto::class), UserConfig::ROLE_EMPLOYEE => $this->createMock(RoleDto::class)],
            $this->roleService->getUserCreationalRoleDtos($userMock)
        );
    }

    /**
     * tests getUserCrationalRoles when user has role manager
     */
    public function testGetUserCreationalRolesWhenUserHasRoleManager()
    {
        $userMock = $this->createMock(User::class);
        $roleManagerMock = $this->createMock(Role::class);
        $roleEmployeeMock = $this->createMock(Role::class);
        $roleOwnerMock = $this->createMock(Role::class);
        $roleClientMock = $this->createMock(Role::class);

        $roleManagerMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_MANAGER);

        $roleEmployeeMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_EMPLOYEE);

        $roleOwnerMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_OWNER);

        $roleClientMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_CLIENT);

        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);

        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findAll')
            ->willReturn([$roleManagerMock, $roleEmployeeMock, $roleClientMock, $roleOwnerMock]);

        $this->roleAdapterMock->expects($this->exactly(2))
            ->method('convertToDto')
            ->willReturn($this->createMock(RoleDto::class));

        $this->assertEquals(
            [UserConfig::ROLE_EMPLOYEE => $this->createMock(RoleDto::class)],
            $this->roleService->getUserCreationalRoleDtos($userMock)
        );
    }

    /**
     * Tests getUserCrationalRoles when user has no role
     */
    public function testGetCreationalRolesWhenUserHasNoRole()
    {
        $this->expectException(NoRoleException::class);

        $userMock = $this->createMock(User::class);

        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->roleService->getUserCreationalRoleDtos($userMock);
    }
}
