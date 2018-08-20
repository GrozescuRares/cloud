<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 20.08.2018
 * Time: 09:01
 */

namespace Tests\AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Service\UserService;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\TestCase;
use Tests\AppBundle\Stub\GetRepositoryUserExpiredToken;
use Tests\AppBundle\Stub\GetRepositroyNonValid;
use Tests\AppBundle\Stub\GetRepositoryValidStub;

/**
 * Class UserServiceTest
 * @package Tests\AppBundle\Service
 */
class UserServiceTest extends TestCase
{
    /**
     * Tests a successful user registration
     */
    public function testSuccessfullyRegisterUser()
    {
        $response = new \stdClass();
        $response->response = true;

        $getRepositoryValidStub = new GetRepositoryValidStub();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('flush')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($getRepositoryValidStub));

        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->will($this->returnValue($response));

        $fileUploaderMock = $this->getMockBuilder('AppBundle\Service\FileUploaderService')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock->expects($this->once())
            ->method('upload')
            ->will($this->returnValue($response));

        $uploadedFileMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock = $this->getMockBuilder('AppBundle\Helper\MailHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock->expects($this->once())
            ->method('sendEmail')
            ->will($this->returnValue($response));

        $userService = new UserService($emMock, $passwordEncoder, $fileUploaderMock, $mailMock, '+1 minutes');

        $user = new User();
        $user->setUsername('Rares')
            ->setEmail('grozescu@grozescu.com')
            ->setImage($uploadedFileMock);


        try {
            $userService->registerUser($user);
            $this->assertTrue(true);
        } catch (OptimisticLockException $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Loader $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Runtime $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Syntax $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * Tests a duplicated user registration
     */
    public function testDuplicatedRegisterUser()
    {
        $response = new \stdClass();
        $response->response = true;

        $exception = new OptimisticLockException('message', new \stdClass());

        $getRepositoryValidStub = new GetRepositoryValidStub();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('flush')
            ->willThrowException($exception);

        $emMock->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($getRepositoryValidStub));

        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->will($this->returnValue($response));

        $fileUploaderMock = $this->getMockBuilder('AppBundle\Service\FileUploaderService')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock->expects($this->once())
            ->method('upload')
            ->will($this->returnValue($response));

        $uploadedFileMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock = $this->getMockBuilder('AppBundle\Helper\MailHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock->expects($this->never())
            ->method('sendEmail')
            ->will($this->returnValue($response));

        $userService = new UserService($emMock, $passwordEncoder, $fileUploaderMock, $mailMock, '+1 minutes');

        $user = new User();
        $user->setUsername('Rares')
            ->setEmail('grozescu@grozescu.com')
            ->setImage($uploadedFileMock);

        try {
            $userService->registerUser($user);
            $this->assertTrue(false);
        } catch (OptimisticLockException $e) {
            $this->assertTrue(true);
        } catch (\Twig_Error_Loader $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Runtime $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Syntax $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * Tests a successful user registration with no profile picture
     */
    public function testNoProfileImageRegisterUser()
    {
        $response = new \stdClass();
        $response->response = true;

        $getRepositoryValidStub = new GetRepositoryValidStub();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('flush')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($getRepositoryValidStub));

        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->will($this->returnValue($response));

        $fileUploaderMock = $this->getMockBuilder('AppBundle\Service\FileUploaderService')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock->expects($this->never())
            ->method('upload')
            ->will($this->returnValue($response));

        $mailMock = $this->getMockBuilder('AppBundle\Helper\MailHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock->expects($this->once())
            ->method('sendEmail')
            ->will($this->returnValue($response));

        $userService = new UserService($emMock, $passwordEncoder, $fileUploaderMock, $mailMock, '+1 minutes');

        $user = new User();
        $user->setUsername('Rares')
            ->setEmail('grozescu@grozescu.com');
        try {
            $userService->registerUser($user);
            $this->assertTrue(true);
        } catch (OptimisticLockException $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Loader $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Runtime $e) {
            $this->assertTrue(false);
        } catch (\Twig_Error_Syntax $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * Tests a successful account activation
     */
    public function testSuccessfullyAccountActivation()
    {
        $response = new \stdClass();
        $response->response = true;

        $userAndGetRepositoryValidStub = new GetRepositoryValidStub();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('flush')
            ->will($this->returnValue($response));

        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($userAndGetRepositoryValidStub));

        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock = $this->getMockBuilder('AppBundle\Service\FileUploaderService')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock = $this->getMockBuilder('AppBundle\Helper\MailHelper')
            ->disableOriginalConstructor()
            ->getMock();


        $userService = new UserService($emMock, $passwordEncoder, $fileUploaderMock, $mailMock, '+1 minutes');

        try {
            $userService->activateAccount('dfsiwgsfhihswiu');
            $this->assertTrue(true);
        } catch (OptimisticLockException $e) {
            $this->assertTrue(false);
        } catch (TokenExpiredException $e) {
            $this->assertTrue(false);
        } catch (UserNotFoundException $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * Tests a resend email account activation
     */
    public function testResendEmailAccountActivation()
    {
        $response = new \stdClass();
        $response->response = true;

        $userAndGetRepositoryValidStub = new GetRepositoryUserExpiredToken();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($response));

        $emMock->expects($this->once())
            ->method('flush')
            ->will($this->returnValue($response));

        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($userAndGetRepositoryValidStub));

        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock = $this->getMockBuilder('AppBundle\Service\FileUploaderService')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock = $this->getMockBuilder('AppBundle\Helper\MailHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock->expects($this->once())
            ->method('sendEmail')
            ->will($this->returnValue($response));


        $userService = new UserService($emMock, $passwordEncoder, $fileUploaderMock, $mailMock, '+1 minutes');

        try {
            $userService->activateAccount('dfsiwgsfhihswiu');
            $this->assertTrue(false);
        } catch (OptimisticLockException $e) {
            $this->assertTrue(false);
        } catch (TokenExpiredException $e) {
            $this->assertTrue(true);
        } catch (UserNotFoundException $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * Tests user not found exception - no user with that token
     */
    public function testNoUserWithThatTokenAccountActivation()
    {
        $response = new \stdClass();
        $response->response = true;

        $userAndGetRepositoryValidStub = new GetRepositroyNonValid();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($userAndGetRepositoryValidStub));

        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock = $this->getMockBuilder('AppBundle\Service\FileUploaderService')
            ->disableOriginalConstructor()
            ->getMock();

        $mailMock = $this->getMockBuilder('AppBundle\Helper\MailHelper')
            ->disableOriginalConstructor()
            ->getMock();


        $userService = new UserService($emMock, $passwordEncoder, $fileUploaderMock, $mailMock, '+1 minutes');

        try {
            $userService->activateAccount('dfsiwgsfhihswiu');
            $this->assertTrue(false);
        } catch (OptimisticLockException $e) {
            $this->assertTrue(false);
        } catch (TokenExpiredException $e) {
            $this->assertTrue(false);
        } catch (UserNotFoundException $e) {
            $this->assertTrue(true);
        }
    }
}
