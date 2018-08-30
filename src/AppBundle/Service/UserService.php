<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 14.08.2018
 * Time: 15:00
 */

namespace AppBundle\Service;

use AppBundle\Adapter\RoleAdapter;
use AppBundle\Adapter\UserAdapter;
use AppBundle\Dto\UserDto;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Enum\UserConfig;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Exception\TokenExpiredException;
use AppBundle\Exception\UneditableRoleException;
use AppBundle\Exception\UserNotFoundException;
use AppBundle\Exception\SameRoleException;
use AppBundle\Helper\MailInterface;

use AppBundle\Helper\ValidateUserHelper;
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
    private $fileUploader;
    private $mailHelper;
    private $userAdapter;
    private $roleAdapter;

    /**
     * UserService constructor.
     *
     * @param EntityManager       $em
     * @param UserPasswordEncoder $encoder
     * @param FileUploaderService $fileUploaderService
     * @param MailInterface       $mailHelper
     * @param UserAdapter         $userAdapter
     * @param RoleAdapter         $roleAdapter
     */
    public function __construct(
        EntityManager $em,
        UserPasswordEncoder $encoder,
        FileUploaderService $fileUploaderService,
        MailInterface $mailHelper,
        UserAdapter $userAdapter,
        RoleAdapter $roleAdapter
    ) {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->fileUploader = $fileUploaderService;
        $this->mailHelper = $mailHelper;
        $this->userAdapter = $userAdapter;
        $this->roleAdapter = $roleAdapter;
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
                    'description' => UserConfig::ROLE_CLIENT,
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
        ValidateUserHelper::checkIfUserHasRole($user->getRoles());

        $userRole = $user->getRoles()[0];
        $roles = $this->em->getRepository(Role::class)->findAll();
        $result = [];

        /** @var Role $role */
        foreach ($roles as $role) {
            $roleDescription = $role->getDescription();

            if (!($roleDescription === UserConfig::ROLE_CLIENT || $roleDescription === $userRole)) {
                $result[$roleDescription] = $role;
            }
        }

        if ($userRole === UserConfig::ROLE_MANAGER) {
            unset($result[UserConfig::ROLE_OWNER]);
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
        ValidateUserHelper::checkIfUserHasRole($loggedUser->getRoles());

        $password = $this
            ->encoder
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            );
        $user->setPassword($password);
        $user->setIsActivated(true);
        $user->setExpirationDate($this->generateActivationTime());

        if ($loggedUser->getRoles()[0] === UserConfig::ROLE_MANAGER) {
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
     * @return array
     */
    public function getUsersFromHotels(User $loggedUser, $offset, $hotelId = null)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);
        ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);

        if (empty($hotelId)) {
            $users = $this->em->getRepository(User::class)->paginateAndSortUsersFromManagerHotel($loggedUser, $offset, null, null);

            return $this->convertUsersAndUsersComponentsToDto($users);
        }
        $users = $this->em->getRepository(User::class)->paginateAndSortUsersFromOwnerHotel($loggedUser, $offset, null, null, $hotelId);

        return $this->convertUsersAndUsersComponentsToDto($users);
    }

    /**
     * @param User $loggedUser
     * @return int
     */
    public function getPagesNumberForManagerManagement(User $loggedUser)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);
        ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);

        return $this->em->getRepository(User::class)->getUsersPagesNumberFromManagerHotel($loggedUser);
    }

    /**
     * @param User  $loggedUser
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sortType
     * @return array
     */
    public function paginateAndSortManagersUsers(User $loggedUser, $offset, $column, $sortType)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);
        ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);

        $users = $this->em->getRepository(User::class)->paginateAndSortUsersFromManagerHotel($loggedUser, $offset, $column, $sortType);

        return $this->convertUsersAndUsersComponentsToDto($users);
    }

    /**
     * @param User  $loggedUser
     * @param mixed $hotelId
     *
     * @return int
     */
    public function getPagesNumberForOwnerManagement(User $loggedUser, $hotelId)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);
        ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);

        return $this->em->getRepository(User::class)->getUsersPagesNumberFromOwnerHotel($loggedUser, $hotelId);
    }

    /**
     * @param User  $loggedUser
     * @param mixed $offset
     * @param mixed $column
     * @param mixed $sortType
     * @param mixed $hotelId
     *
     * @return array
     */
    public function paginateAndSortOwnersUsers(User $loggedUser, $offset, $column, $sortType, $hotelId)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);
        ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);

        $users = $this->em->getRepository(User::class)->paginateAndSortUsersFromOwnerHotel($loggedUser, $offset, $column, $sortType, $hotelId);

        return $this->convertUsersAndUsersComponentsToDto($users);
    }

    /**
     * This function edits userDto's role.
     * Preconditions: $userDto is an existing user from the db
     *
     * @param UserDto $userDto
     * @param User    $loggedUser
     * @param array   $hotels
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editUserRole(UserDto $userDto, User $loggedUser, $hotels)
    {
        $loggedUserRole = $loggedUser->getRoles();
        ValidateUserHelper::checkIfUserHasRole($loggedUserRole);
        $loggedUserRole = ValidateUserHelper::checkIfUserHasHighRole($loggedUserRole);

        if ($loggedUserRole === UserConfig::ROLE_MANAGER) {
            if (!$this->checkIfUserHasManagerHotelId($loggedUser->getHotel(), $userDto->username)) {
                throw new UserNotFoundException('This user is not part of managers hotel.');
            }
        }

        if ($loggedUserRole === UserConfig::ROLE_OWNER) {
            if (!$this->checkIfUserHasOneOfOwnersHotelId($hotels, $userDto->username)) {
                throw new UserNotFoundException('This user is not part of owners hotels.');
            }
        }

        $userEntity = $this->getUserFromDto($userDto);
        $userEntityRole = $userEntity->getRole();
        if ($userEntityRole === $userDto->role) {
            throw new SameRoleException($userDto->username." already has ".$userDto->role->getDescription());
        }
        if (array_search($userEntityRole->getDescription(), UserConfig::EDITABLE_ROLES) === false) {
            throw new UneditableRoleException('Can not edit users with '.$userEntityRole->getDescription().'.');
        }

        $editedUser = $this->userAdapter->convertToEntity($userDto, $userEntity);

        $this->em->persist($editedUser);
        $this->em->flush();
    }

    /**
     * @param $hotels
     * @param $username
     *
     * @return bool
     */
    private function checkIfUserHasOneOfOwnersHotelId($hotels, $username)
    {
        foreach ($hotels as $name => $hotel) {
            $user = $this->em->getRepository(User::class)->findOneBy(
                [
                    'hotel' => $hotel,
                    'username' => $username,
                ]
            );

            if (!empty($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $managerHotel
     * @param $username
     *
     * @return bool
     */
    private function checkIfUserHasManagerHotelId($managerHotel, $username)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(
            [
                'hotel' => $managerHotel,
                'username' => $username,
            ]
        );
        if (empty($user)) {
            return false;
        }

        return true;
    }

    /**
     * @param UserDto $userDto
     *
     * @return User|null|object
     */
    private function getUserFromDto($userDto)
    {
        return $this->em->getRepository(User::class)->findOneBy(
            [
                'username' => $userDto->username,
            ]
        );
    }

    /**
     * @return \DateTime
     */
    private function generateActivationTime()
    {
        $dateTime = new \DateTime();
        $dateTime->modify(UserConfig::TOKEN_LIFETIME);

        return $dateTime;
    }

    /**
     * @param $users
     * @return array
     */
    private function convertUsersAndUsersComponentsToDto($users)
    {
        $userDtos = $this->userAdapter->convertCollectionToDto($users);
        foreach ($userDtos as $userDto) {
            $userDto->role = $this->roleAdapter->convertToDto($userDto->role);
        }

        return $userDtos;
    }
}
