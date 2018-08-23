<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 09:01
 */

namespace Tests\AppBundle\Service;

use AppBundle\Adapter\UserAdapter;
use AppBundle\Entity\Hotel;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Exception\NoRoleException;
use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Helper\MailHelper;
use AppBundle\Repository\RoleRepository;
use AppBundle\Repository\UserRepository;
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
    /** @var UserServiceTest */
    protected $userService;

    const FIRST_ENTITY = Role::class;
    const FIRST_ENTITY_REPOSITORY = RoleRepository::class;
    const SECOND_ENTITY = User::class;
    const SECOND_ENTITY_REPOSITORY = UserRepository::class;

    /**
     * UserServiceTest constructor.
     * @param array  $repositories
     * @param mixed  $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(
        array $repositories = [self::FIRST_ENTITY => self::FIRST_ENTITY_REPOSITORY, self::SECOND_ENTITY => self::SECOND_ENTITY_REPOSITORY],
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
            '+1 times',
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

        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => 'ROLE_CLIENT',
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

        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => 'ROLE_CLIENT',
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
        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => 'ROLE_CLIENT',
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

        $this->repositoriesMocks[self::SECOND_ENTITY]->expects($this->once())
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

        $this->repositoriesMocks[self::SECOND_ENTITY]->expects($this->once())
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

        $this->repositoriesMocks[self::SECOND_ENTITY]->expects($this->once())
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

        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword');

        $userMock->expects($this->once())
            ->method('setIsActivated')
            ->with(true);

        $loggedUserMock->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturn(['ROLE_OWNER']);

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

        $this->userPasswordEncoderMock->expects($this->once())
            ->method('encodePassword');

        $userMock->expects($this->once())
            ->method('setIsActivated')
            ->with(true);
        $userMock->expects($this->once())
            ->method('setHotel')
            ->with($hotelMock);

        $loggedUserMock->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturn(['ROLE_MANAGER']);
        $loggedUserMock->expects($this->once())
            ->method('getHotel')
            ->willReturn($hotelMock);

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
     * Tests addUser when loggedUser has no role
     */
    public function testAddUserWhenLoggedUserHasNoRole()
    {
        $this->expectException(NoRoleException::class);

        $loggedUserMock = $this->createMock(User::class);

        $loggedUserMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->userService->getUserCreationalRoles($loggedUserMock);
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
            ->willReturn('ROLE_MANAGER');

        $roleEmployeeMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_EMPLOYEE');

        $roleOwnerMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_OWNER');

        $roleClientMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_CLIENT');

        $userMock->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturn(['ROLE_OWNER']);

        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
            ->method('findAll')
            ->willReturn([$roleManagerMock, $roleEmployeeMock, $roleClientMock, $roleOwnerMock]);

        $this->assertEquals(
            ['ROLE_MANAGER' => $roleManagerMock, 'ROLE_EMPLOYEE' => $roleEmployeeMock],
            $this->userService->getUserCreationalRoles($userMock)
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
            ->willReturn('ROLE_MANAGER');

        $roleEmployeeMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_EMPLOYEE');

        $roleOwnerMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_OWNER');

        $roleClientMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('ROLE_CLIENT');

        $userMock->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturn(['ROLE_MANAGER']);

        $this->repositoriesMocks[self::FIRST_ENTITY]->expects($this->once())
            ->method('findAll')
            ->willReturn([$roleManagerMock, $roleEmployeeMock, $roleClientMock, $roleOwnerMock]);

        $this->assertEquals(
            ['ROLE_EMPLOYEE' => $roleEmployeeMock],
            $this->userService->getUserCreationalRoles($userMock)
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

        $this->userService->getUserCreationalRoles($userMock);
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
