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
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Helper\MailInterface;
use Doctrine\ORM\EntityManager;
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
    private $mailHelper;
    private $tokenLifetime;

    /**
     * UserService constructor.
     *
     * @param EntityManager       $em
     * @param UserPasswordEncoder $encoder
     * @param FileUploaderService $fileUploaderService
     * @param MailInterface       $mailHelper
     * @param string              $tokenLifetime
     */
    public function __construct(EntityManager $em, UserPasswordEncoder $encoder, FileUploaderService $fileUploaderService, MailInterface $mailHelper, $tokenLifetime)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->clientRole = $em->getRepository(Role::class)->findOneBy([
            'description' => 'ROLE_CLIENT',
        ]);
        $this->fileUploader = $fileUploaderService;
        $this->mailHelper = $mailHelper;
        $this->tokenLifetime = $tokenLifetime;
    }

    /**
     * @param User $user
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function registerUser(User $user)
    {
        $password = $this
            ->encoder
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            );
        $user->setPassword($password);
        $user->setRole($this->clientRole);
        $user->setIsActivated(false);
        $user->setActivationToken(md5($user->getUsername()).md5($user->getEmail()));
        $user->setExpirationDate($this->generateActivationTime());

        $file = $user->getImage();
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $user->setProfilePicture($fileName);
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->mailHelper->sendEmail(
            $user->getEmail(),
            'Registration',
            [
                'username' => $user->getUsername(),
                'password' => $user->getPlainPassword(),
                'activationToken' => $user->getActivationToken(),
            ],
            'emails/registration.html.twig'
        );
    }

    /**
     * @param string $activationToken
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws UserNotFoundException
     * @throws TokenExpiredException
     */
    public function activateAccount($activationToken)
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'activationToken' => $activationToken,
            'isActivated' => false,
        ]);

        if (!$user instanceof User) {
            throw new UserNotFoundException('There is no user with token: '.$activationToken);
        }

        if ($user->getExpirationDate() < new \DateTime()) {
            $user->setExpirationDate($this->generateActivationTime());
            $this->mailHelper->sendEmail(
                $user->getEmail(),
                'Re-activation',
                [
                    'activationToken' => $user->getActivationToken(),
                ],
                'emails/reactivation.html.twig'
            );

            $this->em->persist($user);
            $this->em->flush();

            throw new TokenExpiredException('Token: '.$activationToken.' expired.');
        }

        $user->setIsActivated(true);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Returns an array of roles that contains every roles except
     * $user's role, ROLE_CLIENT, and the roles that are higher in hierarchy.
     * The elements of the array will look like 'role_description' => role entity
     *
     *  Example: 1. For a user with ROLE_OWNER, the function will return
     *              an array containing all the roles except ROLE_OWNER
     *              and ROLE_CLIENT.
     *           2. For a user with ROLE_MANAGER the function will return
     *              an array containing all the roles except ROLE_OWNER,
     *              ROLE_MANAGER and ROLE_CLIENT.
     *
     * @param User $user
     *
     * @throws InappropriateUserRoleException if it's called with an $user that
     *                                        doesn't have role ROLE_OWNER or
     *                                        ROLE_MANAGER
     *
     * @return array
     */
    public function getUserCreationalRoles(User $user)
    {
        $userRole = $user->getRoles()[0];

        if (! ($userRole === 'ROLE_OWNER' || $userRole === 'ROLE_MANAGER' )) {
            throw new InappropriateUserRoleException();
        }

        $roles = $this->em->getRepository(Role::class)->findAll();
        $result = [];

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleDescription = $role->getDescription();

            if (! ($roleDescription === 'ROLE_CLIENT' || $roleDescription === $userRole)) {
                $result[$roleDescription] = $role;
            }
        }

        if ($userRole === 'ROLE_MANAGER') {
            unset($result['ROLE_OWNER']);
        }

        return $result;
    }

    /**
     * @return \DateTime
     */
    private function generateActivationTime()
    {
        $dateTime = new \DateTime();
        $dateTime->modify($this->tokenLifetime);

        return $dateTime;
    }
}
