<?php

namespace Tests\AppBundle\Adapter;

use AppBundle\Adapter\UserAdapter;
use AppBundle\Dto\UserDto;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class UserAdapterTest
 * @package Tests\AppBundle\Adapter
 */
class UserAdapterTest extends TestCase
{
    /** @var UserAdapter */
    protected $userAdapter;

    /**
     *
     */
    public function setUp()
    {
        $this->userAdapter = new UserAdapter();
    }

    /**
     *
     */
    public function testSuccessfullyConvertToDto()
    {
        $userMock = $this->createMock(User::class);
        $roleMock = $this->createMock(Role::class);

        $userMock->expects($this->once())
            ->method('getUsername')
            ->willReturn('username');
        $userMock->expects($this->once())
            ->method('getRole')
            ->willReturn($roleMock);

        $userDto = $this->userAdapter->convertToDto($userMock);

        $this->assertEquals('username', $userDto->username);
        $this->assertEquals($roleMock, $userDto->role);
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithNoExistingUser()
    {
        $userMockDto = $this->createMock(UserDto::class);
        $userMockDto->username = 'username';
        $userMockDto->role = $this->createMock(Role::class);

        $user = $this->userAdapter->convertToEntity($userMockDto);

        $this->assertEquals('username', $user->getUsername());
        $this->assertEquals($this->createMock(Role::class), $user->getRole());
    }

    /**
     *
     */
    public function testSuccessfullyConvertToEntityWithExistingUser()
    {
        $roleMock2 = $this->createMock(Role::class);

        $userMockDto = $this->createMock(UserDto::class);
        $userMockDto->username = 'username';
        $userMockDto->role = $roleMock2;

        $user = new User();
        $user->setUsername('user')->setRole(new Role());

        $user = $this->userAdapter->convertToEntity($userMockDto, $user);

        $this->assertEquals('username', $user->getUsername());
        $this->assertEquals($roleMock2, $user->getRole());
    }
}
