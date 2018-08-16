<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 12:02
 */

namespace AppBundle\Controller;

use AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ActivationController
 * @package AppBundle\Controller
 */
class ActivationController extends Controller
{
    /**
     * @Route("/activate-account/{activationToken}", name="activate-account")
     *
     * @param string      $activationToken
     * @param UserService $userService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function activationAction($activationToken, UserService $userService)
    {

        $response = $userService->activateAccount($activationToken);

        if ($response === -2) {
            return $this->render('error.html.twig', [
                'error' => 'Invalid token',
            ]);
        }

        if ($response === -1) {
            return $this->render('activation/activate-account.html.twig', [
                'message' => 'That activation link expired, but we already sent a new one to your email',
                'success' => false,
            ]);
        }

        return $this->render('activation/activate-account.html.twig', [
            'message' => 'Your account is active now',
            'success' => true,
        ]);
    }
}
