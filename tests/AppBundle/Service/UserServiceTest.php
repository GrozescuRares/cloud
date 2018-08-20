<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 09:01
 */

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Helper\MailHelper;
use AppBundle\Repository\RoleRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\FileUploaderService;
use AppBundle\Service\UserService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Class UserServiceTest
 * @package Tests\AppBundle\Service
 */
class UserServiceTest extends TestCase
{
    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $emMock;
    /** @var Role|\PHPUnit_Framework_MockObject_MockObject */
    protected $clientRoleMock;
    /** @var UserPasswordEncoder| \PHPUnit_Framework_MockObject_MockObject */
    protected $userPasswordEncoderMock;
    /** @var FileUploaderService| \PHPUnit_Framework_MockObject_MockObject */
    protected $fileUploaderMock;
    /** @var MailHelper| \PHPUnit_Framework_MockObject_MockObject */
    protected $mailMock;
    /** @var UserRepository| \PHPUnit_Framework_MockObject_MockObject */
    protected $userRepositoryMock;
    /** @var UserService */
    protected $userService;

    /**
     *
     */
    public function setUp()
    {
        $this->emMock = $this->createMock(EntityManager::class);
        $this->clientRoleMock = $this->createMock(Role::class);
        $roleRepositoryMock = $this->createMock(RoleRepository::class);

        $roleRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'description' => 'ROLE_CLIENT',
                ]
            )
            ->willReturn($this->clientRoleMock);

        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        $this->emMock->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($roleRepositoryMock);

        $this->emMock->expects($this->at(1))
            ->method('getRepository')
            ->willReturn($this->userRepositoryMock);

        $this->userPasswordEncoderMock = $this->createMock(UserPasswordEncoder::class);

        $this->fileUploaderMock = $this->createMock(FileUploaderService::class);

        $this->mailMock = $this->createMock(MailHelper::class);

        $this->userService = new UserService(
            $this->emMock,
            $this->userPasswordEncoderMock,
            $this->fileUploaderMock,
            $this->mailMock,
            '+1 times'
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

        $this->userRepositoryMock->expects($this->once())
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

        $this->userRepositoryMock->expects($this->once())
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

        $this->throwException(new TokenExpiredException());

        $this->userService->activateAccount('dfsdfssdf');
    }

    /**
     * Tests user not found exception - no user with that token
     * @throws OptimisticLockException
     */
    public function testNoUserWithThatTokenAccountActivation()
    {
        $this->expectException(UserNotFoundException::class);

        $this->userRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'activationToken' => 'dfsdfssdf',
                    'isActivated' => false,
                ]
            )
            ->willReturn(null);

        $this->throwException(new UserNotFoundException());

        $this->emMock->expects($this->never())
            ->method('persist');

        $this->emMock->expects($this->never())
            ->method('flush');

        $this->userService->activateAccount('dfsdfssdf');
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
