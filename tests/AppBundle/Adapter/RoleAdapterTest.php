<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 30.08.2018
 * Time: 16:11
 */

namespace Tests\AppBundle\Adapter;

use AppBundle\Adapter\RoleAdapter;
use AppBundle\Dto\RoleDto;
use AppBundle\Entity\Role;
use PHPUnit\Framework\TestCase;

/**
 * Class RoleAdapterTest
 * @package Tests\AppBundle\Adapter
 */
class RoleAdapterTest extends TestCase
{
    /** @var roleAdapter */
    protected $roleAdapter;

    /**
     *
     */
    public function setUp()
    {
        $this->roleAdapter = new RoleAdapter();
    }

    /**
     *
     */
    public function testSuccessfullyConvertToDto()
    {
        $roleMock = $this->createMock(Role::class);
        $roleMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_CLIENT');

        $roleDto = $this->roleAdapter->convertToDto($roleMock);

        $this->assertEquals('ROLE_CLIENT', $roleDto->description);
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithNoExistingRole()
    {
        $roleDtoMock = $this->createMock(RoleDto::class);
        $roleDtoMock->description = 'ROLE_CLIENT';

        $role = $this->roleAdapter->convertToEntity($roleDtoMock);

        $this->assertEquals('ROLE_CLIENT', $role->getDescription());
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithExistingRole()
    {
        $roleMock = new Role();
        $roleMock->setDescription('ROLE_CLIENT');

        $roleDtoMock = $this->createMock(RoleDto::class);
        $roleDtoMock->description = 'ROLE_MANAGER';

        $role = $this->roleAdapter->convertToEntity($roleDtoMock, $roleMock);

        $this->assertEquals('ROLE_MANAGER', $role->getDescription());
    }
}
