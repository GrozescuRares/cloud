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
use AppBundle\Exception\NoRoleException;
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
    public function __construct(
        EntityManager $em,
        UserPasswordEncoder $encoder,
        FileUploaderService $fileUploaderService,
        MailInterface $mailHelper,
        $tokenLifetime
    ) {
        $this->em = $em;
        $this->encoder = $encoder;
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
        $user->setRole(
            $this->em->getRepository(Role::class)->findOneBy(
                [
                    'description' => 'ROLE_CLIENT',
                ]
            )
        );
        $user->setIsActivated(false);
        $user->setActivationToken(md5($user->getUsername()).md5($user->getEmail()));
        $user->setExpirationDate($this->generateActivationTime());

        $file = $user->getImage();
        if (!empty($file)) {
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
        $user = $this->em->getRepository(User::class)->findOneBy(
            [
                'activationToken' => $activationToken,
                'isActivated' => false,
            ]
        );

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
     * @return array
     */
    public function getUserCreationalRoles(User $user)
    {
        if (empty($user->getRoles())) {
            throw new NoRoleException();
        }

        $userRole = $user->getRoles()[0];
        $roles = $this->em->getRepository(Role::class)->findAll();
        $result = [];

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleDescription = $role->getDescription();

            if (!($roleDescription === 'ROLE_CLIENT' || $roleDescription === $userRole)) {
                $result[$roleDescription] = $role;
            }
        }

        if ($userRole === 'ROLE_MANAGER') {
            unset($result['ROLE_OWNER']);
        }

        return $result;
    }

    /**
     * @param User $user
     * @param User $loggedUser
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addUser(User $user, User $loggedUser)
    {
        if (empty($loggedUser->getRoles())) {
            throw new NoRoleException();
        }

        $password = $this
            ->encoder
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            );
        $user->setPassword($password);
        $user->setIsActivated(true);
        $user->setExpirationDate($this->generateActivationTime());

        if ($loggedUser->getRoles()[0] === 'ROLE_MANAGER') {
            $user->setHotel($loggedUser->getHotel());
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param User $user
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateUser(User $user)
    {
        if (!empty($user->getPlainPassword())) {
            $password = $this
                ->encoder
                ->encodePassword(
                    $user,
                    $user->getPlainPassword()
                );
            $user->setPassword($password);
        }

        $file = $user->getImage();
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $user->setProfilePicture($fileName);
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param User  $loggedUser
     * @param mixed $offset
     * @param mixed $hotelId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersFromHotels(User $loggedUser, $offset, $hotelId = null)
    {
        $loggedUserRole = $loggedUser->getRoles()[0];

        if (empty($loggedUserRole)) {
            throw new NoRoleException('This user has no role.');
        }

        if ($loggedUserRole !== 'ROLE_OWNER' && $loggedUserRole !== 'ROLE_MANAGER') {
            throw new InappropriateUserRoleException('This user is not an owner.');
        }

        if (empty($hotelId)) {
            return $this->em->getRepository(User::class)->getUsersFromManagerHotel($loggedUser, $offset);
        }

        $dql = "SELECT user FROM AppBundle:User user JOIN AppBundle:Hotel h WITH user.hotel = h.hotelId JOIN AppBundle:Role r WITH r.roleId = user.role WHERE h.owner=:owner AND h.hotelId=:hotelId";
        $query = $this->em->createQuery($dql)->setParameter('owner', $loggedUser)->setParameter('hotelId', $hotelId);

        return $query;
    }

    /**
     * @param User $loggedUser
     * @return int
     */
    public function getPagesNumberForManagerManagement(User $loggedUser)
    {
        return $this->em->getRepository(User::class)->getUsersPagesNumberFromManagerHotel($loggedUser);
    }

    /**
     * @param User  $loggedUser
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sortType
     * @return array
     */
    public function sortUsersFromManagerHotel(User $loggedUser, $offset, $column, $sortType)
    {
        return $this->em->getRepository(User::class)->sortUsersFromManagerHotel($loggedUser, $offset, $column, $sortType);
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
