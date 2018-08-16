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
    const ACTIVATION_LINK_TIME = '+1 minutes';

    /**
     * UserService constructor.
     *
     * @param EntityManager       $em
     *
     * @param UserPasswordEncoder $encoder
     *
     * @param FileUploaderService $fileUploaderService
     *
     * @param MailInterface       $mailHelper
     */
    public function __construct(EntityManager $em, UserPasswordEncoder $encoder, FileUploaderService $fileUploaderService, MailInterface $mailHelper)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->clientRole = $em->getRepository(Role::class)->find(4);
        $this->fileUploader = $fileUploaderService;
        $this->mailHelper = $mailHelper;
    }

    /**
     * @param User $user
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @throws \Twig_Error_Syntax
     *
     * @throws \Twig_Error_Loader
     *
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
        $user->setLastName($user->getUsername().' Last Name');
        $user->setFirstName($user->getUsername().' First Name');
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
     * @return int      1 if everything was ok
     *                 -1 if the token expired
     *                 -2 if the token is invalid
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function activateAccount($activationToken)
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'activationToken' => $activationToken,
            'isActivated' => false,
        ]);

        if (!$user) {
            return -2; //invalid token
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

            return -1; //expired token
        }

        $user->setIsActivated(true);
        $this->em->persist($user);
        $this->em->flush();

        return 1; //ok
    }

    /**
     * @return \DateTime
     */
    private function generateActivationTime()
    {
        $dateTime = new \DateTime();
        $dateTime->modify(self::ACTIVATION_LINK_TIME);

        return $dateTime;
    }
}
