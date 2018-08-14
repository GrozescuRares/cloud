<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 14.08.2018
 * Time: 15:00
 */

namespace AppBundle\Service;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Class UserService
 * @package AppBundle\Service
 */
class UserService
{
    private $em;
    private $encoder;
    private $clientRole;
    private $fileUploader;

    /**
     * UserService constructor.
     *
     * @param EntityManager       $em
     *
     * @param UserPasswordEncoder $encoder
     *
     * @param FileUploaderService $fileUploaderService
     */
    public function __construct(EntityManager $em, UserPasswordEncoder $encoder, FileUploaderService $fileUploaderService)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->clientRole = $em->getRepository(Role::class)->find(4);
        $this->fileUploader = $fileUploaderService;
    }

    /**
     * @param User $user
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function insertUser(User $user)
    {
        $password = $this
            ->encoder
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            );
        $user->setPassword($password);
        $user->setRole($this->clientRole);
        $user->setLastName($user->getUsername().' Last Name');
        $user->setFirstName($user->getUsername().' First Name');

        $file = $user->getImage();
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $user->setProfilePicture($fileName);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}
