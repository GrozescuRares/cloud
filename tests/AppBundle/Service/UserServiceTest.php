<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 09:01
 */

namespace Tests\AppBundle\Service;

use AppBundle\Adapter\UserAdapter;
use AppBundle\Dto\RoleDto;
use AppBundle\Dto\UserDto;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Enum\EntityConfig;
use AppBundle\Enum\RepositoryConfig;
use AppBundle\Enum\UserConfig;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Exception\SameRoleException;
use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UneditableRoleException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Helper\MailHelper;
use AppBundle\Service\FileUploaderService;
use AppBundle\Service\UserService;

use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Class UserServiceTest
 * @package Tests\AppBundle\Service
 */
class UserServiceTest extends EntityManagerMock
{
    /** @var Role|\PHPUnit_Framework_MockObject_MockObject */
    protected $clientRoleMock;
    /** @var UserPasswordEncoder| \PHPUnit_Framework_MockObject_MockObject */
    protected $userPasswordEncoderMock;
    /** @var FileUploaderService| \PHPUnit_Framework_MockObject_MockObject */
    protected $fileUploaderMock;
    /** @var MailHelper| \PHPUnit_Framework_MockObject_MockObject */
    protected $mailMock;
    /** @var UserAdapter| \PHPUnit_Framework_MockObject_MockObject */
    protected $userAdapterMock;
    /** @var UserService */
    protected $userService;

    /**
     * UserServiceTest constructor.
     * @param array  $repositories
     * @param mixed  $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(
        array $repositories = [
            EntityConfig::ROLE => RepositoryConfig::ROLE_REPOSITORY,
            EntityConfig::USER => RepositoryConfig::USER_REPOSITORY,
        ],
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

        $this->clientRoleMock = $this->createMock(Role::class);
        $this->userPasswordEncoderMock = $this->createMock(UserPasswordEncoder::class);
        $this->fileUploaderMock = $this->createMock(FileUploaderService::class);
        $this->mailMock = $this->createMock(MailHelper::class);
        $this->userAdapterMock = $this->createMock(UserAdapter::class);

        $this->userService = new UserService(
            $this->emMock,
            $this->userPasswordEncoderMock,
            $this->fileUploaderMock,
            $this->mailMock,
            $this->userAdapterMock
        );
    }

    /**
     * Tests a successful user registration
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function testSuccessfullyRegisterUser()
    {
        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword')
            ->willReturn("hashedUserPassword");

        $uploadeFileMock = $this->createMock(UploadedFile::class);

        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => UserConfig::ROLE_CLIENT,
                ]
            )
            ->willReturn($this->clientRoleMock);

        $this->fileUploaderMock->expects($this->once())
            ->method('upload')
            ->willReturn("fileName");

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');
        $this->mailMock->expects($this->once())
            ->method('sendEmail')
            ->willReturn(1);

        /** @var User| \PHPUnit_Framework_MockObject_MockObject */
        $userMock = $this->createMock(User::class);

        $userMock->expects($this->exactly(2))
            ->method('getPlainPassword')
            ->willReturn('password');
        $userMock->expects($this->once())
            ->method('getImage')
            ->willReturn($uploadeFileMock);
        $userMock->expects($this->exactly(2))
            ->method('getEmail')
            ->willReturn('email@email.com');

        $this->userService->registerUser($userMock);
    }

    /**
     * Tests a duplicated user registration
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function testDuplicatedRegisterUser()
    {
        /** @var  $optimisticLockExceptionMock OptimisticLockException| \PHPUnit_Framework_MockObject_MockObject */
        $optimisticLockExceptionMock = $this->createMock(OptimisticLockException::class);

        $this->expectException(OptimisticLockException::class);

        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => UserConfig::ROLE_CLIENT,
                ]
            )
            ->willReturn($this->clientRoleMock);


        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword')
            ->willReturn("hashedUserPassword");

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush')
            ->willThrowException($optimisticLockExceptionMock);

        $this->mailMock->expects($this->never())
            ->method('sendEmail');

        /** @var User| \PHPUnit_Framework_MockObject_MockObject */
        $userMock = $this->createMock(User::class);

        $this->userService->registerUser($userMock);
    }

    /**
     * Tests a successful user registration with no profile picture
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function testNoProfileImageRegisterUser()
    {
        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => UserConfig::ROLE_CLIENT,
                ]
            )
            ->willReturn($this->clientRoleMock);

        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword')
            ->willReturn("hashedUserPassword");

        $this->fileUploaderMock->expects($this->never())
            ->method('upload');

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->mailMock->expects($this->once())
            ->method('sendEmail')
            ->willReturn(1);

        /** @var User| \PHPUnit_Framework_MockObject_MockObject */
        $userMock = $this->createMock(User::class);

        $userMock->expects($this->exactly(2))
            ->method('getPlainPassword')
            ->willReturn('password');
        $userMock->expects($this->once())
            ->method('getImage')
            ->willReturn(null);
        $userMock->expects($this->exactly(2))
            ->method('getEmail')
            ->willReturn('email@email.com');

        $this->userService->registerUser($userMock);
    }

    /**
     * Tests a successful account activation
     * @throws OptimisticLockException
     */
    public function testSuccessfullyAccountActivation()
    {

        $user = $this->createMock(User::class);

        $user->expects($this->once())
            ->method('getExpirationDate')
            ->willReturn($this->generateActivationTime());

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'activationToken' => 'dfsdfssdf',
                    'isActivated' => false,
                ]
            )
            ->willReturn($user);

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->activateAccount('dfsdfssdf');
    }

    /**
     * Tests a resend email account activation
     * @throws OptimisticLockException
     */
    public function testResendEmailAccountActivation()
    {
        $this->expectException(TokenExpiredException::class);

        $userMock = $this->createMock(User::class);

        $userMock->expects($this->once())
            ->method('getExpirationDate')
            ->willReturn($this->generateExpiredActivationTime());
        $userMock->expects($this->once())
            ->method('getEmail')
            ->willReturn('email@email.com');
        $userMock->expects($this->once())
            ->method('getActivationToken')
            ->willReturn('sygfudhiifsnjoisdj');

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'activationToken' => 'dfsdfssdf',
                    'isActivated' => false,
                ]
            )
            ->willReturn($userMock);

        $this->mailMock->expects($this->once())
            ->method('sendEmail')
            ->willReturn(1);
        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->activateAccount('dfsdfssdf');
    }

    /**
     * Tests user not found exception - no user with that token
     */
    public function testNoUserWithThatTokenAccountActivation()
    {
        $this->expectException(UserNotFoundException::class);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'activationToken' => 'dfsdfssdf',
                    'isActivated' => false,
                ]
            )
            ->willReturn(null);

        $this->emMock->expects($this->never())
            ->method('persist');
        $this->emMock->expects($this->never())
            ->method('flush');

        $this->userService->activateAccount('dfsdfssdf');
    }

    /**
     * Tests successfully add user by owner
     */
    public function testSuccessfullyAddUserByOwner()
    {
        $userMock = $this->createMock(User::class);
        $loggedUserMock = $this->createMock(User::class);
        $hotelMock = $this->createMock(Hotel::class);

        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword');

        $userMock->expects($this->once())
            ->method('setIsActivated')
            ->with(true);
        $userMock->expects($this->once())
            ->method('getHotel')
            ->willReturn($hotelMock);

        $hotelMock->expects($this->once())
            ->method('getEmployees')
            ->willReturn(10);

        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $this->userService->addUser($userMock, $loggedUserMock);
    }

    /**
     * Tests user update with no new password
     */
    public function testUpdateUserWithNoNewPassword()
    {
        $userMock = $this->createMock(User::class);

        $userMock->expects($this->once())
            ->method('getPlainPassword')
            ->willReturn(null);
        $userMock->expects($this->once())
            ->method('getImage')
            ->willReturn(null);

        $this->fileUploaderMock->expects($this->never())
            ->method('upload');

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->updateUser($userMock);
    }

    /**
     * Tests successfully add user by manager
     */
    public function testSuccessfullyAddUserByManager()
    {
        $userMock = $this->createMock(User::class);
        $loggedUserMock = $this->createMock(User::class);
        $hotelMock = $this->createMock(Hotel::class);
        $hotelMock1 = $this->createMock(Hotel::class);

        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword');

        $userMock->expects($this->once())
            ->method('setIsActivated')
            ->with(true);
        $userMock->expects($this->once())
            ->method('setHotel')
            ->with($hotelMock1);
        $userMock->expects($this->exactly(1))
            ->method('getHotel')
            ->willReturn($hotelMock);

        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);
        $loggedUserMock->expects($this->once())
            ->method('getHotel')
            ->willReturn($hotelMock1);

        $hotelMock->expects($this->once())
            ->method('getEmployees')
            ->willReturn(10);

        $this->userService->addUser($userMock, $loggedUserMock);
    }

    /**
     * Tests user update with new password
     */
    public function testUpdateUserWithNewPassword()
    {
        $userMock = $this->createMock(User::class);

        $userMock->expects($this->exactly(2))
            ->method('getPlainPassword')
            ->willReturn('password');
        $userMock->expects($this->once())
            ->method('getImage')
            ->willReturn(null);

        $this->fileUploaderMock->expects($this->never())
            ->method('upload');

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->updateUser($userMock);
    }

    /**
     * Tests user update with new profile picture
     */
    public function testUpdateUserWithNewProfilePicture()
    {
        $uploadedImageMock = $this->createMock(UploadedFile::class);
        $userMock = $this->createMock(User::class);

        $userMock->expects($this->once())
            ->method('getPlainPassword')
            ->willReturn(null);
        $userMock->expects($this->once())
            ->method('getImage')
            ->willReturn($uploadedImageMock);

        $this->fileUploaderMock->expects($this->once())
            ->method('upload');

        $this->emMock->expects($this->once())
            ->method('persist');
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->updateUser($userMock);
    }

    /**
     *
     */
    public function testSuccessfullyEditUserRoleByOwner()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $userEntityMock = $this->createMock(User::class);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($userEntityMock);

        $userEntityRoleMock = $this->createMock(Role::class);
        $userEntityRoleMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_EMPLOYEE);

        $userEntityMock->expects($this->once())
            ->method('getRole')
            ->willReturn($userEntityRoleMock);

        $roleDtoMock = $this->createMock(RoleDto::class);
        $roleDtoMock->description = UserConfig::ROLE_MANAGER;
        $userDtoMock = $this->createMock(UserDto::class);
        $userDtoMock->username = 'test';
        $userDtoMock->role = $roleDtoMock;

        $roleEntityMock = $this->createMock(Role::class);
        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(['description' => $roleDtoMock->description])
            ->willReturn($roleEntityMock);

        $userResultMock = $this->createMock(User::class);
        $userResultMock->expects($this->once())
            ->method('setRole')
            ->with($roleEntityMock);

        $this->userAdapterMock->expects($this->once())
            ->method('convertToEntity')
            ->with($userDtoMock, $userEntityMock)
            ->willReturn($userResultMock);

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($userResultMock);
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->editUserRole($userDtoMock, $loggedUserMock, 'username');
    }

    /**
     * Test that manager can edit user roles
     */
    public function testSuccessfullyEditUserRoleByManager()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);

        $userEntityMock = $this->createMock(User::class);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($userEntityMock);

        $userEntityRoleMock = $this->createMock(Role::class);
        $userEntityRoleMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_EMPLOYEE);

        $userEntityMock->expects($this->once())
            ->method('getRole')
            ->willReturn($userEntityRoleMock);

        $roleDtoMock = $this->createMock(RoleDto::class);
        $roleDtoMock->description = UserConfig::ROLE_EMPLOYEE;
        $userDtoMock = $this->createMock(UserDto::class);
        $userDtoMock->username = 'test';
        $userDtoMock->role = $roleDtoMock;

        $roleEntityMock = $this->createMock(Role::class);
        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(['description' => $roleDtoMock->description])
            ->willReturn($roleEntityMock);

        $userResultMock = $this->createMock(User::class);
        $userResultMock->expects($this->once())
            ->method('setRole')
            ->with($roleEntityMock);

        $this->userAdapterMock->expects($this->once())
            ->method('convertToEntity')
            ->with($userDtoMock, $userEntityMock)
            ->willReturn($userResultMock);

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($userResultMock);
        $this->emMock->expects($this->once())
            ->method('flush');

        $this->userService->editUserRole($userDtoMock, $loggedUserMock, 'username');
    }

    /**
     * Test the page when it's accessed by a user with no role
     */
    public function testEditUserRoleWithLoggedUserThatHasNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->editUserRole($this->createMock(UserDto::class), $loggedUserMock, 'username');
    }

    /**
     * @throws OptimisticLockException
     */
    public function testEditUserRoleByOwnerWithNonExistingRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $userEntityMock = $this->createMock(User::class);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($userEntityMock);

        $userEntityRoleMock = $this->createMock(Role::class);
        $userEntityRoleMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_EMPLOYEE);

        $userEntityMock->expects($this->once())
            ->method('getRole')
            ->willReturn($userEntityRoleMock);

        $roleDtoMock = $this->createMock(RoleDto::class);
        $roleDtoMock->description = 'no role';
        $userDtoMock = $this->createMock(UserDto::class);
        $userDtoMock->username = 'test';
        $userDtoMock->role = $roleDtoMock;

        $roleEntityMock = $this->createMock(Role::class);
        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(['description' => $roleDtoMock->description]);

        $userResultMock = $this->createMock(User::class);
        $userResultMock->expects($this->never())
            ->method('setRole')
            ->with($userDtoMock->role);

        $this->userAdapterMock->expects($this->once())
            ->method('convertToEntity')
            ->with($userDtoMock, $userEntityMock)
            ->willReturn($userResultMock);

        $this->emMock->expects($this->never())
            ->method('persist')
            ->with($userResultMock);
        $this->emMock->expects($this->never())
            ->method('flush');

        $this->userService->editUserRole($userDtoMock, $loggedUserMock, 'username');
    }

    /**
     * @throws OptimisticLockException
     */
    public function testEditUserRoleByManagerWithNonExistingRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);

        $userEntityMock = $this->createMock(User::class);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($userEntityMock);

        $userEntityRoleMock = $this->createMock(Role::class);
        $userEntityRoleMock->expects($this->once())
            ->method('getDescription')
            ->willReturn(UserConfig::ROLE_EMPLOYEE);

        $userEntityMock->expects($this->once())
            ->method('getRole')
            ->willReturn($userEntityRoleMock);

        $roleDtoMock = $this->createMock(RoleDto::class);
        $roleDtoMock->description = 'no role';
        $userDtoMock = $this->createMock(UserDto::class);
        $userDtoMock->username = 'test';
        $userDtoMock->role = $roleDtoMock;

        $roleEntityMock = $this->createMock(Role::class);
        $this->repositoriesMocks[EntityConfig::ROLE]->expects($this->once())
            ->method('findOneBy')
            ->with(['description' => $roleDtoMock->description]);

        $userResultMock = $this->createMock(User::class);
        $userResultMock->expects($this->never())
            ->method('setRole')
            ->with($userDtoMock->role);

        $this->userAdapterMock->expects($this->once())
            ->method('convertToEntity')
            ->with($userDtoMock, $userEntityMock)
            ->willReturn($userResultMock);

        $this->emMock->expects($this->never())
            ->method('persist')
            ->with($userResultMock);
        $this->emMock->expects($this->never())
            ->method('flush');

        $this->userService->editUserRole($userDtoMock, $loggedUserMock, 'username');
    }

    /**
     * Tests the case when a user that is not owner or managers tries to access the page
     */
    public function testEditUserRoleWithLoggedUserThatIsNotOwnerOrUser()
    {
        $this->expectException(InappropriateUserRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->userService->editUserRole($this->createMock(UserDto::class), $loggedUserMock, 'username');
    }

    /**
     * @test
     */
    public function testSuccessfullyGetUsersFromHotelsByOwner()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $offset = 0;
        $hotelId = 1;

        $userMock1 = $this->createMock(User::class);
        $userMock2 = $this->createMock(User::class);
        $usersMocks = [
            $userMock1,
            $userMock2,
        ];

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('paginateAndSortUsersFromOwnerHotel')
            ->with($loggedUserMock, $offset, null, null, $hotelId)
            ->willReturn($usersMocks);

        $userDtoMock1 = $this->createMock(UserDto::class);
        $userDtoMock1->role = $this->createMock(RoleDto::class);
        $userDtoMock2 = $this->createMock(UserDto::class);
        $userDtoMock2->role = $this->createMock(RoleDto::class);
        $userDtoMocks = [$userDtoMock1, $userDtoMock2];

        $this->userAdapterMock->expects($this->once())
            ->method('convertCollectionToDto')
            ->with($usersMocks)
            ->willReturn($userDtoMocks);

        $result = $this->userService->getUsersFromHotels($loggedUserMock, $offset, $hotelId);
        $this->assertInstanceOf(RoleDto::class, $result[0]->role);
    }

    /**
     *  @test
     */
    public function testSuccessfullyGetUsersFromHotelsByManager()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);

        $offset = 0;
        $hotelId = null;

        $userMock1 = $this->createMock(User::class);
        $userMock2 = $this->createMock(User::class);
        $usersMocks = [
            $userMock1,
            $userMock2,
        ];

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('paginateAndSortUsersFromManagerHotel')
            ->with($loggedUserMock, $offset, null, null)
            ->willReturn($usersMocks);

        $userDtoMock1 = $this->createMock(UserDto::class);
        $userDtoMock1->role = $this->createMock(RoleDto::class);
        $userDtoMock2 = $this->createMock(UserDto::class);
        $userDtoMock2->role = $this->createMock(RoleDto::class);
        $userDtoMocks = [$userDtoMock1, $userDtoMock2];

        $this->userAdapterMock->expects($this->once())
            ->method('convertCollectionToDto')
            ->with($usersMocks)
            ->willReturn($userDtoMocks);

        $result = $this->userService->getUsersFromHotels($loggedUserMock, $offset, $hotelId);
        $this->assertInstanceOf(RoleDto::class, $result[0]->role);
    }

    /**
     * @test
     */
    public function testGetUsersFromHotelsByUserWithNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->getUsersFromHotels($loggedUserMock, 0, null);
    }

    /**
     * @test
     */
    public function testGetUsersFromHotelsByUserWithNoHighRole()
    {
        $this->expectException(InappropriateUserRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->userService->getUsersFromHotels($loggedUserMock, 0, null);
    }

    /**
     * @test
     */
    public function testSuccessfullyGetPagesNumberForManagerManagement()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('getUsersPagesNumberFromManagerHotel')
            ->with($loggedUserMock)
            ->willReturn(2);

        $this->userService->getPagesNumberForManagerManagement($loggedUserMock);
    }

    /**
     * @test
     */
    public function testGetPagesNumberForManagerManagementByUserWithNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->getPagesNumberForManagerManagement($loggedUserMock);
    }

    /**
     * @test
     */
    public function testGetPagesNumberForManagerManagementByUserWithNoHighRole()
    {
        $this->expectException(InappropriateUserRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->userService->getPagesNumberForManagerManagement($loggedUserMock);
    }

    /**
     * @test
     */
    public function testSuccessfullyPaginateAndSortManagersUsers()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_MANAGER]);

        $userMock1 = $this->createMock(User::class);
        $userMock2 = $this->createMock(User::class);
        $usersMocks = [$userMock1, $userMock2];

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('paginateAndSortUsersFromManagerHotel')
            ->with($loggedUserMock, 0, 'firstName', 'ASC')
            ->willReturn($usersMocks);

        $userDtoMock1 = $this->createMock(UserDto::class);
        $userDtoMock1->role = $this->createMock(RoleDto::class);
        $userDtoMock2 = $this->createMock(UserDto::class);
        $userDtoMock2->role = $this->createMock(RoleDto::class);
        $userDtoMocks = [$userDtoMock1, $userDtoMock2];

        $this->userAdapterMock->expects($this->once())
            ->method('convertCollectionToDto')
            ->with($usersMocks)
            ->willReturn($userDtoMocks);

        $result = $this->userService->paginateAndSortManagersUsers($loggedUserMock, 0, 'firstName', 'ASC');
        $this->assertInstanceOf(RoleDto::class, $result[0]->role);
    }

    /**
     * @test
     */
    public function paginateAndSortManagersUsersByUserWithNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->paginateAndSortManagersUsers($loggedUserMock, 0, 'firstName', 'ASC');
    }

    /**
     * @test
     */
    public function paginateAndSortManagersUsersByUserWithNoHighRole()
    {
        $this->expectException(InappropriateUserRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->userService->paginateAndSortManagersUsers($loggedUserMock, 0, 'firstName', 'ASC');
    }

    /**
     * @test
     */
    public function testSuccessfullyPaginateAndSortOwnersUsers()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $userMock1 = $this->createMock(User::class);
        $userMock2 = $this->createMock(User::class);
        $usersMocks = [$userMock1, $userMock2];

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('paginateAndSortUsersFromOwnerHotel')
            ->with($loggedUserMock, 0, 'firstName', 'ASC', 1)
            ->willReturn($usersMocks);

        $userDtoMock1 = $this->createMock(UserDto::class);
        $userDtoMock1->role = $this->createMock(RoleDto::class);
        $userDtoMock2 = $this->createMock(UserDto::class);
        $userDtoMock2->role = $this->createMock(RoleDto::class);
        $userDtoMocks = [$userDtoMock1, $userDtoMock2];

        $this->userAdapterMock->expects($this->once())
            ->method('convertCollectionToDto')
            ->with($usersMocks)
            ->willReturn($userDtoMocks);

        $result = $this->userService->paginateAndSortOwnersUsers($loggedUserMock, 0, 'firstName', 'ASC', 1);
        $this->assertInstanceOf(RoleDto::class, $result[0]->role);
    }

    /**
     * @test
     */
    public function paginateAndSortOwnersUsersByUserWithNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->paginateAndSortOwnersUsers($loggedUserMock, 0, 'firstName', 'ASC', 1);
    }

    /**
     * @test
     */
    public function paginateAndSortOwnersUsersByUserWithNoHighRole()
    {
        $this->expectException(InappropriateUserRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->userService->paginateAndSortOwnersUsers($loggedUserMock, 0, 'firstName', 'ASC', 1);
    }

    /**
     * @test
     */
    public function testSuccessfullyGetPagesNumberForOwnerManagement()
    {
        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_OWNER]);

        $this->repositoriesMocks[EntityConfig::USER]->expects($this->once())
            ->method('getUsersPagesNumberFromOwnerHotel')
            ->with($loggedUserMock)
            ->willReturn(2);

        $this->userService->getPagesNumberForOwnerManagement($loggedUserMock, 1);
    }

    /**
     * @test
     */
    public function testGetPagesNumberForOwnerManagementByUserWithNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->getPagesNumberForOwnerManagement($loggedUserMock, 1);
    }

    /**
     * @test
     */
    public function testGetPagesNumberForOwnerManagementByUserWithNoHighRole()
    {
        $this->expectException(InappropriateUserRoleException::class);

        $loggedUserMock = $this->createMock(User::class);
        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn([UserConfig::ROLE_EMPLOYEE]);

        $this->userService->getPagesNumberForOwnerManagement($loggedUserMock, 1);
    }

    /**
     * @return \DateTime
     */
    private function generateActivationTime()
    {
        $dateTime = new \DateTime();
        $dateTime->modify('+1 minutes');

        return $dateTime;
    }

    /**
     * @return \DateTime
     */
    private function generateExpiredActivationTime()
    {
        $dateTime = new \DateTime();
        $dateTime->modify('-1 minutes');

        return $dateTime;
    }
}
